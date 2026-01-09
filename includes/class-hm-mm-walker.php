<?php
if ( ! defined('ABSPATH') ) {
  exit;
}

class HM_MM_Walker extends Walker_Nav_Menu {

  public function start_lvl( &$output, $depth = 0, $args = null ) {
    // Normal submenu'leri kapatıyoruz
    return;
  }

  public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {

    $enabled = get_post_meta($item->ID, '_hm_mm_enabled', true);

    $classes = empty($item->classes) ? [] : (array) $item->classes;
    if ($enabled === '1') {
      $classes[] = 'hm-has-mega';
    }

    $class_names = implode(' ', array_map('esc_attr', $classes));

    $output .= '<li class="' . $class_names . '">';

    $atts  = ! empty($item->url) ? ' href="' . esc_url($item->url) . '"' : '';
    $title = esc_html($item->title);

    $output .= '<a' . $atts . '>' . $title . '</a>';

    if ($enabled === '1') {
      $output .= '
        <div class="hm-mega-panel">
          <div class="hm-mega-inner">
            <div>
              <h4 class="hm-mega-col-title">Demo Column 1</h4>
              <ul class="hm-mega-links">
                <li><a href="#">Link 1</a></li>
                <li><a href="#">Link 2</a></li>
              </ul>
            </div>
            <div>
              <h4 class="hm-mega-col-title">Demo Column 2</h4>
              <ul class="hm-mega-links">
                <li><a href="#">Link 3</a></li>
                <li><a href="#">Link 4</a></li>
              </ul>
            </div>
            <div>
              <h4 class="hm-mega-col-title">Demo Column 3</h4>
              <ul class="hm-mega-links">
                <li><a href="#">Link 5</a></li>
                <li><a href="#">Link 6</a></li>
              </ul>
            </div>
            <div>
              <h4 class="hm-mega-col-title">Demo Column 4</h4>
              <ul class="hm-mega-links">
                <li><a href="#">Link 7</a></li>
                <li><a href="#">Link 8</a></li>
              </ul>
            </div>
          </div>
        </div>
      ';
    }
  }

  public function end_el( &$output, $item, $depth = 0, $args = null ) {
    $output .= '</li>';
  }
}
