<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class HM_MM_Frontend_Hooks {

	const META_ENABLED = '_hm_mm_enabled';

	public static function init() {
		add_filter( 'nav_menu_css_class', array( __CLASS__, 'add_menu_item_classes' ), 10, 4 );
		add_filter( 'walker_nav_menu_start_el', array( __CLASS__, 'inject_mega_panel' ), 10, 4 );
	}

	public static function add_menu_item_classes( $classes, $item, $args, $depth ) {
		$enabled = get_post_meta( $item->ID, self::META_ENABLED, true );

		if ( $enabled === '1' ) {
			$classes[] = 'hm-has-mega';
		}

		return $classes;
	}

	public static function inject_mega_panel( $item_output, $item, $depth, $args ) {
		$enabled = get_post_meta( $item->ID, self::META_ENABLED, true );
		if ( $enabled !== '1' ) {
			return $item_output;
		}

		// Demo content (Commit 5'te gerçek block sistemine geçeceğiz).
		$panel = '
			<div class="hm-mega-panel" aria-hidden="true">
				<div class="hm-mega-inner">
					<div class="hm-mega-col">
						<h4 class="hm-mega-col-title">Demo Column 1</h4>
						<ul class="hm-mega-links">
							<li><a href="#">Link 1</a></li>
							<li><a href="#">Link 2</a></li>
							<li><a href="#">Link 3</a></li>
						</ul>
					</div>

					<div class="hm-mega-col">
						<h4 class="hm-mega-col-title">Demo Column 2</h4>
						<ul class="hm-mega-links">
							<li><a href="#">Link 4</a></li>
							<li><a href="#">Link 5</a></li>
							<li><a href="#">Link 6</a></li>
						</ul>
					</div>

					<div class="hm-mega-col">
						<h4 class="hm-mega-col-title">Demo Column 3</h4>
						<ul class="hm-mega-links">
							<li><a href="#">Link 7</a></li>
							<li><a href="#">Link 8</a></li>
							<li><a href="#">Link 9</a></li>
						</ul>
					</div>

					<div class="hm-mega-col">
						<h4 class="hm-mega-col-title">Demo Column 4</h4>
						<ul class="hm-mega-links">
							<li><a href="#">Link 10</a></li>
							<li><a href="#">Link 11</a></li>
							<li><a href="#">Link 12</a></li>
						</ul>
					</div>
				</div>
			</div>
		';

		// Theme anchor output'unun sonuna panel ekle.
		return $item_output . $panel;
	}
}
