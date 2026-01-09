<?php
if ( ! defined('ABSPATH') ) {
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

    // FRONTEND
    require_once HM_MM_PATH . 'includes/class-hm-mm-walker.php';
    add_filter('wp_nav_menu_args', [$this, 'inject_walker'], 10, 1);
    add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
  }

  public function inject_walker($args) {
    // Sadece primary menüde çalışsın istersen burada şart ekleriz
    if ( empty($args['walker']) ) {
      $args['walker'] = new HM_MM_Walker();
    }
    return $args;
  }

  public function enqueue_assets() {
    wp_enqueue_style(
      'hm-mega-menu-front',
      HM_MM_URL . 'assets/front.css',
      [],
      HM_MM_VERSION
    );
  }
}
