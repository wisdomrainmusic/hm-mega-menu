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
    $cols = get_post_meta($item_id, '_hm_mm_cols', true);
    $cols = $cols ? (int) $cols : 4;

    $parent = get_post_meta($item_id, '_hm_mm_parent_cat', true);
    $parent = $parent ? (int) $parent : 0;

    $depth = get_post_meta($item_id, '_hm_mm_depth', true);
    $depth = $depth ? (int) $depth : 2;

    $limit = get_post_meta($item_id, '_hm_mm_limit', true);
    $limit = $limit ? (int) $limit : 24;

    // Woo categories list
    $terms = array();
    if ( taxonomy_exists('product_cat') ) {
      $terms = get_terms(array(
        'taxonomy' => 'product_cat',
        'hide_empty' => false,
        'parent' => 0,
        'orderby' => 'name',
        'order' => 'ASC',
      ));
    }
    ?>

    <p class="field-hm-mm-cols description description-wide">
      <label for="hm-mm-cols-<?php echo esc_attr($item_id); ?>">
        <?php echo esc_html__('Columns (3–6)', 'hm-mega-menu'); ?><br/>
        <select id="hm-mm-cols-<?php echo esc_attr($item_id); ?>" name="menu-item-hm-mm-cols[<?php echo esc_attr($item_id); ?>]">
          <?php for ($i = 3; $i <= 6; $i++): ?>
            <option value="<?php echo esc_attr($i); ?>" <?php selected($cols, $i); ?>><?php echo esc_html($i); ?></option>
          <?php endfor; ?>
        </select>
      </label>
    </p>

    <p class="field-hm-mm-parent description description-wide">
      <label for="hm-mm-parent-<?php echo esc_attr($item_id); ?>">
        <?php echo esc_html__('Parent Woo Category (top level)', 'hm-mega-menu'); ?><br/>
        <select id="hm-mm-parent-<?php echo esc_attr($item_id); ?>" name="menu-item-hm-mm-parent[<?php echo esc_attr($item_id); ?>]">
          <option value="0"><?php echo esc_html__('-- Select --', 'hm-mega-menu'); ?></option>
          <?php foreach ($terms as $t): ?>
            <option value="<?php echo esc_attr($t->term_id); ?>" <?php selected($parent, (int) $t->term_id); ?>>
              <?php echo esc_html($t->name); ?>
            </option>
          <?php endforeach; ?>
        </select>
      </label>
      <?php if ($enabled === '1' && (int) $parent === 0): ?>
        <em style="display:block; margin-top:6px; color:#b32d2e;">
          <?php echo esc_html__('Select a parent category, otherwise mega panel shows an empty message.', 'hm-mega-menu'); ?>
        </em>
      <?php endif; ?>
    </p>

    <p class="field-hm-mm-depth description description-wide">
      <label for="hm-mm-depth-<?php echo esc_attr($item_id); ?>">
        <?php echo esc_html__('Depth (1–3)', 'hm-mega-menu'); ?><br/>
        <select id="hm-mm-depth-<?php echo esc_attr($item_id); ?>" name="menu-item-hm-mm-depth[<?php echo esc_attr($item_id); ?>]">
          <?php for ($i = 1; $i <= 3; $i++): ?>
            <option value="<?php echo esc_attr($i); ?>" <?php selected($depth, $i); ?>><?php echo esc_html($i); ?></option>
          <?php endfor; ?>
        </select>
      </label>
    </p>

    <p class="field-hm-mm-limit description description-wide">
      <label for="hm-mm-limit-<?php echo esc_attr($item_id); ?>">
        <?php echo esc_html__('Max items (recommended 24–60)', 'hm-mega-menu'); ?><br/>
        <input type="number"
          id="hm-mm-limit-<?php echo esc_attr($item_id); ?>"
          name="menu-item-hm-mm-limit[<?php echo esc_attr($item_id); ?>]"
          value="<?php echo esc_attr($limit); ?>"
          min="1"
          max="200"
          step="1"
        />
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

    // Columns
    if ( isset($_POST['menu-item-hm-mm-cols'][$menu_item_id]) ) {
      $cols = (int) $_POST['menu-item-hm-mm-cols'][$menu_item_id];
      $cols = max(3, min(6, $cols));
      update_post_meta($menu_item_id, '_hm_mm_cols', (string) $cols);
    }

    // Parent category (always update, even if 0)
    $parent = 0;
    if ( isset($_POST['menu-item-hm-mm-parent']) && is_array($_POST['menu-item-hm-mm-parent']) ) {
      if ( isset($_POST['menu-item-hm-mm-parent'][$menu_item_id]) ) {
        $parent = (int) $_POST['menu-item-hm-mm-parent'][$menu_item_id];
      }
    }
    update_post_meta($menu_item_id, '_hm_mm_parent_cat', (string) $parent);

    // Depth
    if ( isset($_POST['menu-item-hm-mm-depth'][$menu_item_id]) ) {
      $depth = (int) $_POST['menu-item-hm-mm-depth'][$menu_item_id];
      $depth = max(1, min(3, $depth));
      update_post_meta($menu_item_id, '_hm_mm_depth', (string) $depth);
    }

    // Limit
    if ( isset($_POST['menu-item-hm-mm-limit'][$menu_item_id]) ) {
      $limit = (int) $_POST['menu-item-hm-mm-limit'][$menu_item_id];
      $limit = max(1, min(200, $limit));
      update_post_meta($menu_item_id, '_hm_mm_limit', (string) $limit);
    }
  }
}
