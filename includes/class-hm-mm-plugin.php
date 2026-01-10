<?php
// File: includes/class-hm-mm-plugin.php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Core plugin class.
 *
 * @package HM_Mega_Menu
 */
final class HM_MM_Plugin {

	/**
	 * Loader that maintains and registers all hooks.
	 *
	 * @var HM_MM_Loader
	 */
	private $loader;

	/**
	 * Plugin textdomain.
	 *
	 * @var string
	 */
	private $plugin_name = 'hm-mega-menu';

	/**
	 * Plugin version.
	 *
	 * @var string
	 */
	private $version;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->version = defined( 'HM_MM_VERSION' ) ? HM_MM_VERSION : '2.0.0';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	/**
	 * Load required dependencies for this plugin.
	 *
	 * @return void
	 */
	private function load_dependencies() {
		require_once HM_MM_PLUGIN_DIR . 'includes/class-hm-mm-loader.php';
		require_once HM_MM_PLUGIN_DIR . 'includes/class-hm-mm-i18n.php';
		require_once HM_MM_PLUGIN_DIR . 'includes/class-hm-mm-admin.php';
		require_once HM_MM_PLUGIN_DIR . 'includes/class-hm-mm-public.php';

		$this->loader = new HM_MM_Loader();
	}

	/**
	 * Define locale for internationalization.
	 *
	 * @return void
	 */
	private function set_locale() {
		$i18n = new HM_MM_I18n( $this->plugin_name );

		$this->loader->add_action( 'plugins_loaded', $i18n, 'load_plugin_textdomain' );
	}

	/**
	 * Register admin hooks.
	 *
	 * @return void
	 */
	private function define_admin_hooks() {
		$admin = new HM_MM_Admin( $this->plugin_name, $this->version );

		$this->loader->add_action( 'admin_enqueue_scripts', $admin, 'enqueue_assets' );
	}

	/**
	 * Register public hooks.
	 *
	 * @return void
	 */
	private function define_public_hooks() {
		$public = new HM_MM_Public( $this->plugin_name, $this->version );

		$this->loader->add_action( 'wp_enqueue_scripts', $public, 'enqueue_assets' );
	}

	/**
	 * Run the loader to execute hooks.
	 *
	 * @return void
	 */
	public function run() {
		if ( $this->loader instanceof HM_MM_Loader ) {
			$this->loader->run();
		}
	}
}
