<?php
// File: includes/class-hm-mm-admin.php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin functionality (MVP: enqueue placeholders only).
 *
 * @package HM_Mega_Menu
 */
final class HM_MM_Admin {

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
	}

	/**
	 * Enqueue admin assets (placeholder).
	 *
	 * @param string $hook_suffix Current admin page hook.
	 * @return void
	 */
	public function enqueue_assets( $hook_suffix ) {
		// Intentionally minimal for Commit 1.
		// Builder page assets will be conditionally loaded in later commits.
		wp_register_style(
			$this->plugin_name . '-admin',
			HM_MM_PLUGIN_URL . 'assets/admin/admin.css',
			array(),
			$this->version
		);

		wp_register_script(
			$this->plugin_name . '-admin',
			HM_MM_PLUGIN_URL . 'assets/admin/admin.js',
			array( 'jquery' ),
			$this->version,
			true
		);
	}
}
