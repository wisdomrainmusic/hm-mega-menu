<?php
// File: includes/class-hm-mm-admin-render.php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin page render helpers.
 *
 * @package HM_Mega_Menu
 */
final class HM_MM_Admin_Render {

	/**
	 * Render Builder page HTML.
	 *
	 * @return void
	 */
	public static function builder_page() {
		$menus = wp_get_nav_menus();
		?>
		<div class="wrap hm-mm-wrap">
			<h1><?php echo esc_html__( 'HM Mega Menü Oluşturucu', 'hm-mega-menu' ); ?></h1>

			<div class="hm-mm-card">
				<div class="hm-mm-row">
					<label for="hm-mm-menu-select" class="hm-mm-label"><?php echo esc_html__( 'Menü', 'hm-mega-menu' ); ?></label>
					<select id="hm-mm-menu-select" class="hm-mm-select">
						<option value=""><?php echo esc_html__( 'Menü seç', 'hm-mega-menu' ); ?></option>
						<?php foreach ( $menus as $menu ) : ?>
							<option value="<?php echo esc_attr( (string) $menu->term_id ); ?>">
								<?php echo esc_html( $menu->name ); ?>
							</option>
						<?php endforeach; ?>
					</select>

					<label for="hm-mm-target-item" class="hm-mm-label"><?php echo esc_html__( 'Hedef Menü Öğesi', 'hm-mega-menu' ); ?></label>
					<select id="hm-mm-target-item" class="hm-mm-select" disabled>
						<option value=""><?php echo esc_html__( 'Önce menü seç', 'hm-mega-menu' ); ?></option>
					</select>

					<label class="hm-mm-toggle">
						<input type="checkbox" id="hm-mm-enabled" />
						<span><?php echo esc_html__( 'Aktif', 'hm-mega-menu' ); ?></span>
					</label>

					<button type="button" class="button button-primary" id="hm-mm-save" disabled>
						<?php echo esc_html__( 'Kaydet', 'hm-mega-menu' ); ?>
					</button>

					<span class="hm-mm-status" id="hm-mm-status" aria-live="polite"></span>
				</div>

				<hr />

				<div class="hm-mm-builder">
					<div class="hm-mm-builder-head">
						<h2><?php echo esc_html__( 'Satırlar', 'hm-mega-menu' ); ?></h2>
						<button type="button" class="button" id="hm-mm-add-row" disabled>
							<?php echo esc_html__( 'Satır Ekle', 'hm-mega-menu' ); ?>
						</button>
					</div>

					<table class="widefat fixed striped hm-mm-table" id="hm-mm-rows-table">
						<thead>
							<tr>
								<th class="hm-mm-col-drag"></th>
								<th><?php echo esc_html__( 'Satır Başlığı', 'hm-mega-menu' ); ?></th>
								<th><?php echo esc_html__( 'Kaynak Türü', 'hm-mega-menu' ); ?></th>
								<th><?php echo esc_html__( 'Kaynak (Menü Öğesi)', 'hm-mega-menu' ); ?></th>
								<th><?php echo esc_html__( 'Kolon Sayısı', 'hm-mega-menu' ); ?></th>
								<th><?php echo esc_html__( 'Derinlik', 'hm-mega-menu' ); ?></th>
								<th><?php echo esc_html__( 'Başlık', 'hm-mega-menu' ); ?></th>
								<th class="hm-mm-col-actions"></th>
							</tr>
						</thead>
						<tbody id="hm-mm-rows-body">
							<tr class="hm-mm-empty">
								<td colspan="8"><?php echo esc_html__( 'No rows yet. Add one to start.', 'hm-mega-menu' ); ?></td>
							</tr>
						</tbody>
					</table>

					<p class="description hm-mm-legend">
						<?php echo esc_html__( 'Kolon Sayısı: Bu satırın içeriği kaç sütuna bölünsün (1–6). Derinlik: Kaynak menü öğesinin alt seviyeleri (1–3). 1 = sadece başlıklar, 2 = alt menüler, 3 = alt-alt menüler.', 'hm-mega-menu' ); ?>
					</p>

					<p class="description hm-mm-note">
						<?php echo esc_html__( 'MVP: Menu Node mode only. Schema is stored per target menu item as post meta.', 'hm-mega-menu' ); ?>
					</p>
				</div>
			</div>
		</div>
		<?php
	}
}
