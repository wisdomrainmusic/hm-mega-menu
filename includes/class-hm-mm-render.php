<?php
// File: includes/class-hm-mm-render.php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Frontend renderer for mega panel HTML.
 *
 * @package HM_Mega_Menu
 */
final class HM_MM_Render {

	/**
	 * Render mega panel markup for a target menu item.
	 *
	 * @param int                 $target_item_id Menu item ID (enabled item).
	 * @param array<string,mixed> $schema Sanitized schema.
	 * @param array<int, WP_Post> $menu_items Items of the currently rendered menu.
	 * @return string
	 */
	public static function render_panel( $target_item_id, $schema, $menu_items = array() ) {
		$target_item_id = absint( $target_item_id );
		$schema         = HM_MM_Schema::sanitize( $schema );

		$rows      = isset( $schema['rows'] ) && is_array( $schema['rows'] ) ? $schema['rows'] : array();
		$by_parent = HM_MM_Menu::index_by_parent( $menu_items );

		ob_start();
		?>
		<div class="hm-mega-panel" data-hm-mm="1" aria-hidden="true">
			<div class="hm-mega-inner">
				<?php if ( empty( $rows ) ) : ?>
					<div class="hm-mega-row hm-mega-row--empty">
						<?php echo esc_html__( 'Mega menu enabled, but no rows configured yet.', 'hm-mega-menu' ); ?>
					</div>
				<?php else : ?>
					<div class="hm-mega-rows">
						<?php foreach ( $rows as $row ) : ?>
							<?php echo self::render_row( $row, $by_parent ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>
		</div>
		<?php
		return (string) ob_get_clean();
	}

	/**
	 * Render one row from schema.
	 *
	 * @param array<string,mixed>              $row Row schema.
	 * @param array<int, array<int, WP_Post>>  $by_parent Parent index.
	 * @return string
	 */
	private static function render_row( $row, $by_parent ) {
		if ( ! is_array( $row ) ) {
			return '';
		}

		$title        = isset( $row['title'] ) ? (string) $row['title'] : '';
		$source_type  = isset( $row['source_type'] ) ? (string) $row['source_type'] : 'menu_node';
		$source_id    = isset( $row['source_id'] ) ? absint( $row['source_id'] ) : 0;
		$columns      = isset( $row['columns'] ) ? absint( $row['columns'] ) : 4;
		$depth        = isset( $row['depth'] ) ? absint( $row['depth'] ) : 3;
		$show_heading = isset( $row['show_heading'] ) ? absint( $row['show_heading'] ) : 1;

		$columns = ( $columns >= 1 && $columns <= 6 ) ? $columns : 4;
		$depth   = ( $depth >= 1 && $depth <= 3 ) ? $depth : 3;

		$allowed_source_types = array( 'menu_node' );
		if ( ! in_array( $source_type, $allowed_source_types, true ) ) {
			$source_type = 'menu_node';
		}

		// Build blocks for this row (Menu Node Mode).
		$blocks = array();
		if ( 'menu_node' === $source_type && $source_id > 0 ) {
			$blocks = self::build_menu_node_blocks( $by_parent, $source_id, $depth );
		}

		// Distribute blocks into columns.
		$cols = self::distribute_blocks( $blocks, $columns );

		ob_start();
		?>
		<div class="hm-mega-row" data-cols="<?php echo esc_attr( (string) $columns ); ?>" data-depth="<?php echo esc_attr( (string) $depth ); ?>">
			<?php if ( $show_heading && '' !== $title ) : ?>
				<div class="hm-mega-row-title"><?php echo esc_html( $title ); ?></div>
			<?php endif; ?>

			<div class="hm-mega-cols hm-mega-cols--<?php echo esc_attr( (string) $columns ); ?>">
				<?php for ( $i = 0; $i < $columns; $i++ ) : ?>
					<div class="hm-mega-col">
						<?php
						$col_blocks = isset( $cols[ $i ] ) ? (array) $cols[ $i ] : array();
						foreach ( $col_blocks as $block ) {
							echo self::render_block( $block, $depth ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						}
						?>
					</div>
				<?php endfor; ?>
			</div>
		</div>
		<?php
		return (string) ob_get_clean();
	}

	/**
	 * Build blocks from a menu node and its descendants.
	 *
	 * Rule:
	 * - depth=1: show only direct children (as block headings, no links)
	 * - depth=2: include children + their direct children links
	 * - depth=3: include children + grandchildren + great-grandchildren (flattened under each link group)
	 *
	 * @param array<int, array<int, WP_Post>> $by_parent Index.
	 * @param int                             $source_id Menu item ID.
	 * @param int                             $depth 1-3.
	 * @return array<int, array<string,mixed>>
	 */
	private static function build_menu_node_blocks( $by_parent, $source_id, $depth ) {
		$blocks = array();
		$level1 = HM_MM_Menu::children_of( $by_parent, $source_id );

		foreach ( $level1 as $child1 ) {
			$link1 = HM_MM_Menu::link_of( $child1 );

			$block = array(
				'heading' => $link1['title'],
				'url'     => $link1['url'],
				'links'   => array(),
				'weight'  => 1,
			);

			// depth 1: headings only.
			if ( 1 === (int) $depth ) {
				$blocks[] = $block;
				continue;
			}

			$level2 = HM_MM_Menu::children_of( $by_parent, absint( $child1->ID ) );

			foreach ( $level2 as $child2 ) {
				$link2 = HM_MM_Menu::link_of( $child2 );

				$item = array(
					'title'    => $link2['title'],
					'url'      => $link2['url'],
					'children' => array(),
				);

				if ( 3 === (int) $depth ) {
					$level3 = HM_MM_Menu::children_of( $by_parent, absint( $child2->ID ) );
					foreach ( $level3 as $child3 ) {
						$link3 = HM_MM_Menu::link_of( $child3 );
						$item['children'][] = array(
							'title' => $link3['title'],
							'url'   => $link3['url'],
						);
					}
				}

				$block['links'][] = $item;
			}

			// Weight = heading(1) + link count + child-link count.
			$w = 1;
			foreach ( $block['links'] as $lnk ) {
				$w += 1;
				if ( isset( $lnk['children'] ) && is_array( $lnk['children'] ) ) {
					$w += count( $lnk['children'] );
				}
			}
			$block['weight'] = $w;

			$blocks[] = $block;
		}

		return $blocks;
	}

	/**
	 * Distribute blocks across columns using greedy min-weight assignment.
	 *
	 * @param array<int, array<string,mixed>> $blocks Blocks.
	 * @param int                             $columns Columns count.
	 * @return array<int, array<int, array<string,mixed>>>
	 */
	private static function distribute_blocks( $blocks, $columns ) {
		$columns = ( $columns >= 1 && $columns <= 6 ) ? $columns : 4;

		$out     = array();
		$weights = array();

		for ( $i = 0; $i < $columns; $i++ ) {
			$out[ $i ]     = array();
			$weights[ $i ] = 0;
		}

		foreach ( (array) $blocks as $block ) {
			$w = isset( $block['weight'] ) ? absint( $block['weight'] ) : 1;

			// Find column with minimum current weight.
			$min_idx = 0;
			$min_w   = $weights[0];

			for ( $i = 1; $i < $columns; $i++ ) {
				if ( $weights[ $i ] < $min_w ) {
					$min_w   = $weights[ $i ];
					$min_idx = $i;
				}
			}

			$out[ $min_idx ][] = $block;
			$weights[ $min_idx ] += $w;
		}

		return $out;
	}

	/**
	 * Render one block.
	 *
	 * @param array<string,mixed> $block Block.
	 * @param int                 $depth Depth.
	 * @return string
	 */
	private static function render_block( $block, $depth ) {
		if ( ! is_array( $block ) ) {
			return '';
		}

		$heading = isset( $block['heading'] ) ? (string) $block['heading'] : '';
		$url     = isset( $block['url'] ) ? (string) $block['url'] : '';
		$links   = isset( $block['links'] ) && is_array( $block['links'] ) ? $block['links'] : array();

		ob_start();
		?>
		<div class="hm-mega-block">
			<?php if ( '' !== $heading ) : ?>
				<div class="hm-mega-block-title">
					<?php if ( '' !== $url ) : ?>
						<a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $heading ); ?></a>
					<?php else : ?>
						<?php echo esc_html( $heading ); ?>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<?php if ( (int) $depth >= 2 && ! empty( $links ) ) : ?>
				<ul class="hm-mega-links">
					<?php foreach ( $links as $lnk ) : ?>
						<?php
						$t  = isset( $lnk['title'] ) ? (string) $lnk['title'] : '';
						$u  = isset( $lnk['url'] ) ? (string) $lnk['url'] : '';
						$ch = isset( $lnk['children'] ) && is_array( $lnk['children'] ) ? $lnk['children'] : array();
						?>
						<li class="hm-mega-link">
							<?php if ( '' !== $u ) : ?>
								<a href="<?php echo esc_url( $u ); ?>"><?php echo esc_html( $t ); ?></a>
							<?php else : ?>
								<?php echo esc_html( $t ); ?>
							<?php endif; ?>

							<?php if ( (int) $depth >= 3 && ! empty( $ch ) ) : ?>
								<ul class="hm-mega-sublinks">
									<?php foreach ( $ch as $c ) : ?>
										<?php
										$ct = isset( $c['title'] ) ? (string) $c['title'] : '';
										$cu = isset( $c['url'] ) ? (string) $c['url'] : '';
										?>
										<li class="hm-mega-sublink">
											<?php if ( '' !== $cu ) : ?>
												<a href="<?php echo esc_url( $cu ); ?>"><?php echo esc_html( $ct ); ?></a>
											<?php else : ?>
												<?php echo esc_html( $ct ); ?>
											<?php endif; ?>
										</li>
									<?php endforeach; ?>
								</ul>
							<?php endif; ?>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>
		<?php
		return (string) ob_get_clean();
	}
}
