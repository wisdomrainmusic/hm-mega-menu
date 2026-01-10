<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class HM_MM_Frontend_Hooks {

	const META_ENABLED = '_hm_mm_enabled';

	public static function init() {
		add_filter( 'nav_menu_css_class', array( __CLASS__, 'add_menu_item_classes' ), 10, 4 );
		add_filter( 'walker_nav_menu_start_el', array( __CLASS__, 'inject_mega_panel' ), 10, 4 );
	}

	public static function add_menu_item_classes( $classes, $item, $args, $depth ) {
		$enabled = get_post_meta( $item->ID, self::META_ENABLED, true );

		if ( $enabled === '1' ) {
			$classes[] = 'hm-has-mega';
		}

		return $classes;
	}

	public static function inject_mega_panel( $item_output, $item, $depth, $args ) {
		$enabled = get_post_meta( $item->ID, self::META_ENABLED, true );
		if ( $enabled !== '1' ) {
			return $item_output;
		}

		$cols = (int) get_post_meta( $item->ID, '_hm_mm_cols', true );
		$cols = $cols ? $cols : 4;

		$parent = (int) get_post_meta( $item->ID, '_hm_mm_parent_cat', true );
		$depthn = (int) get_post_meta( $item->ID, '_hm_mm_depth', true );
		$depthn = $depthn ? $depthn : 2;

		$limit = (int) get_post_meta( $item->ID, '_hm_mm_limit', true );
		$limit = $limit ? $limit : 24;

		$content = self::render_woo_columns( $parent, $cols, $depthn, $limit );

		$panel = '
			<div class="hm-mega-panel" aria-hidden="true">
				<div class="hm-mega-inner" style="grid-template-columns: repeat(' . (int) $cols . ', minmax(0, 1fr));">
					' . $content . '
				</div>
			</div>
		';

		return $item_output . $panel;
	}

	private static function render_woo_columns( $parent_term_id, $cols, $depth, $limit ) {
		if ( ! taxonomy_exists( 'product_cat' ) ) {
			return '<div class="hm-mega-col"><p>WooCommerce categories not found.</p></div>';
		}

		if ( $parent_term_id <= 0 ) {
			return '<div class="hm-mega-col"><p>Select a parent category in menu settings.</p></div>';
		}

		$children = get_terms(
			array(
				'taxonomy' => 'product_cat',
				'hide_empty' => false,
				'parent' => $parent_term_id,
				'orderby' => 'name',
				'order' => 'ASC',
				'number' => $limit,
			)
		);

		if ( is_wp_error( $children ) || empty( $children ) ) {
			return '<div class="hm-mega-col"><p>No subcategories found.</p></div>';
		}

		$chunks = array_chunk( $children, (int) ceil( count( $children ) / max( 1, $cols ) ) );

		$html = '';
		for ( $i = 0; $i < $cols; $i++ ) {
			$chunk = isset( $chunks[ $i ] ) ? $chunks[ $i ] : array();

			$html .= '<div class="hm-mega-col">';
			$html .= '<ul class="hm-mega-links">';

			foreach ( $chunk as $term ) {
				$url = get_term_link( $term );
				if ( is_wp_error( $url ) ) {
					continue;
				}
				$html .= '<li><a href="' . esc_url( $url ) . '">' . esc_html( $term->name ) . '</a></li>';
			}

			$html .= '</ul>';
			$html .= '</div>';
		}

		return $html;
	}
}
