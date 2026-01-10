<?php
if ( ! defined('ABSPATH') ) {
  exit;
}

class HM_MM_Walker extends Walker_Nav_Menu {
  /**
   * Intentionally empty.
   * This walker exists only to force the standard core Walker_Nav_Menu rendering flow
   * so that 'walker_nav_menu_start_el' runs reliably across themes/builders.
   *
   * Mega panel HTML must be rendered ONLY by HM_MM_Frontend_Hooks::inject_mega_panel()
   * to avoid duplicate panels.
   */
}
