<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once HM_MM_PLUGIN_DIR . 'includes/class-hm-mega-menu-renderer.php';

class HM_Mega_Menu_Public {

	const BREAKPOINT_DESKTOP = 1024;

	/**
	 * Enqueue public assets only if at least one menu has enabled mega config.
	 */
	public function maybe_enqueue_assets() {
		if ( is_admin() ) {
			return;
		}

		$all = HM_Mega_Menu_Storage::get_all();
		if ( empty( $all ) || ! is_array( $all ) ) {
			return;
		}

		$enabled_map = array(); // menu_id => target_item_id
		foreach ( $all as $menu_id => $cfg ) {
			if ( ! is_array( $cfg ) ) {
				continue;
			}
			$cfg = HM_Mega_Menu_Storage::normalize_menu_config( $cfg );
			if ( ! empty( $cfg['enabled'] ) && ! empty( $cfg['target_item_id'] ) ) {
				$enabled_map[ (int) $menu_id ] = (int) $cfg['target_item_id'];
			}
		}

		if ( empty( $enabled_map ) ) {
			return;
		}

		wp_enqueue_style( 'hm-mm-public' );
		wp_enqueue_script( 'hm-mm-public' );

		wp_localize_script(
			'hm-mm-public',
			'HM_MM_PUBLIC',
			array(
				'breakpoint' => self::BREAKPOINT_DESKTOP,
				'enabledMap' => $enabled_map,
			)
		);
	}

	/**
	 * Add "hm-mm-has-mega" class to the target <li> element.
	 *
	 * @param array    $classes
	 * @param WP_Post  $item
	 * @param stdClass $args
	 * @param int      $depth
	 * @return array
	 */
	public function filter_nav_menu_css_class( $classes, $item, $args, $depth ) {
		$menu_id = $this->resolve_menu_id_from_args( $args );
		if ( ! $menu_id ) {
			return $classes;
		}

		$cfg = HM_Mega_Menu_Storage::get_menu_config( $menu_id );
		if ( empty( $cfg['enabled'] ) || empty( $cfg['target_item_id'] ) ) {
			return $classes;
		}

		if ( (int) $item->ID === (int) $cfg['target_item_id'] ) {
			$classes[] = 'hm-mm-has-mega';
		}

		return $classes;
	}

	/**
	 * Inject panel markup into the target menu item's output (after the <a> element).
	 *
	 * @param string   $item_output
	 * @param WP_Post  $item
	 * @param int      $depth
	 * @param stdClass $args
	 * @return string
	 */
	public function filter_start_el_inject_panel( $item_output, $item, $depth, $args ) {
		$menu_id = $this->resolve_menu_id_from_args( $args );
		if ( ! $menu_id ) {
			return $item_output;
		}

		$cfg = HM_Mega_Menu_Storage::get_menu_config( $menu_id );
		if ( empty( $cfg['enabled'] ) || empty( $cfg['target_item_id'] ) ) {
			return $item_output;
		}

		// Only inject for the exact target item (and only once per item).
		if ( (int) $item->ID !== (int) $cfg['target_item_id'] ) {
			return $item_output;
		}

		$panel = $this->render_panel_shell( $menu_id, (int) $item->ID, $cfg );

		// Append after anchor output (Walker already builds <a>...).
		return $item_output . $panel;
	}

	/**
	 * Render panel shell with sections content.
	 *
	 * @param int   $menu_id
	 * @param int   $target_item_id
	 * @param array $cfg
	 * @return string
	 */
	private function render_panel_shell( $menu_id, $target_item_id, $cfg ) {
		$menu_id        = absint( $menu_id );
		$target_item_id = absint( $target_item_id );

		$renderer      = new HM_Mega_Menu_Renderer();
		$sections_html = $renderer->render_sections_html( $menu_id, $cfg );

		ob_start();
		?>
		<div class="hm-mm-panel" data-hm-mm-menu="<?php echo esc_attr( $menu_id ); ?>" data-hm-mm-target="<?php echo esc_attr( $target_item_id ); ?>" aria-hidden="true">
			<div class="hm-mm-inner">
				<div class="hm-mm-sections">
					<?php echo $sections_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</div>
			</div>
		</div>
		<?php
		return (string) ob_get_clean();
	}

	/**
	 * Resolve menu_id from $args without touching menu objects.
	 *
	 * @param stdClass $args
	 * @return int
	 */
	private function resolve_menu_id_from_args( $args ) {
		// Case 1: args->menu is term object or id.
		if ( isset( $args->menu ) && $args->menu ) {
			if ( is_object( $args->menu ) && isset( $args->menu->term_id ) ) {
				return absint( $args->menu->term_id );
			}
			if ( is_numeric( $args->menu ) ) {
				return absint( $args->menu );
			}
		}

		// Case 2: theme_location -> menu id.
		if ( isset( $args->theme_location ) && $args->theme_location ) {
			$locations = get_nav_menu_locations();
			if ( isset( $locations[ $args->theme_location ] ) ) {
				return absint( $locations[ $args->theme_location ] );
			}
		}

		return 0;
	}
}
