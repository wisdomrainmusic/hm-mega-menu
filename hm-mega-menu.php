<?php
/**
 * Plugin Name:       HM Mega Menu
 * Plugin URI:        https://example.com
 * Description:       Full-width mega menu builder (Row/Column schema) without mutating core menu item data.
 * Version:           2.0.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            WisdomRainMusic
 * License:           GPL-2.0+
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       hm-mega-menu
 * Domain Path:       /languages
 *
 * @package HM_Mega_Menu
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'HM_MM_VERSION', '2.0.0' );
define( 'HM_MM_PLUGIN_FILE', __FILE__ );
define( 'HM_MM_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
define( 'HM_MM_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'HM_MM_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

require_once HM_MM_PLUGIN_DIR . 'includes/class-hm-mm-plugin.php';

/**
 * Begins execution of the plugin.
 *
 * @return void
 */
function hm_mm_run() {
	$plugin = new HM_MM_Plugin();
	$plugin->run();
}
hm_mm_run();
