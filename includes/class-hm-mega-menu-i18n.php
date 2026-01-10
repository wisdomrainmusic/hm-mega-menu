<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class HM_Mega_Menu_I18n {

	public function load_plugin_textdomain() {
		load_plugin_textdomain(
			'hm-mega-menu',
			false,
			dirname( plugin_basename( HM_MM_PLUGIN_FILE ) ) . '/languages/'
		);
	}
}
