<?php
if ( ! defined('ABSPATH') ) {
  exit;
}

class HM_MM_Walker extends Walker_Nav_Menu {

  public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
    // Let core build normal markup.
    parent::start_el( $output, $item, $depth, $args, $id );

    $enabled = get_post_meta( $item->ID, '_hm_mm_enabled', true );
    if ( $enabled !== '1' ) {
      return;
    }

    // Mirror the same settings as Frontend_Hooks injection.
    $cols = (int) get_post_meta( $item->ID, '_hm_mm_cols', true );
    $cols = $cols ? $cols : 4;

    $parent = (int) get_post_meta( $item->ID, '_hm_mm_parent_cat', true );
    $depthn = (int) get_post_meta( $item->ID, '_hm_mm_depth', true );
    $depthn = $depthn ? $depthn : 2;

    $limit = (int) get_post_meta( $item->ID, '_hm_mm_limit', true );
    $limit = $limit ? $limit : 24;

    // Use the same renderer to avoid divergence.
    require_once HM_MM_PATH . 'includes/class-hm-mm-frontend-hooks.php';
    $content = HM_MM_Frontend_Hooks::render_woo_columns( $parent, $cols, $depthn, $limit );

    $output .= '
      <div class="hm-mega-panel" aria-hidden="true">
        <div class="hm-mega-inner" style="grid-template-columns: repeat(' . (int) $cols . ', minmax(0, 1fr));">
          ' . $content . '
        </div>
      </div>
    ';
  }
}
