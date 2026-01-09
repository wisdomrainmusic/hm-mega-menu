<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class HM_MM_Loader {

	private static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
			self::$instance->init();
		}
		return self::$instance;
	}

	private function __construct() {}

	private function init() {

		if ( is_admin() ) {
			require_once HM_MM_PATH . 'includes/class-hm-mm-admin-menu-fields.php';
			HM_MM_Admin_Menu_Fields::init();
		}

		// FRONTEND (theme-safe).
		require_once HM_MM_PATH . 'includes/class-hm-mm-frontend-hooks.php';
		HM_MM_Frontend_Hooks::init();

		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	public function enqueue_assets() {
		wp_enqueue_style(
			'hm-mega-menu-front',
			HM_MM_URL . 'assets/front.css',
			array(),
			HM_MM_VERSION
		);
	}
}
