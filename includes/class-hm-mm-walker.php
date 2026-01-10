<?php
// File: includes/class-hm-mm-walker.php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Custom walker to inject mega panel markup without mutating menu item objects.
 *
 * @package HM_Mega_Menu
 */
final class HM_MM_Walker extends Walker_Nav_Menu {

	/**
	 * Output the end of an element.
	 *
	 * We append mega panel markup right before closing </li> when enabled.
	 *
	 * @param string   $output Passed by reference. Used to append additional content.
	 * @param WP_Post  $item   Menu item data object.
	 * @param int      $depth  Depth of menu item.
	 * @param stdClass $args   An object of wp_nav_menu() arguments.
	 * @return void
	 */
	public function end_el( &$output, $item, $depth = 0, $args = null ) {
		// Only inject on top-level items (MVP).
		if ( 0 === (int) $depth && isset( $item->ID ) ) {
			$item_id = absint( $item->ID );

			if ( $item_id > 0 && 1 === HM_MM_Storage::get_enabled( $item_id ) ) {
				$schema  = HM_MM_Storage::get_schema( $item_id );
				$output .= HM_MM_Render::render_panel( $item_id, $schema );
			}
		}

		parent::end_el( $output, $item, $depth, $args );
	}
}
