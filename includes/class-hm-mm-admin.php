<?php
// File: includes/class-hm-mm-admin.php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin functionality.
 *
 * @package HM_Mega_Menu
 */
final class HM_MM_Admin {

	/**
	 * Plugin name.
	 *
	 * @var string
	 */
	private $plugin_name;

	/**
	 * Plugin version.
	 *
	 * @var string
	 */
	private $version;

	/**
	 * Builder page hook suffix.
	 *
	 * @var string|null
	 */
	private $builder_hook = null;

	/**
	 * Constructor.
	 *
	 * @param string $plugin_name Plugin name.
	 * @param string $version Plugin version.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = (string) $plugin_name;
		$this->version     = (string) $version;

		require_once HM_MM_PLUGIN_DIR . 'includes/class-hm-mm-admin-render.php';
	}

	/**
	 * Register admin menu.
	 *
	 * @return void
	 */
	public function register_menu() {
		// Use edit_theme_options because menu management uses it.
		$this->builder_hook = add_menu_page(
			__( 'HM Mega Menu', 'hm-mega-menu' ),
			__( 'HM Mega Menu', 'hm-mega-menu' ),
			'edit_theme_options',
			'hm-mega-menu',
			array( $this, 'render_builder_page' ),
			'dashicons-screenoptions',
			58
		);

		add_submenu_page(
			'hm-mega-menu',
			__( 'Builder', 'hm-mega-menu' ),
			__( 'Builder', 'hm-mega-menu' ),
			'edit_theme_options',
			'hm-mega-menu',
			array( $this, 'render_builder_page' )
		);
	}

	/**
	 * Render Builder page.
	 *
	 * @return void
	 */
	public function render_builder_page() {
		if ( ! current_user_can( 'edit_theme_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'hm-mega-menu' ) );
		}

		HM_MM_Admin_Render::builder_page();
	}

	/**
	 * Enqueue admin assets (conditional).
	 *
	 * @param string $hook_suffix Current admin page hook.
	 * @return void
	 */
	public function enqueue_assets( $hook_suffix ) {
		// Only load on our builder screen.
		if ( empty( $this->builder_hook ) || $hook_suffix !== $this->builder_hook ) {
			return;
		}

		wp_enqueue_style(
			$this->plugin_name . '-admin',
			HM_MM_PLUGIN_URL . 'assets/admin/admin.css',
			array(),
			$this->version
		);

		wp_enqueue_script(
			$this->plugin_name . '-admin',
			HM_MM_PLUGIN_URL . 'assets/admin/admin.js',
			array( 'jquery', 'jquery-ui-sortable' ),
			$this->version,
			true
		);

		wp_localize_script(
			$this->plugin_name . '-admin',
			'HM_MM_BUILDER',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'hm_mm_builder_nonce' ),
				'i18n'     => array(
					'saving'         => __( 'Saving...', 'hm-mega-menu' ),
					'saved'          => __( 'Saved.', 'hm-mega-menu' ),
					'save_failed'    => __( 'Save failed.', 'hm-mega-menu' ),
					'load_failed'    => __( 'Load failed.', 'hm-mega-menu' ),
					'choose_menu'    => __( 'Choose a menu first.', 'hm-mega-menu' ),
					'confirm_remove' => __( 'Remove this row?', 'hm-mega-menu' ),
				),
			)
		);
	}

	/**
	 * AJAX: Load builder data for a target menu item.
	 *
	 * @return void
	 */
	public function ajax_load() {
		if ( ! current_user_can( 'edit_theme_options' ) ) {
			wp_send_json_error( array( 'message' => 'forbidden' ), 403 );
		}

		check_ajax_referer( 'hm_mm_builder_nonce', 'nonce' );

		$target_item_id = isset( $_POST['target_item_id'] ) ? absint( $_POST['target_item_id'] ) : 0;
		if ( $target_item_id <= 0 ) {
			wp_send_json_error( array( 'message' => 'invalid_target' ), 400 );
		}

		$enabled = HM_MM_Storage::get_enabled( $target_item_id );
		$schema  = HM_MM_Storage::get_schema( $target_item_id );

		wp_send_json_success(
			array(
				'enabled' => $enabled,
				'schema'  => $schema,
			)
		);
	}

	/**
	 * AJAX: Save builder data for a target menu item.
	 *
	 * @return void
	 */
	public function ajax_save() {
		if ( ! current_user_can( 'edit_theme_options' ) ) {
			wp_send_json_error( array( 'message' => 'forbidden' ), 403 );
		}

		check_ajax_referer( 'hm_mm_builder_nonce', 'nonce' );

		$target_item_id = isset( $_POST['target_item_id'] ) ? absint( $_POST['target_item_id'] ) : 0;
		if ( $target_item_id <= 0 ) {
			wp_send_json_error( array( 'message' => 'invalid_target' ), 400 );
		}

		$enabled = isset( $_POST['enabled'] ) ? HM_MM_Utils::bool01( $_POST['enabled'] ) : 0;

		$schema_raw = array();
		if ( isset( $_POST['schema'] ) ) {
			// Expect JSON string from JS.
			$schema_json = wp_unslash( (string) $_POST['schema'] );
			$schema_raw  = json_decode( $schema_json, true );
			if ( ! is_array( $schema_raw ) ) {
				$schema_raw = array();
			}
		}

		$ok = HM_MM_Storage::save( $target_item_id, $enabled, $schema_raw );
		if ( ! $ok ) {
			wp_send_json_error( array( 'message' => 'save_failed' ), 500 );
		}

		wp_send_json_success(
			array(
				'enabled' => HM_MM_Storage::get_enabled( $target_item_id ),
				'schema'  => HM_MM_Storage::get_schema( $target_item_id ),
			)
		);
	}

	/**
	 * AJAX: Get menu items for a selected menu.
	 *
	 * @return void
	 */
	public function ajax_get_menu_items() {
		if ( ! current_user_can( 'edit_theme_options' ) ) {
			wp_send_json_error( array( 'message' => 'forbidden' ), 403 );
		}

		check_ajax_referer( 'hm_mm_builder_nonce', 'nonce' );

		$menu_id = isset( $_POST['menu_id'] ) ? absint( $_POST['menu_id'] ) : 0;
		if ( $menu_id <= 0 ) {
			wp_send_json_error( array( 'message' => 'invalid_menu' ), 400 );
		}

		$items = wp_get_nav_menu_items( $menu_id );
		if ( ! is_array( $items ) ) {
			$items = array();
		}

		$by_parent = array();
		foreach ( $items as $item ) {
			$parent_id = absint( $item->menu_item_parent );
			if ( ! isset( $by_parent[ $parent_id ] ) ) {
				$by_parent[ $parent_id ] = array();
			}
			$by_parent[ $parent_id ][] = $item;
		}

		$out  = array();
		$walk = function( $parent_id, $depth ) use ( &$walk, &$by_parent, &$out ) {
			if ( $depth > 10 ) {
				return;
			}
			if ( empty( $by_parent[ $parent_id ] ) ) {
				return;
			}
			foreach ( $by_parent[ $parent_id ] as $child ) {
				$out[] = array(
					'id'    => absint( $child->ID ),
					'title' => (string) $child->title,
					'depth' => (int) $depth,
				);
				$walk( absint( $child->ID ), $depth + 1 );
			}
		};

		$walk( 0, 0 );

		wp_send_json_success(
			array(
				'items' => $out,
			)
		);
	}
}
