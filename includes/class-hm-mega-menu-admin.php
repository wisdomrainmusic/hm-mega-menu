<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class HM_Mega_Menu_Admin {

	const MENU_SLUG    = 'hm-mm-builder';
	const NONCE_ACTION = 'hm_mm_save_builder_v2';

	/**
	 * Register admin menu.
	 */
	public function register_menu() {
		add_menu_page(
			__( 'HM Mega Menu', 'hm-mega-menu' ),
			__( 'HM Mega Menu', 'hm-mega-menu' ),
			'edit_theme_options',
			self::MENU_SLUG,
			array( $this, 'render_page' ),
			'dashicons-screenoptions',
			59
		);
	}

	/**
	 * Conditionally enqueue admin assets only on our page.
	 *
	 * @param string $hook_suffix
	 */
	public function enqueue_assets( $hook_suffix ) {
		if ( ! function_exists( 'get_current_screen' ) ) {
			return;
		}

		$screen = get_current_screen();
		if ( empty( $screen->id ) ) {
			return;
		}

		// Our menu page screen id typically: "toplevel_page_hm-mm-builder".
		if ( false === strpos( $screen->id, 'hm-mm-builder' ) ) {
			return;
		}

		wp_enqueue_style( 'hm-mm-admin' );
		wp_enqueue_script( 'hm-mm-admin' );

		wp_localize_script(
			'hm-mm-admin',
			'HM_MM_ADMIN',
			array(
				'maxSections' => 50,
				'strings'     => array(
					'addSection' => __( 'Bölüm Ekle', 'hm-mega-menu' ),
					'confirmDel' => __( 'Bu bölümü silmek istiyor musun?', 'hm-mega-menu' ),
				),
			)
		);
	}

	/**
	 * Render builder page.
	 */
	public function render_page() {
		if ( ! current_user_can( 'edit_theme_options' ) ) {
			wp_die( esc_html__( 'Bu sayfaya erişim yetkin yok.', 'hm-mega-menu' ) );
		}

		$menus = wp_get_nav_menus();

		$selected_menu_id = isset( $_GET['menu_id'] ) ? absint( $_GET['menu_id'] ) : 0;
		if ( ! $selected_menu_id && ! empty( $menus ) ) {
			$selected_menu_id = (int) $menus[0]->term_id;
		}

		$config = HM_Mega_Menu_Storage::get_menu_config( $selected_menu_id );

		$menu_items = array();
		if ( $selected_menu_id ) {
			$items = wp_get_nav_menu_items( $selected_menu_id );
			if ( is_array( $items ) ) {
				$menu_items = $items;
			}
		}

		$notice = $this->get_notice();

		?>
		<div class="wrap hm-mm-wrap">
			<h1><?php echo esc_html__( 'HM Mega Menu → Builder', 'hm-mega-menu' ); ?></h1>
			<div id="hm-mm-js-status" data-ok="0">JS status: waiting…</div>

			<?php if ( $notice ) : ?>
				<div class="notice notice-<?php echo esc_attr( $notice['type'] ); ?> is-dismissible">
					<p><?php echo esc_html( $notice['message'] ); ?></p>
				</div>
			<?php endif; ?>

			<form method="get" action="">
				<input type="hidden" name="page" value="<?php echo esc_attr( self::MENU_SLUG ); ?>" />
				<div class="hm-mm-topbar">
					<div class="hm-mm-field">
						<label for="hm-mm-menu-select"><?php echo esc_html__( 'Menü Seç', 'hm-mega-menu' ); ?></label>
						<select id="hm-mm-menu-select" name="menu_id">
							<?php foreach ( $menus as $m ) : ?>
								<option value="<?php echo esc_attr( $m->term_id ); ?>" <?php selected( $selected_menu_id, (int) $m->term_id ); ?>>
									<?php echo esc_html( $m->name ); ?>
								</option>
							<?php endforeach; ?>
						</select>
					</div>

					<div class="hm-mm-field hm-mm-field--actions">
						<button type="submit" class="button"><?php echo esc_html__( 'Yükle', 'hm-mega-menu' ); ?></button>
					</div>
				</div>
			</form>

			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="hm-mm-form">
				<input type="hidden" name="action" value="hm_mm_save_builder_v2" />
				<?php wp_nonce_field( self::NONCE_ACTION, 'hm_mm_nonce' ); ?>
				<input type="hidden" name="menu_id" value="<?php echo esc_attr( $selected_menu_id ); ?>" />

				<div class="hm-mm-card">
					<h2><?php echo esc_html__( 'Temel Ayarlar', 'hm-mega-menu' ); ?></h2>

					<div class="hm-mm-grid">
						<div class="hm-mm-field">
							<label for="hm-mm-target-item"><?php echo esc_html__( 'Hedef Menü Öğesi', 'hm-mega-menu' ); ?></label>
							<select id="hm-mm-target-item" name="target_item_id">
								<option value="0"><?php echo esc_html__( 'Seçiniz...', 'hm-mega-menu' ); ?></option>
								<?php foreach ( $menu_items as $it ) : ?>
									<option value="<?php echo esc_attr( $it->ID ); ?>" <?php selected( (int) $config['target_item_id'], (int) $it->ID ); ?>>
										<?php echo esc_html( $it->title ); ?>
									</option>
								<?php endforeach; ?>
							</select>
						</div>

						<div class="hm-mm-field hm-mm-field--checkbox">
							<label>
								<input type="checkbox" name="enabled" value="1" <?php checked( (int) $config['enabled'], 1 ); ?> />
								<?php echo esc_html__( 'Aktif', 'hm-mega-menu' ); ?>
							</label>
							<p class="description">
								<?php echo esc_html__( 'Aktif olduğunda hedef öğede mega panel enjekte edilir.', 'hm-mega-menu' ); ?>
							</p>
						</div>
					</div>
				</div>

				<div class="hm-mm-card">
					<div class="hm-mm-card-head">
						<h2><?php echo esc_html__( 'Bölümler', 'hm-mega-menu' ); ?></h2>
						<button type="button" class="button button-secondary" id="hm-mm-add-section">
							<?php echo esc_html__( 'Bölüm Ekle', 'hm-mega-menu' ); ?>
						</button>
					</div>

					<p class="description hm-mm-help">
						<?php echo esc_html__( 'Kolon sayısı: Bu bölümün içindeki bloklar kaç sütuna bölünsün.', 'hm-mega-menu' ); ?><br/>
						<?php echo esc_html__( 'Derinlik: Kaç seviye alt menü gösterilsin.', 'hm-mega-menu' ); ?>
					</p>

					<div class="hm-mm-sections" id="hm-mm-sections">
						<?php
						$sections = isset( $config['sections'] ) && is_array( $config['sections'] ) ? $config['sections'] : array();
						if ( empty( $sections ) ) {
							$sections = array();
						}

						foreach ( $sections as $idx => $section ) {
							$this->render_section_row( $idx, $section, $menu_items );
						}
						?>
					</div>

					<!-- Template for JS cloning -->
					<script type="text/template" id="hm-mm-section-template">
						<?php
						$this->render_section_row(
							'__INDEX__',
							array(
								'id'          => 'sec_' . wp_generate_uuid4(),
								'title'       => '',
								'source_type' => 'menu_node',
								'source_id'   => 0,
								'columns'     => 3,
								'depth'       => 2,
								'show_title'  => 1,
							),
							$menu_items,
							true
						);
						?>
					</script>
				</div>

				<div class="hm-mm-actions">
					<button type="submit" class="button button-primary button-large">
						<?php echo esc_html__( 'Kaydet', 'hm-mega-menu' ); ?>
					</button>
				</div>
			</form>
		</div>
		<?php
	}

	/**
	 * Render one section row.
	 *
	 * @param int|string $index
	 * @param array      $section
	 * @param array      $menu_items
	 * @param bool       $is_template
	 */
	private function render_section_row( $index, $section, $menu_items, $is_template = false ) {
		$section = HM_Mega_Menu_Storage::normalize_section( $section );
		if ( ! $section ) {
			return;
		}

		$index_attr = esc_attr( $index );
		$row_id     = $is_template ? '__INDEX__' : $index_attr;

		?>
		<div class="hm-mm-section" data-index="<?php echo $index_attr; ?>">
			<div class="hm-mm-section__handle" title="<?php echo esc_attr__( 'Sürükle-bırak ile sırala', 'hm-mega-menu' ); ?>">≡</div>

			<div class="hm-mm-section__body">
				<input type="hidden" name="sections[<?php echo $row_id; ?>][id]" value="<?php echo esc_attr( $section['id'] ); ?>" />

				<div class="hm-mm-row">
					<div class="hm-mm-field">
						<label><?php echo esc_html__( 'Bölüm Başlığı', 'hm-mega-menu' ); ?></label>
						<input type="text" name="sections[<?php echo $row_id; ?>][title]" value="<?php echo esc_attr( $section['title'] ); ?>" placeholder="<?php echo esc_attr__( 'Örn: Yeni Gelenler', 'hm-mega-menu' ); ?>" />
					</div>

					<div class="hm-mm-field hm-mm-field--checkbox">
						<label>
							<input type="checkbox" name="sections[<?php echo $row_id; ?>][show_title]" value="1" <?php checked( (int) $section['show_title'], 1 ); ?> />
							<?php echo esc_html__( 'Başlığı Göster', 'hm-mega-menu' ); ?>
						</label>
					</div>
				</div>

				<div class="hm-mm-row">
					<div class="hm-mm-field">
						<label><?php echo esc_html__( 'Source', 'hm-mega-menu' ); ?></label>
						<select name="sections[<?php echo $row_id; ?>][source_id]">
							<option value="0"><?php echo esc_html__( 'Menu item seçiniz...', 'hm-mega-menu' ); ?></option>
							<?php foreach ( $menu_items as $it ) : ?>
								<option value="<?php echo esc_attr( $it->ID ); ?>" <?php selected( (int) $section['source_id'], (int) $it->ID ); ?>>
									<?php echo esc_html( $it->title ); ?>
								</option>
							<?php endforeach; ?>
						</select>
						<input type="hidden" name="sections[<?php echo $row_id; ?>][source_type]" value="menu_node" />
					</div>

					<div class="hm-mm-field">
						<label><?php echo esc_html__( 'Kolon Sayısı', 'hm-mega-menu' ); ?></label>
						<select name="sections[<?php echo $row_id; ?>][columns]">
							<?php for ( $i = 1; $i <= 6; $i++ ) : ?>
								<option value="<?php echo esc_attr( $i ); ?>" <?php selected( (int) $section['columns'], $i ); ?>><?php echo esc_html( $i ); ?></option>
							<?php endfor; ?>
						</select>
					</div>

					<div class="hm-mm-field">
						<label><?php echo esc_html__( 'Derinlik', 'hm-mega-menu' ); ?></label>
						<select name="sections[<?php echo $row_id; ?>][depth]">
							<?php for ( $d = 1; $d <= 3; $d++ ) : ?>
								<option value="<?php echo esc_attr( $d ); ?>" <?php selected( (int) $section['depth'], $d ); ?>><?php echo esc_html( $d ); ?></option>
							<?php endfor; ?>
						</select>
					</div>
				</div>
			</div>

			<div class="hm-mm-section__actions">
				<button type="button" class="button-link-delete hm-mm-remove-section">
					<?php echo esc_html__( 'Sil', 'hm-mega-menu' ); ?>
				</button>
			</div>
		</div>
		<?php
	}

	/**
	 * Handle save request.
	 */
	public function handle_save() {
		if ( ! current_user_can( 'edit_theme_options' ) ) {
			wp_die( esc_html__( 'Yetkisiz işlem.', 'hm-mega-menu' ) );
		}

		$nonce = isset( $_POST['hm_mm_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['hm_mm_nonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, self::NONCE_ACTION ) ) {
			wp_die( esc_html__( 'Güvenlik doğrulaması başarısız (nonce).', 'hm-mega-menu' ) );
		}

		$menu_id        = isset( $_POST['menu_id'] ) ? absint( $_POST['menu_id'] ) : 0;
		$enabled        = isset( $_POST['enabled'] ) ? 1 : 0;
		$target_item_id = isset( $_POST['target_item_id'] ) ? absint( $_POST['target_item_id'] ) : 0;

		$sections_raw = array();
		if ( isset( $_POST['sections'] ) && is_array( $_POST['sections'] ) ) {
			$sections_raw = wp_unslash( $_POST['sections'] );
		}

		$sections = array();
		foreach ( $sections_raw as $sec ) {
			$norm = HM_Mega_Menu_Storage::normalize_section( is_array( $sec ) ? $sec : array() );
			if ( $norm ) {
				$sections[] = $norm;
			}
		}

		$config = array(
			'enabled'        => $enabled,
			'target_item_id' => $target_item_id,
			'sections'       => $sections,
		);

		HM_Mega_Menu_Storage::save_menu_config( $menu_id, $config );

		$redirect = add_query_arg(
			array(
				'page'    => self::MENU_SLUG,
				'menu_id' => $menu_id,
				'hm_mm'   => 'saved',
			),
			admin_url( 'admin.php' )
		);

		wp_safe_redirect( $redirect );
		exit;
	}

	/**
	 * Fetch admin notice data.
	 *
	 * @return array|null
	 */
	private function get_notice() {
		$flag = isset( $_GET['hm_mm'] ) ? sanitize_key( (string) $_GET['hm_mm'] ) : '';
		if ( 'saved' === $flag ) {
			return array(
				'type'    => 'success',
				'message' => __( 'Ayarlar kaydedildi.', 'hm-mega-menu' ),
			);
		}

		return null;
	}
}
