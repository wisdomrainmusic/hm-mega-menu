<?php
if ( ! defined('ABSPATH') ) {
  exit;
}

final class HM_MM_Admin_Menu_Fields {

  const META_ENABLED = '_hm_mm_enabled';

  public static function init() {
    // Render fields inside each nav menu item (Appearance > Menus).
    add_action('wp_nav_menu_item_custom_fields', [__CLASS__, 'render_fields'], 10, 4);

    // Save on menu update.
    add_action('wp_update_nav_menu_item', [__CLASS__, 'save_fields'], 10, 3);
    add_action('save_post_nav_menu_item', [__CLASS__, 'save_fields_post'], 10, 3);
  }

  public static function render_fields($item_id, $item, $depth, $args) {
    $enabled = get_post_meta($item_id, self::META_ENABLED, true);
    $enabled = ($enabled === '1') ? '1' : '0';

    ?>
    <p class="field-hm-mm-enabled description description-wide">
      <label for="hm-mm-enabled-<?php echo esc_attr($item_id); ?>">
        <input
          type="checkbox"
          id="hm-mm-enabled-<?php echo esc_attr($item_id); ?>"
          name="menu-item-hm-mm-enabled[<?php echo esc_attr($item_id); ?>]"
          value="1"
          <?php checked($enabled, '1'); ?>
        />
        <?php echo esc_html__('Enable Mega Menu (HM)', 'hm-mega-menu'); ?>
      </label>
    </p>
    <?php
  }

  public static function save_fields($menu_id, $menu_item_db_id, $args) {
    self::persist_enabled_meta($menu_item_db_id);
  }

  public static function save_fields_post($post_id, $post, $update) {
    // Only for nav menu items
    if ( empty($post) || $post->post_type !== 'nav_menu_item' ) {
      return;
    }

    self::persist_enabled_meta($post_id);
  }

  private static function persist_enabled_meta($menu_item_id) {
    if ( ! current_user_can('edit_theme_options') ) {
      return;
    }

    $enabled = '0';

    // NEW (WordPress native-ish naming)
    if ( isset($_POST['menu-item-hm-mm-enabled']) && is_array($_POST['menu-item-hm-mm-enabled']) ) {
      if ( isset($_POST['menu-item-hm-mm-enabled'][$menu_item_id]) && $_POST['menu-item-hm-mm-enabled'][$menu_item_id] === '1' ) {
        $enabled = '1';
      }
    }

    // BACKWARD COMPAT (old field name)
    if ( $enabled === '0' && isset($_POST['hm_mm_enabled']) && is_array($_POST['hm_mm_enabled']) ) {
      if ( isset($_POST['hm_mm_enabled'][$menu_item_id]) && $_POST['hm_mm_enabled'][$menu_item_id] === '1' ) {
        $enabled = '1';
      }
    }

    update_post_meta($menu_item_id, self::META_ENABLED, $enabled);
  }
}
