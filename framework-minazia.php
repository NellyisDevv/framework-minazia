<?php
/**
 * Plugin Name: Minazia Framework
 * Plugin URI: https://www.minazia.com
 * Description: A flexible framework for Divi.
 * Version: 1.4.0
 * Author: Minazia CO
 * Author URI: https://www.minazia.com
 * Text Domain: minazia-framework
 * Requires at least: 5.0
 * Requires PHP: 7.2
 * Plugin Icon: icon.svg
 */

// Exit if accessed directly
defined('ABSPATH') || exit;

// Include the plugin update checker
require __DIR__ . '/plugin-update-checker/plugin-update-checker.php';

use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

// Setup the update checker
$myUpdateChecker = PucFactory::buildUpdateChecker(
    'https://github.com/NellyisDevv/framework-minazia/',
    __FILE__,
    'framework-minazia'
);
$myUpdateChecker->setBranch('main');

// Enqueue styles with high priority only if license is valid
add_action('wp_enqueue_scripts', function() {
    if (minazia_is_license_valid()) {
        wp_enqueue_style(
            'framework-minazia-style',
            plugin_dir_url(__FILE__) . 'style.min.css',
            array(),
            filemtime(plugin_dir_path(__FILE__) . 'style.min.css')
        );
    }
}, 9999);

// Add license menu
add_action('admin_menu', 'minazia_register_license_menu');
function minazia_register_license_menu() {
    add_menu_page(
        'Framework License',
        'Minazia',
        'manage_options',
        'minazia_license',
        'minazia_license_page_callback',
        'dashicons-superhero'
    );
}

// License page callback
function minazia_license_page_callback() {
    if (isset($_POST['submit_license'])) {
        update_option('minazia_framework_license_key', sanitize_text_field($_POST['license_key']));
        if (minazia_is_license_valid()) {
            echo '<div class="updated"><p>License key saved and validated successfully!</p></div>';
        } else {
            echo '<div class="error"><p>Invalid or inactive license key. Please try again.</p></div>';
        }
    }
    $license_key = get_option('minazia_framework_license_key', '');
    ?>
    <div class="wrap">
        <h1>Activate Minazia Framework</h1>
        <form method="post">
            <label for="license_key">Enter License Key:</label>
            <input type="text" name="license_key" value="<?php echo esc_attr($license_key); ?>" size="40" />
            <input type="submit" name="submit_license" class="button-primary" value="Activate" />
        </form>
    </div>
    <?php
}

// Validate license
function minazia_is_license_valid() {
    $license_key = get_option('minazia_framework_license_key', '');
    if (empty($license_key)) {
        error_log("License key is empty.");
        return false;
    }

    global $wpdb;
    $table = $wpdb->prefix . 'wckm_keys';
    $result = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $table WHERE `code` = %s AND status = 'sold'",
        $license_key
    ));

    return $result > 0;
}

// Add banner if license is invalid
add_action('wp_footer', 'minazia_add_license_banner');
function minazia_add_license_banner() {
    if (!minazia_is_license_valid() && current_user_can('manage_options')) {
        $activation_url = admin_url('admin.php?page=minazia_license');
        echo '
        <div style="position: fixed; bottom: 20px; right: 20px; background-color: #f44336; color: white; padding: 10px 20px; border-radius: 5px; z-index: 9999;">
            <p style="margin: 0;">Please activate your Minazia Framework license. <a href="' . esc_url($activation_url) . '" style="color: white; text-decoration: underline;">Activate Now</a></p>
        </div>
        ';
    }
}