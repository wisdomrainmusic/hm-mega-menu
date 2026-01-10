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
	 * Current menu items.
	 *
	 * @var array<int, WP_Post>
	 */
	private $menu_items = array();

	/**
	 * Constructor.
	 *
	 * @param array<int, WP_Post> $menu_items Menu items of the rendered menu.
	 */
	public function __construct( $menu_items = array() ) {
		$this->menu_items = is_array( $menu_items ) ? $menu_items : array();
	}

	/**
	 * Output the end of an element.
	 *
	 * @param string   $output Passed by reference.
	 * @param WP_Post  $item Menu item data object.
	 * @param int      $depth Depth.
	 * @param stdClass $args Args.
	 * @return void
	 */
	public function end_el( &$output, $item, $depth = 0, $args = null ) {
		// Only inject on top-level items (MVP).
		if ( 0 === (int) $depth && isset( $item->ID ) ) {
			$item_id = absint( $item->ID );

			if ( $item_id > 0 && 1 === HM_MM_Storage::get_enabled( $item_id ) ) {
				$schema = HM_MM_Storage::get_schema( $item_id );
				$output .= HM_MM_Render::render_panel( $item_id, $schema, $this->menu_items );
			}
		}

		parent::end_el( $output, $item, $depth, $args );
	}
}
