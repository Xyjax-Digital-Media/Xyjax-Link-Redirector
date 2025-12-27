<?php
/**
 * Plugin Name: Xyjax Link Redirector
 * Plugin URI: https://xyjax.com/go/linkredirects
 * Description: Opensource lightweight redirect manager with basic click tracking.
 * Version: 0.2.2
 * Author: Xyjax Digital Media
 * Author URI: https://xyjax.com
 * License: MIT
 * License URI: https://opensource.org/licenses/MIT
 */

if (!defined('ABSPATH')) exit;

class Xyjax_Link_Redirector {
  const CPT = 'xyjax_link';

  const META_DEST = '_xyjax_dest_url';
  const META_CODE = '_xyjax_redirect_code';
  const META_ENABLED = '_xyjax_enabled';
  const META_CLICKS = '_xyjax_clicks';

  const OPT_PREFIX = 'xyjax_link_prefix';

  public function __construct() {
    add_action('init', [$this, 'register_cpt']);
    add_action('init', [$this, 'add_rewrite_rules']);
    add_filter('query_vars', [$this, 'query_vars']);
    add_action('template_redirect', [$this, 'handle_redirect']);

    add_action('add_meta_boxes', [$this, 'meta_boxes']);
    add_action('save_post_' . self::CPT, [$this, 'save_meta'], 10, 2);

    add_action('admin_menu', [$this, 'settings_page']);
    add_action('admin_init', [$this, 'register_settings']);

    register_activation_hook(__FILE__, [$this, 'activate']);
    register_deactivation_hook(__FILE__, 'flush_rewrite_rules');
  }

  public function activate() {
    if (!get_option(self::OPT_PREFIX)) {
      update_option(self::OPT_PREFIX, 'go');
    }
    $this->register_cpt();
    $this->add_rewrite_rules();
    flush_rewrite_rules();
  }

  public function register_cpt() {
    register_post_type(self::CPT, [
      'labels' => [
        'name' => 'Link Redirects',
        'singular_name' => 'Link Redirect',
      ],
      'public' => false,
      'show_ui' => true,
      'menu_icon' => 'dashicons-randomize',
      'supports' => ['title'],
    ]);
  }

  private function prefix() {
    return sanitize_title(get_option(self::OPT_PREFIX, 'go'));
  }

  public function add_rewrite_rules() {
    add_rewrite_rule(
      '^' . preg_quote($this->prefix(), '/') . '/([^/]+)/?$',
      'index.php?xyjax_go=$matches[1]',
      'top'
    );
  }

  public function query_vars($vars) {
    $vars[] = 'xyjax_go';
    return $vars;
  }

  public function handle_redirect() {
    $slug = get_query_var('xyjax_go');
    if (!$slug) return;

    $post = get_page_by_path($slug, OBJECT, self::CPT);
    if (!$post) {
      status_header(404);
      exit('Link not found');
    }

    if (get_post_meta($post->ID, self::META_ENABLED, true) === '0') {
      status_header(410);
      exit('Link disabled');
    }

    $dest = esc_url_raw(get_post_meta($post->ID, self::META_DEST, true));
    if (!$dest) {
      status_header(500);
      exit('Destination missing');
    }

    $code = (int) get_post_meta($post->ID, self::META_CODE, true);
    if (!in_array($code, [301, 302, 307], true)) $code = 302;

    $clicks = (int) get_post_meta($post->ID, self::META_CLICKS, true);
    update_post_meta($post->ID, self::META_CLICKS, $clicks + 1);

    wp_redirect($dest, $code);
    exit;
  }

  public function meta_boxes() {
    add_meta_box('xyjax_link', 'Redirect Settings', [$this, 'render_meta'], self::CPT);
  }

  public function render_meta($post) {
    wp_nonce_field('xyjax_save', 'xyjax_nonce');

    $dest = esc_url(get_post_meta($post->ID, self::META_DEST, true));
    $code = get_post_meta($post->ID, self::META_CODE, true) ?: 302;
    $enabled = get_post_meta($post->ID, self::META_ENABLED, true);
    $enabled = ($enabled === '') ? '1' : $enabled;
    $clicks = (int) get_post_meta($post->ID, self::META_CLICKS, true);

    $short = home_url('/' . $this->prefix() . '/' . $post->post_name);

    echo "<p><strong>Short URL:</strong><br><code>{$short}</code></p>";

    echo "<p><label>Destination URL<br>
      <input type='url' name='xyjax_dest' value='{$dest}' style='width:100%'></label></p>";

    echo "<p><label>Redirect Type<br>
      <select name='xyjax_code'>
        <option value='301' " . selected($code, 301, false) . ">301 Permanent</option>
        <option value='302' " . selected($code, 302, false) . ">302 Temporary</option>
        <option value='307' " . selected($code, 307, false) . ">307 Temporary</option>
      </select></label></p>";

    echo "<p><label>
      <input type='checkbox' name='xyjax_enabled' value='1' " . checked($enabled, '1', false) . ">
      Enabled</label></p>";

    echo "<p><strong>Clicks:</strong> {$clicks}</p>";
  }

  public function save_meta($post_id) {
    if (!isset($_POST['xyjax_nonce']) || !wp_verify_nonce($_POST['xyjax_nonce'], 'xyjax_save')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

    update_post_meta($post_id, self::META_DEST, esc_url_raw($_POST['xyjax_dest'] ?? ''));
    update_post_meta($post_id, self::META_CODE, (int)($_POST['xyjax_code'] ?? 302));
    update_post_meta($post_id, self::META_ENABLED, isset($_POST['xyjax_enabled']) ? '1' : '0');
  }

  public function settings_page() {
    add_submenu_page(
      'edit.php?post_type=' . self::CPT,
      'Settings',
      'Settings',
      'manage_options',
      'xyjax-settings',
      [$this, 'render_settings']
    );
  }

  public function register_settings() {
    register_setting('xyjax_settings', self::OPT_PREFIX, [
      'sanitize_callback' => 'sanitize_title'
    ]);

    add_action('update_option_' . self::OPT_PREFIX, function () {
      flush_rewrite_rules();
    });
  }

  public function render_settings() {
    ?>
    <div class="wrap">
      <h1>Xyjax Link Redirector Settings</h1>
      <form method="post" action="options.php">
        <?php settings_fields('xyjax_settings'); ?>
        <table class="form-table">
          <tr>
            <th>Link Prefix</th>
            <td>
              <input type="text" name="<?php echo self::OPT_PREFIX; ?>" value="<?php echo esc_attr($this->prefix()); ?>">
              <p class="description">Default is <code>go</code>. Example: <code>r</code> → /r/slug</p>
            </td>
          </tr>
        </table>
        <?php submit_button(); ?>
      </form>
    </div>
    <?php
  }
}

new Xyjax_Link_Redirector();
