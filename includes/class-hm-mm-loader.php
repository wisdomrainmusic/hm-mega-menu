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

    // Admin'de asla dokunma
    if ( is_admin() ) {
      return $args;
    }

    /**
     * IMPORTANT:
     * Mega walker sadece hedef menüde çalışmalı.
     * Menü adın: "Shop Categories"
     * (WP menü adıyla birebir aynı olmalı)
     */
    $target_menu_name = 'Shop Categories';

    $menu_ok = false;

    // args['menu'] bazen ID/slug/name gelebiliyor
    if ( isset($args['menu']) ) {
      if ( is_string($args['menu']) && $args['menu'] === $target_menu_name ) {
        $menu_ok = true;
      } elseif ( is_object($args['menu']) && ! empty($args['menu']->name) && $args['menu']->name === $target_menu_name ) {
        $menu_ok = true;
      }
    }

    // theme_location ile geliyorsa, burayı şimdilik kapalı tutuyoruz
    // (istersen sonra ekleriz)
    if ( ! $menu_ok ) {
      return $args;
    }

    // Sadece hedef menüde walker set et
    $args['walker'] = new HM_MM_Walker();

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
