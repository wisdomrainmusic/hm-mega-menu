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
	 * @param int                 $target_item_id Menu item ID.
	 * @param array<string,mixed> $schema Sanitized schema.
	 * @return string
	 */
	public static function render_panel( $target_item_id, $schema ) {
		$target_item_id = absint( $target_item_id );
		$schema         = HM_MM_Schema::sanitize( $schema );

		$rows = isset( $schema['rows'] ) && is_array( $schema['rows'] ) ? $schema['rows'] : array();

		ob_start();
		?>
		<div class="hm-mega-panel" data-hm-mm="1" aria-hidden="true">
			<div class="hm-mega-inner">
				<?php if ( empty( $rows ) ) : ?>
					<div class="hm-mega-row hm-mega-row--empty">
						<?php echo esc_html__( 'Mega menu enabled, but no rows configured yet.', 'hm-mega-menu' ); ?>
					</div>
				<?php else : ?>
					<?php foreach ( $rows as $row ) : ?>
						<?php
							$title        = isset( $row['title'] ) ? (string) $row['title'] : '';
							$columns      = isset( $row['columns'] ) ? absint( $row['columns'] ) : 4;
							$depth        = isset( $row['depth'] ) ? absint( $row['depth'] ) : 3;
							$show_heading = isset( $row['show_heading'] ) ? absint( $row['show_heading'] ) : 1;

							$columns = ( $columns >= 1 && $columns <= 6 ) ? $columns : 4;
							$depth   = ( $depth >= 1 && $depth <= 3 ) ? $depth : 3;
							?>
							<div class="hm-mega-row" data-cols="<?php echo esc_attr( (string) $columns ); ?>" data-depth="<?php echo esc_attr( (string) $depth ); ?>">
								<?php if ( $show_heading && '' !== $title ) : ?>
									<div class="hm-mega-row-title"><?php echo esc_html( $title ); ?></div>
								<?php endif; ?>
								<div class="hm-mega-row-body">
									<!-- Commit 5 will render menu-node children + distribute into columns -->
									<div class="hm-mega-row-placeholder">
										<?php
										echo esc_html(
											sprintf(
												/* translators: 1: columns, 2: depth */
												__( 'Row placeholder (cols: %1$d, depth: %2$d).', 'hm-mega-menu' ),
												(int) $columns,
												(int) $depth
											)
										);
										?>
									</div>
								</div>
							</div>
						<?php endforeach; ?>
				<?php endif; ?>
			</div>
		</div>
		<?php
		return (string) ob_get_clean();
	}
}
