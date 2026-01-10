<?php
// File: includes/class-hm-mm-storage.php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Storage helper for menu item meta.
 *
 * @package HM_Mega_Menu
 */
final class HM_MM_Storage {

	/**
	 * Meta keys.
	 */
	const META_ENABLED = '_hm_mm_enabled';
	const META_SCHEMA  = '_hm_mm_schema';

	/**
	 * Get enabled flag for menu item.
	 *
	 * @param int $menu_item_id Menu item post ID.
	 * @return int 0|1
	 */
	public static function get_enabled( $menu_item_id ) {
		$menu_item_id = absint( $menu_item_id );
		if ( $menu_item_id <= 0 ) {
			return 0;
		}

		$val = get_post_meta( $menu_item_id, self::META_ENABLED, true );
		return HM_MM_Utils::bool01( $val );
	}

	/**
	 * Get schema for menu item (sanitized).
	 *
	 * @param int $menu_item_id Menu item post ID.
	 * @return array<string, mixed>
	 */
	public static function get_schema( $menu_item_id ) {
		$menu_item_id = absint( $menu_item_id );
		if ( $menu_item_id <= 0 ) {
			return HM_MM_Schema::defaults();
		}

		$json = get_post_meta( $menu_item_id, self::META_SCHEMA, true );
		return HM_MM_Schema::from_json( is_string( $json ) ? $json : '' );
	}

	/**
	 * Save enabled + schema for a menu item.
	 *
	 * @param int                 $menu_item_id Menu item post ID.
	 * @param int|bool|string     $enabled 0/1.
	 * @param array<string,mixed> $schema Schema array.
	 * @return bool
	 */
	public static function save( $menu_item_id, $enabled, $schema ) {
		$menu_item_id = absint( $menu_item_id );
		if ( $menu_item_id <= 0 ) {
			return false;
		}

		// Ensure this is actually a nav menu item post.
		$post = get_post( $menu_item_id );
		if ( ! $post || 'nav_menu_item' !== $post->post_type ) {
			return false;
		}

		$enabled = HM_MM_Utils::bool01( $enabled );
		$schema  = HM_MM_Schema::sanitize( $schema );
		$json    = HM_MM_Schema::to_json( $schema );

		update_post_meta( $menu_item_id, self::META_ENABLED, $enabled );
		update_post_meta( $menu_item_id, self::META_SCHEMA, $json );

		return true;
	}

	/**
	 * Clear schema for menu item (optional utility).
	 *
	 * @param int $menu_item_id Menu item post ID.
	 * @return void
	 */
	public static function clear( $menu_item_id ) {
		$menu_item_id = absint( $menu_item_id );
		if ( $menu_item_id <= 0 ) {
			return;
		}
		delete_post_meta( $menu_item_id, self::META_ENABLED );
		delete_post_meta( $menu_item_id, self::META_SCHEMA );
	}
}
