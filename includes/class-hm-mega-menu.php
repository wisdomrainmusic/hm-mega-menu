<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once HM_MM_PLUGIN_DIR . 'includes/class-hm-mega-menu-loader.php';
require_once HM_MM_PLUGIN_DIR . 'includes/class-hm-mega-menu-i18n.php';
require_once HM_MM_PLUGIN_DIR . 'includes/class-hm-mega-menu-storage.php';
require_once HM_MM_PLUGIN_DIR . 'includes/class-hm-mega-menu-admin.php';
require_once HM_MM_PLUGIN_DIR . 'includes/class-hm-mega-menu-public.php';

class HM_Mega_Menu {

	/**
	 * @var HM_Mega_Menu_Loader
	 */
	protected $loader;

	/**
	 * @var string
	 */
	protected $plugin_name = 'hm-mega-menu';

	/**
	 * @var string
	 */
	protected $version;

	public function __construct() {
		$this->version = defined( 'HM_MM_VERSION' ) ? HM_MM_VERSION : '2.0.0';
		$this->loader  = new HM_Mega_Menu_Loader();

		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	private function set_locale() {
		$i18n = new HM_Mega_Menu_I18n();
		$this->loader->add_action( 'plugins_loaded', $i18n, 'load_plugin_textdomain' );
	}

	private function define_admin_hooks() {
		$admin = new HM_Mega_Menu_Admin();

		$this->loader->add_action( 'admin_menu', $admin, 'register_menu' );
		$this->loader->add_action( 'admin_enqueue_scripts', $this, 'register_admin_assets', 5, 1 );
		$this->loader->add_action( 'admin_enqueue_scripts', $admin, 'enqueue_assets', 20, 1 );
		$this->loader->add_action( 'admin_post_hm_mm_save_builder_v2', $admin, 'handle_save' );
	}

	private function define_public_hooks() {
		$public = new HM_Mega_Menu_Public();

		// Register handles (already in Commit 1/3 pattern).
		$this->loader->add_action( 'wp_enqueue_scripts', $this, 'register_public_assets' );

		// Enqueue only if enabled configs exist.
		$this->loader->add_action( 'wp_enqueue_scripts', $public, 'maybe_enqueue_assets' );

		// Output-only integration: add class + inject panel via filters.
		$this->loader->add_filter( 'nav_menu_css_class', $public, 'filter_nav_menu_css_class', 20, 4 );
		$this->loader->add_filter( 'walker_nav_menu_start_el', $public, 'filter_start_el_inject_panel', 20, 4 );
	}

	public function run() {
		$this->loader->run();
	}

	public function register_admin_assets() {
		wp_register_style(
			'hm-mm-admin',
			HM_MM_PLUGIN_URL . 'assets/admin/admin.css',
			array(),
			$this->version
		);

		wp_register_script(
			'hm-mm-admin',
			HM_MM_PLUGIN_URL . 'assets/admin/admin.js',
			array( 'jquery' ),
			$this->version,
			true
		);
	}

	public function register_public_assets() {
		wp_register_style(
			'hm-mm-public',
			HM_MM_PLUGIN_URL . 'assets/public/public.css',
			array(),
			$this->version
		);

		wp_register_script(
			'hm-mm-public',
			HM_MM_PLUGIN_URL . 'assets/public/public.js',
			array(),
			$this->version,
			true
		);
	}
}
