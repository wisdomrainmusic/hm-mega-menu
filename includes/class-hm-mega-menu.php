<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once HM_MM_PLUGIN_DIR . 'includes/class-hm-mega-menu-loader.php';
require_once HM_MM_PLUGIN_DIR . 'includes/class-hm-mega-menu-i18n.php';

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
		// Builder sayfası Commit 3'te gelecek.
		$this->loader->add_action( 'admin_enqueue_scripts', $this, 'enqueue_admin_assets' );
	}

	private function define_public_hooks() {
		// Frontend injection + walker Commit 4'te gelecek.
		$this->loader->add_action( 'wp_enqueue_scripts', $this, 'enqueue_public_assets' );
	}

	public function run() {
		$this->loader->run();
	}

	public function enqueue_admin_assets() {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

		// Şimdilik sadece plugin admin sayfaları gelince yükleyeceğiz (Commit 3'te netleşecek).
		// Burada erken yüklemeyi minimum tutuyoruz.
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

		// Builder sayfası slug'ı Commit 3'te belli olunca koşullu enqueue yapılacak.
	}

	public function enqueue_public_assets() {
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

		// Commit 4/6: mega aktif olduğunda enqueue edeceğiz (şimdilik register).
	}
}
