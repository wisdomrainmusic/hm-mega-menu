<?php
/**
 * Plugin Name: HM Mega Menu
 * Description: Lightweight mega menu builder for WordPress nav menus (WooCommerce categories supported).
 * Version: 0.1.0
 * Author: wisdomrainmusic
 * Text Domain: hm-mega-menu
 */

if ( ! defined('ABSPATH') ) {
  exit;
}

define('HM_MM_VERSION', '0.1.0');
define('HM_MM_PATH', plugin_dir_path(__FILE__));
define('HM_MM_URL', plugin_dir_url(__FILE__));

require_once HM_MM_PATH . 'includes/class-hm-mm-loader.php';

function hm_mm_boot() {
  return HM_MM_Loader::instance();
}
hm_mm_boot();
