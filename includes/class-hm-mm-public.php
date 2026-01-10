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

	/**
	 * Plugin name.
	 *
	 * @var string
	 */
	private $plugin_name;

	/**
	 * Plugin version.
	 *
	 * @var string
	 */
	private $version;

	/**
	 * Constructor.
	 *
	 * @param string $plugin_name Plugin name.
	 * @param string $version Plugin version.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = (string) $plugin_name;
		$this->version     = (string) $version;

		require_once HM_MM_PLUGIN_DIR . 'includes/class-hm-mm-render.php';
		require_once HM_MM_PLUGIN_DIR . 'includes/class-hm-mm-walker.php';
	}

	/**
	 * Enqueue public assets (placeholder).
	 *
	 * @return void
	 */
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
	 * This does not alter menu items; it only affects output generation.
	 *
	 * @param array $args wp_nav_menu args.
	 * @return array
	 */
	public function filter_nav_menu_args( $args ) {
		if ( is_admin() ) {
			return $args;
		}

		// If a theme/plugin already provided a custom walker, respect it for MVP.
		if ( isset( $args['walker'] ) && $args['walker'] instanceof Walker ) {
			return $args;
		}

		$args['walker'] = new HM_MM_Walker();
		return $args;
	}
}
