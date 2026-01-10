<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class HM_MM_Frontend_Hooks {

	const META_ENABLED = '_hm_mm_enabled';

	public static function init() {
		add_filter( 'wp_nav_menu_args', array( __CLASS__, 'maybe_force_walker' ), 20, 1 );
		add_filter( 'nav_menu_css_class', array( __CLASS__, 'add_menu_item_classes' ), 10, 4 );
		add_filter( 'walker_nav_menu_start_el', array( __CLASS__, 'inject_mega_panel' ), 10, 4 );

		// Standard wp_nav_menu() path (some themes bypass this)
		add_filter( 'wp_nav_menu_objects', array( __CLASS__, 'remove_children_for_mega_items' ), 20, 2 );

		// Theme-safe fallbacks (Astra/Elementor/header builders)
		add_filter( 'wp_get_nav_menu_items', array( __CLASS__, 'filter_items_at_source' ), 20, 3 );
		add_filter( 'wp_nav_menu_items', array( __CLASS__, 'fallback_inject_panels' ), 20, 2 );
	}

	/**
	 * Some themes/header builders bypass or short-circuit walker_nav_menu_start_el injection.
	 * As a fallback, force our Walker only when this menu contains at least one mega-enabled item.
	 */
	public static function maybe_force_walker( $args ) {
		$menu_obj = null;

		// If a menu is explicitly provided.
		if ( ! empty( $args['menu'] ) ) {
			$menu_obj = wp_get_nav_menu_object( $args['menu'] );
		}

		// If a theme_location is used, resolve the assigned menu.
		if ( ! $menu_obj && ! empty( $args['theme_location'] ) ) {
			$locations = get_nav_menu_locations();
			if ( ! empty( $locations[ $args['theme_location'] ] ) ) {
				$menu_obj = wp_get_nav_menu_object( (int) $locations[ $args['theme_location'] ] );
			}
		}

		if ( ! $menu_obj || empty( $menu_obj->term_id ) ) {
			return $args;
		}

		$items = wp_get_nav_menu_items( (int) $menu_obj->term_id );
		if ( empty( $items ) || ! is_array( $items ) ) {
			return $args;
		}

		$has_mega = false;
		foreach ( $items as $it ) {
			$enabled = get_post_meta( $it->ID, self::META_ENABLED, true );
			if ( $enabled === '1' ) {
				$has_mega = true;
				break;
			}
		}

		if ( ! $has_mega ) {
			return $args;
		}

		// Force HM walker only for this menu render.
		require_once HM_MM_PATH . 'includes/class-hm-mm-walker.php';
		$args['walker'] = new HM_MM_Walker();

		return $args;
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

		return $item_output . self::build_panel_html( $item->ID );
	}

	private static function build_panel_html( $menu_item_id ) {
		$cols = (int) get_post_meta( $menu_item_id, '_hm_mm_cols', true );
		$cols = $cols ? $cols : 4;

		$parent = (int) get_post_meta( $menu_item_id, '_hm_mm_parent_cat', true );
		$depthn = (int) get_post_meta( $menu_item_id, '_hm_mm_depth', true );
		$depthn = $depthn ? $depthn : 2;

		$limit = (int) get_post_meta( $menu_item_id, '_hm_mm_limit', true );
		$limit = $limit ? $limit : 24;

		$content = self::render_woo_columns( $parent, $cols, $depthn, $limit );

		return '
			<div class="hm-mega-panel" aria-hidden="true">
				<div class="hm-mega-inner" style="grid-template-columns: repeat(' . (int) $cols . ', minmax(0, 1fr));">
					' . $content . '
				</div>
			</div>
		';
	}

	public static function render_woo_columns( $parent_term_id, $cols, $depth, $limit ) {
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

	public static function remove_children_for_mega_items( $items, $args ) {
		if ( empty( $items ) || ! is_array( $items ) ) {
			return $items;
		}

		$mega_parents = array();

		// Find mega-enabled parents
		foreach ( $items as $it ) {
			$enabled = get_post_meta( $it->ID, self::META_ENABLED, true );
			if ( $enabled === '1' ) {
				$mega_parents[ (int) $it->ID ] = true;
			}
		}

		if ( empty( $mega_parents ) ) {
			return $items;
		}

		// Remove direct children (and their children) of mega parents
		$remove = array();

		$changed = true;
		while ( $changed ) {
			$changed = false;

			foreach ( $items as $it ) {
				$pid = (int) $it->menu_item_parent;

				if ( isset( $mega_parents[ $pid ] ) || isset( $remove[ $pid ] ) ) {
					if ( ! isset( $remove[ (int) $it->ID ] ) ) {
						$remove[ (int) $it->ID ] = true;
						$changed = true;
					}
				}
			}
		}

		if ( empty( $remove ) ) {
			return $items;
		}

		$filtered = array();
		foreach ( $items as $it ) {
			if ( isset( $remove[ (int) $it->ID ] ) ) {
				continue;
			}
			$filtered[] = $it;
		}

		return $filtered;
	}

	/**
	 * Fallback: remove children at the source so header builders can't re-add them.
	 */
	public static function filter_items_at_source( $items, $menu, $args ) {
		// Reuse the same logic (2nd param is "args" in wp_nav_menu_objects; here it's $args but safe).
		return self::remove_children_for_mega_items( $items, $args );
	}

	/**
	 * Fallback: if walker injection didn't run, inject panels into final HTML.
	 * This targets <li> elements that already have hm-has-mega class (added by nav_menu_css_class),
	 * and appends the panel right after the first closing </a> inside that <li>.
	 */
	public static function fallback_inject_panels( $items_html, $args ) {
		if ( empty( $items_html ) || strpos( $items_html, 'hm-has-mega' ) === false ) {
			return $items_html;
		}
		// If panel already exists, do nothing.
		if ( strpos( $items_html, 'hm-mega-panel' ) !== false ) {
			return $items_html;
		}

		// Get menu items for this render (best-effort).
		$menu_obj = null;
		if ( ! empty( $args->menu ) ) {
			$menu_obj = wp_get_nav_menu_object( $args->menu );
		}
		if ( ! $menu_obj && ! empty( $args->theme_location ) ) {
			$locations = get_nav_menu_locations();
			if ( ! empty( $locations[ $args->theme_location ] ) ) {
				$menu_obj = wp_get_nav_menu_object( (int) $locations[ $args->theme_location ] );
			}
		}
		if ( ! $menu_obj || empty( $menu_obj->term_id ) ) {
			return $items_html;
		}

		$items = wp_get_nav_menu_items( (int) $menu_obj->term_id );
		if ( empty( $items ) ) {
			return $items_html;
		}

		foreach ( $items as $it ) {
			$enabled = get_post_meta( $it->ID, self::META_ENABLED, true );
			if ( $enabled !== '1' ) {
				continue;
			}
			// Append panel after the anchor of the matching menu item ID.
			// Look for the menu-item-123 class which is standard in WP menus.
			$needle = 'menu-item-' . (int) $it->ID;
			if ( strpos( $items_html, $needle ) === false ) {
				continue;
			}
			$panel = self::build_panel_html( $it->ID );

			$items_html = preg_replace(
				'/(<li[^>]*class="[^"]*' . preg_quote( $needle, '/' ) . '[^"]*"[^>]*>.*?<\\/a>)/s',
				'$1' . $panel,
				$items_html,
				1
			);
		}

		return $items_html;
	}
}
