<?php
// File: includes/class-hm-mm-menu.php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Menu resolution + indexing helpers.
 *
 * @package HM_Mega_Menu
 */
final class HM_MM_Menu {

	/**
	 * Resolve menu term ID from wp_nav_menu args.
	 *
	 * @param array $args wp_nav_menu args.
	 * @return int Menu term ID or 0.
	 */
	public static function resolve_menu_term_id( $args ) {
		// 1) Explicit 'menu' arg (id/slug/name/term object).
		if ( isset( $args['menu'] ) && ! empty( $args['menu'] ) ) {
			$menu_obj = wp_get_nav_menu_object( $args['menu'] );
			if ( $menu_obj && isset( $menu_obj->term_id ) ) {
				return absint( $menu_obj->term_id );
			}
		}

		// 2) theme_location.
		if ( isset( $args['theme_location'] ) && ! empty( $args['theme_location'] ) ) {
			$locations = get_nav_menu_locations();
			$loc       = (string) $args['theme_location'];
			if ( isset( $locations[ $loc ] ) ) {
				return absint( $locations[ $loc ] );
			}
		}

		return 0;
	}

	/**
	 * Get menu items for a menu term ID.
	 *
	 * @param int $menu_term_id Menu term ID.
	 * @return array<int, WP_Post>
	 */
	public static function get_items( $menu_term_id ) {
		$menu_term_id = absint( $menu_term_id );
		if ( $menu_term_id <= 0 ) {
			return array();
		}

		$items = wp_get_nav_menu_items( $menu_term_id );
		return is_array( $items ) ? $items : array();
	}

	/**
	 * Build parent => children index.
	 *
	 * @param array<int, WP_Post> $items Menu items.
	 * @return array<int, array<int, WP_Post>>
	 */
	public static function index_by_parent( $items ) {
		$by_parent = array();

		foreach ( (array) $items as $it ) {
			if ( ! is_object( $it ) || ! isset( $it->ID ) ) {
				continue;
			}
			$pid = isset( $it->menu_item_parent ) ? absint( $it->menu_item_parent ) : 0;

			if ( ! isset( $by_parent[ $pid ] ) ) {
				$by_parent[ $pid ] = array();
			}
			$by_parent[ $pid ][] = $it;
		}

		return $by_parent;
	}

	/**
	 * Get direct children for a menu item ID.
	 *
	 * @param array<int, array<int, WP_Post>> $by_parent Index.
	 * @param int                             $parent_id Parent menu item ID.
	 * @return array<int, WP_Post>
	 */
	public static function children_of( $by_parent, $parent_id ) {
		$parent_id = absint( $parent_id );
		if ( $parent_id < 0 ) {
			$parent_id = 0;
		}
		return isset( $by_parent[ $parent_id ] ) ? (array) $by_parent[ $parent_id ] : array();
	}

	/**
	 * Safe link fields for an item.
	 *
	 * @param WP_Post $item Menu item.
	 * @return array{title:string,url:string}
	 */
	public static function link_of( $item ) {
		$title = '';
		$url   = '';

		if ( is_object( $item ) ) {
			$title = isset( $item->title ) ? (string) $item->title : '';
			$url   = isset( $item->url ) ? (string) $item->url : '';
		}

		return array(
			'title' => $title,
			'url'   => $url,
		);
	}
}
