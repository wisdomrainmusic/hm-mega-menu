<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class HM_Mega_Menu_Storage {

	const OPTION_KEY = 'hm_mm_builder_v2';

	/**
	 * Get entire stored dataset.
	 *
	 * @return array
	 */
	public static function get_all() {
		$data = get_option( self::OPTION_KEY, array() );

		if ( ! is_array( $data ) ) {
			return array();
		}

		return $data;
	}

	/**
	 * Get config for a specific WP menu (term_id).
	 *
	 * @param int $menu_id
	 * @return array
	 */
	public static function get_menu_config( $menu_id ) {
		$menu_id = absint( $menu_id );
		if ( ! $menu_id ) {
			return self::default_menu_config();
		}

		$all = self::get_all();

		if ( isset( $all[ $menu_id ] ) && is_array( $all[ $menu_id ] ) ) {
			return self::normalize_menu_config( $all[ $menu_id ] );
		}

		return self::default_menu_config();
	}

	/**
	 * Save config for a specific WP menu (term_id).
	 *
	 * @param int   $menu_id
	 * @param array $config
	 * @return bool
	 */
	public static function save_menu_config( $menu_id, $config ) {
		$menu_id = absint( $menu_id );
		if ( ! $menu_id ) {
			return false;
		}

		$all            = self::get_all();
		$all[ $menu_id ] = self::normalize_menu_config( $config );

		// autoload false: this data can grow with sections.
		return update_option( self::OPTION_KEY, $all, false );
	}

	/**
	 * Default menu config structure.
	 *
	 * @return array
	 */
	public static function default_menu_config() {
		return array(
			'enabled'        => 0,
			'target_item_id' => 0,
			'sections'       => array(),
		);
	}

	/**
	 * Normalize and sanitize menu config.
	 *
	 * @param array $config
	 * @return array
	 */
	public static function normalize_menu_config( $config ) {
		$base = self::default_menu_config();

		if ( ! is_array( $config ) ) {
			return $base;
		}

		$enabled        = isset( $config['enabled'] ) ? (int) $config['enabled'] : 0;
		$target_item_id = isset( $config['target_item_id'] ) ? absint( $config['target_item_id'] ) : 0;

		$sections_in = array();
		if ( isset( $config['sections'] ) && is_array( $config['sections'] ) ) {
			$sections_in = $config['sections'];
		}

		$sections_out = array();
		foreach ( $sections_in as $section ) {
			$norm = self::normalize_section( $section );
			if ( $norm ) {
				$sections_out[] = $norm;
			}
		}

		return array(
			'enabled'        => $enabled ? 1 : 0,
			'target_item_id' => $target_item_id,
			'sections'       => $sections_out,
		);
	}

	/**
	 * Normalize a section structure.
	 *
	 * Section keys:
	 * - id (string)
	 * - title (string)
	 * - source_type (string) [MVP: menu_node]
	 * - source_id (int) menu item id
	 * - columns (int 1-6)
	 * - depth (int 1-3)
	 * - show_title (int 0/1)
	 * - order (int)
	 *
	 * @param mixed $section
	 * @return array|null
	 */
	public static function normalize_section( $section ) {
		if ( ! is_array( $section ) ) {
			return null;
		}

		$id = isset( $section['id'] ) ? sanitize_text_field( (string) $section['id'] ) : '';
		if ( '' === $id ) {
			// Stable ID required for drag/drop ordering.
			$id = self::generate_section_id();
		}

		$title       = isset( $section['title'] ) ? sanitize_text_field( (string) $section['title'] ) : '';
		$source_type = isset( $section['source_type'] ) ? sanitize_key( (string) $section['source_type'] ) : 'menu_node';

		// MVP lock: only menu_node allowed for now.
		if ( 'menu_node' !== $source_type ) {
			$source_type = 'menu_node';
		}

		$source_id  = isset( $section['source_id'] ) ? absint( $section['source_id'] ) : 0;
		$columns    = isset( $section['columns'] ) ? (int) $section['columns'] : 3;
		$depth      = isset( $section['depth'] ) ? (int) $section['depth'] : 2;
		$show_title = isset( $section['show_title'] ) ? (int) $section['show_title'] : 1;
		$order      = isset( $section['order'] ) ? (int) $section['order'] : 0;

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

		$show_title = $show_title ? 1 : 0;

		return array(
			'id'          => $id,
			'title'       => $title,
			'source_type' => $source_type,
			'source_id'   => $source_id,
			'columns'     => $columns,
			'depth'       => $depth,
			'show_title'  => $show_title,
			'order'       => $order,
		);
	}

	/**
	 * Generate a section ID.
	 *
	 * @return string
	 */
	public static function generate_section_id() {
		// wp_generate_uuid4 exists since WP 4.7
		if ( function_exists( 'wp_generate_uuid4' ) ) {
			return 'sec_' . wp_generate_uuid4();
		}

		return 'sec_' . md5( uniqid( (string) mt_rand(), true ) );
	}
}
