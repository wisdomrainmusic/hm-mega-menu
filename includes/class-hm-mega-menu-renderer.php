<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class HM_Mega_Menu_Renderer {

	/**
	 * Render sections container content for a menu config.
	 *
	 * @param int   $menu_id
	 * @param array $config
	 * @return string
	 */
	public function render_sections_html( $menu_id, $config ) {
		$menu_id = absint( $menu_id );
		if ( ! $menu_id ) {
			return '';
		}

		$config   = HM_Mega_Menu_Storage::normalize_menu_config( $config );
		$sections = isset( $config['sections'] ) && is_array( $config['sections'] ) ? $config['sections'] : array();
		if ( empty( $sections ) ) {
			return '';
		}

		usort(
			$sections,
			function ( $left, $right ) {
				$left_order  = isset( $left['order'] ) ? (int) $left['order'] : 0;
				$right_order = isset( $right['order'] ) ? (int) $right['order'] : 0;
				return $left_order <=> $right_order;
			}
		);

		$items = wp_get_nav_menu_items( $menu_id );
		if ( ! is_array( $items ) ) {
			$items = array();
		}

		$index = $this->build_index( $items );

		ob_start();
		foreach ( $sections as $section ) {
			$section = HM_Mega_Menu_Storage::normalize_section( $section );
			if ( ! $section ) {
				continue;
			}

			// MVP: menu_node only
			if ( 'menu_node' !== $section['source_type'] || empty( $section['source_id'] ) ) {
				continue;
			}

			echo $this->render_section_menu_node( $section, $index );
		}
		return (string) ob_get_clean();
	}

	/**
	 * Build a fast lookup index from menu items.
	 *
	 * @param array $items
	 * @return array
	 */
	private function build_index( $items ) {
		$by_id    = array();
		$child_of = array(); // parent_id => [childId, childId...]

		foreach ( $items as $it ) {
			if ( ! is_object( $it ) || empty( $it->ID ) ) {
				continue;
			}
			$id         = (int) $it->ID;
			$by_id[ $id ] = $it;

			$parent = isset( $it->menu_item_parent ) ? (int) $it->menu_item_parent : 0;
			if ( ! isset( $child_of[ $parent ] ) ) {
				$child_of[ $parent ] = array();
			}
			$child_of[ $parent ][] = $id;
		}

		return array(
			'by_id'    => $by_id,
			'child_of' => $child_of,
		);
	}

	/**
	 * Render one section for menu_node.
	 *
	 * Contract:
	 * - Blocks = source_id's depth-1 children.
	 * - depth=1: show only block titles
	 * - depth=2: block title + level2 links
	 * - depth=3: level2 links + their level3 children
	 *
	 * @param array $section
	 * @param array $index
	 * @return string
	 */
	private function render_section_menu_node( $section, $index ) {
		$source_id = (int) $section['source_id'];
		$columns   = (int) $section['columns'];
		$depth     = (int) $section['depth'];

		if ( $columns < 1 ) {
			$columns = 1;
		}
		if ( $columns > 6 ) {
			$columns = 6;
		}
		if ( $depth < 1 ) {
			$depth = 1;
		}
		if ( $depth > 3 ) {
			$depth = 3;
		}

		$blocks = $this->get_blocks( $source_id, $depth, $index );

		// Balanced distribution: greedy by weight
		$columns_blocks = $this->balance_blocks_into_columns( $blocks, $columns );

		ob_start();
		?>
		<div class="hm-mm-section" data-sec-id="<?php echo esc_attr( $section['id'] ); ?>">
			<?php if ( ! empty( $section['show_title'] ) && '' !== trim( (string) $section['title'] ) ) : ?>
				<div class="hm-mm-section__title"><?php echo esc_html( $section['title'] ); ?></div>
			<?php endif; ?>

			<div class="hm-mm-section__cols" style="--hm-mm-cols: <?php echo esc_attr( (string) $columns ); ?>;">
				<?php for ( $c = 0; $c < $columns; $c++ ) : ?>
					<div class="hm-mm-col">
						<?php
						$col_blocks = isset( $columns_blocks[ $c ] ) ? $columns_blocks[ $c ] : array();
						foreach ( $col_blocks as $block ) {
							echo $this->render_block( $block, $depth );
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
	 * Blocks are depth-1 children of source_id.
	 *
	 * Each block:
	 * - title, url
	 * - links2: list of level2 items (each can have links3)
	 * - weight: used for balancing (count of visible links)
	 *
	 * @param int   $source_id
	 * @param int   $depth
	 * @param array $index
	 * @return array
	 */
	private function get_blocks( $source_id, $depth, $index ) {
		$by_id    = $index['by_id'];
		$child_of = $index['child_of'];

		$block_ids = isset( $child_of[ $source_id ] ) ? $child_of[ $source_id ] : array();
		$blocks    = array();

		foreach ( $block_ids as $bid ) {
			if ( ! isset( $by_id[ $bid ] ) ) {
				continue;
			}
			$block_item = $by_id[ $bid ];

			$block = array(
				'id'     => (int) $block_item->ID,
				'title'  => (string) $block_item->title,
				'url'    => isset( $block_item->url ) ? (string) $block_item->url : '',
				'links2' => array(),
				'weight' => 1,
			);

			if ( $depth >= 2 ) {
				$lvl2_ids = isset( $child_of[ (int) $block_item->ID ] ) ? $child_of[ (int) $block_item->ID ] : array();

				foreach ( $lvl2_ids as $l2id ) {
					if ( ! isset( $by_id[ $l2id ] ) ) {
						continue;
					}
					$l2 = $by_id[ $l2id ];

					$link2 = array(
						'id'     => (int) $l2->ID,
						'title'  => (string) $l2->title,
						'url'    => isset( $l2->url ) ? (string) $l2->url : '',
						'links3' => array(),
					);

					if ( $depth >= 3 ) {
						$lvl3_ids = isset( $child_of[ (int) $l2->ID ] ) ? $child_of[ (int) $l2->ID ] : array();
						foreach ( $lvl3_ids as $l3id ) {
							if ( ! isset( $by_id[ $l3id ] ) ) {
								continue;
							}
							$l3                 = $by_id[ $l3id ];
							$link2['links3'][] = array(
								'id'    => (int) $l3->ID,
								'title' => (string) $l3->title,
								'url'   => isset( $l3->url ) ? (string) $l3->url : '',
							);
						}
					}

					$block['links2'][] = $link2;
				}
			}

			$block['weight'] = $this->compute_block_weight( $block, $depth );
			$blocks[]        = $block;
		}

		return $blocks;
	}

	/**
	 * Compute block "size" to balance columns.
	 *
	 * @param array $block
	 * @param int   $depth
	 * @return int
	 */
	private function compute_block_weight( $block, $depth ) {
		$w = 1; // block title

		if ( $depth >= 2 ) {
			$w += count( $block['links2'] );
		}

		if ( $depth >= 3 && ! empty( $block['links2'] ) ) {
			foreach ( $block['links2'] as $l2 ) {
				if ( ! empty( $l2['links3'] ) ) {
					$w += count( $l2['links3'] );
				}
			}
		}

		return max( 1, (int) $w );
	}

	/**
	 * Greedy load-balance blocks across N columns by weight.
	 *
	 * @param array $blocks
	 * @param int   $columns
	 * @return array
	 */
	private function balance_blocks_into_columns( $blocks, $columns ) {
		if ( $columns < 1 ) {
			$columns = 1;
		}
		$out  = array();
		$load = array();

		for ( $i = 0; $i < $columns; $i++ ) {
			$out[ $i ]  = array();
			$load[ $i ] = 0;
		}

		usort(
			$blocks,
			function( $a, $b ) {
				$aw = isset( $a['weight'] ) ? (int) $a['weight'] : 0;
				$bw = isset( $b['weight'] ) ? (int) $b['weight'] : 0;
				return $bw <=> $aw;
			}
		);

		foreach ( $blocks as $block ) {
			$min_i = 0;
			for ( $i = 1; $i < $columns; $i++ ) {
				if ( $load[ $i ] < $load[ $min_i ] ) {
					$min_i = $i;
				}
			}
			$out[ $min_i ][] = $block;
			$load[ $min_i ] += isset( $block['weight'] ) ? (int) $block['weight'] : 1;
		}

		return $out;
	}

	/**
	 * Render a single block.
	 *
	 * @param array $block
	 * @param int   $depth
	 * @return string
	 */
	private function render_block( $block, $depth ) {
		$title = isset( $block['title'] ) ? (string) $block['title'] : '';
		$url   = isset( $block['url'] ) ? (string) $block['url'] : '';

		ob_start();
		?>
		<div class="hm-mm-block">
			<div class="hm-mm-block__title">
				<?php if ( '' !== trim( $url ) ) : ?>
					<a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $title ); ?></a>
				<?php else : ?>
					<span><?php echo esc_html( $title ); ?></span>
				<?php endif; ?>
			</div>

			<?php if ( $depth >= 2 && ! empty( $block['links2'] ) ) : ?>
				<ul class="hm-mm-links">
					<?php foreach ( $block['links2'] as $l2 ) : ?>
						<li class="hm-mm-link">
							<?php if ( ! empty( $l2['url'] ) ) : ?>
								<a href="<?php echo esc_url( (string) $l2['url'] ); ?>"><?php echo esc_html( (string) $l2['title'] ); ?></a>
							<?php else : ?>
								<span><?php echo esc_html( (string) $l2['title'] ); ?></span>
							<?php endif; ?>

							<?php if ( $depth >= 3 && ! empty( $l2['links3'] ) ) : ?>
								<ul class="hm-mm-sublinks">
									<?php foreach ( $l2['links3'] as $l3 ) : ?>
										<li class="hm-mm-sublink">
											<?php if ( ! empty( $l3['url'] ) ) : ?>
												<a href="<?php echo esc_url( (string) $l3['url'] ); ?>"><?php echo esc_html( (string) $l3['title'] ); ?></a>
											<?php else : ?>
												<span><?php echo esc_html( (string) $l3['title'] ); ?></span>
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
