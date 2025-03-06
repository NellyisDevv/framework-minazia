<?php
/**
 * Plugin Name: Minazia Framework
 * Plugin URI: https://www.minazia.com
 * Description: A flexible framework for Divi.
 * Version: 1.3.6
 * Author: Minazia CO
 * Author URI: https://www.minazia.com
 * Text Domain: minazia-framework
 * Requires at least: 5.0
 * Requires PHP: 7.2
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

// Set the branch that contains the stable release
$myUpdateChecker->setBranch('main');

// Enqueue styles
add_action('wp_enqueue_scripts', function() {
    // Enqueue our plugin's CSS
    wp_enqueue_style(
        'framework-minazia-style',
        plugin_dir_url(__FILE__) . 'style.min.css',
        array(), // No dependencies as a plugin
        filemtime(plugin_dir_path(__FILE__) . 'style.min.css') // Dynamic versioning
    );
});

// Add license menu
add_action('admin_menu', 'minazia_register_license_menu');
function minazia_register_license_menu() {
    add_menu_page('Framework License', 'Minazia License', 'manage_options', 'minazia-license', 'minazia_license_page_callback', 'dashicons-lock');
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
    $table = $wpdb->prefix . 'wckm_keys'; // Matches your table name
    $result = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $table WHERE `code` = %s AND status = 'sold'",
        $license_key
    ));

    return $result > 0;
}

// Check for valid license
add_action('wp', 'minazia_restrict_without_license');
function minazia_restrict_without_license() {
    if (!minazia_is_license_valid() && !is_admin()) {
        wp_die('Please activate Minazia Framework with a valid license key. Go to <a href="' . admin_url('admin.php?page=minazia-license') . '">Minazia License</a> to enter your key.');
    }
}