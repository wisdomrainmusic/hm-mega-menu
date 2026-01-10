<?php
// File: includes/class-hm-mm-public.php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Public functionality.
 *
 * @package HM_Mega_Menu
 */
final class HM_MM_Public {

	private $plugin_name;
	private $version;

	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = (string) $plugin_name;
		$this->version     = (string) $version;

		require_once HM_MM_PLUGIN_DIR . 'includes/class-hm-mm-menu.php';
		require_once HM_MM_PLUGIN_DIR . 'includes/class-hm-mm-render.php';
		require_once HM_MM_PLUGIN_DIR . 'includes/class-hm-mm-walker.php';
	}

	public function enqueue_assets() {
		wp_register_style(
			$this->plugin_name . '-public',
			HM_MM_PLUGIN_URL . 'assets/public/public.css',
			array(),
			$this->version
		);

		wp_register_script(
			$this->plugin_name . '-public',
			HM_MM_PLUGIN_URL . 'assets/public/public.js',
			array(),
			$this->version,
			true
		);
	}

	/**
	 * Hook: set our walker on frontend (only when theme didn't set a custom walker).
	 *
	 * @param array $args wp_nav_menu args.
	 * @return array
	 */
	public function filter_nav_menu_args( $args ) {
		if ( is_admin() ) {
			return $args;
		}

		// Respect existing walker if any (MVP).
		if ( isset( $args['walker'] ) && $args['walker'] instanceof Walker ) {
			return $args;
		}

		$menu_term_id = HM_MM_Menu::resolve_menu_term_id( $args );
		$items        = HM_MM_Menu::get_items( $menu_term_id );

		$args['walker'] = new HM_MM_Walker( $items );

		return $args;
	}
}
