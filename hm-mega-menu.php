<?php
/**
 * Plugin Name:       HM Mega Menu
 * Plugin URI:        https://wisdomrainmusic.com
 * Description:       Full-width mega menu panel injection without mutating WordPress menu items.
 * Version: 2.0.11
 * Author:            WisdomRain
 * Text Domain:       hm-mega-menu
 * Domain Path:       /languages
 *
 * Requires at least: 6.0
 * Requires PHP:      7.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define('HM_MM_VERSION', '2.0.11');
define( 'HM_MM_PLUGIN_FILE', __FILE__ );
define( 'HM_MM_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'HM_MM_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

require_once HM_MM_PLUGIN_DIR . 'includes/class-hm-mega-menu.php';

/**
 * Begin execution of the plugin.
 */
function hm_mm_run() {
	$plugin = new HM_Mega_Menu();
	$plugin->run();
}
hm_mm_run();
