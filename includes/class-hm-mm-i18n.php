<?php
// File: includes/class-hm-mm-i18n.php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Internationalization.
 *
 * @package HM_Mega_Menu
 */
final class HM_MM_I18n {

	/**
	 * Text domain.
	 *
	 * @var string
	 */
	private $domain;

	/**
	 * Constructor.
	 *
	 * @param string $domain Text domain.
	 */
	public function __construct( $domain ) {
		$this->domain = (string) $domain;
	}

	/**
	 * Load plugin textdomain.
	 *
	 * @return void
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain(
			$this->domain,
			false,
			dirname( HM_MM_PLUGIN_BASENAME ) . '/languages/'
		);
	}
}
