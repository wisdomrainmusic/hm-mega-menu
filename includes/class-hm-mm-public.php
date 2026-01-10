<?php
// File: includes/class-hm-mm-public.php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Public functionality (MVP: enqueue placeholders only).
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
}
