<?php
/**
 * Plugin Name: Minazia Framework
 * Plugin URI: https://www.minazia.com
 * Description: A flexible framework for Divi.
 * Version: 1.3.7
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

// Enqueue styles or inject CSS manually only if license is valid
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

// Fallback: Manually inject CSS into wp_head if needed
add_action('wp_head', function() {
    if (minazia_is_license_valid() && !wp_style_is('framework-minazia-style', 'enqueued')) {
        echo '<link rel="stylesheet" href="' . esc_url(plugin_dir_url(__FILE__) . 'style.min.css') . '?ver=' . filemtime(plugin_dir_path(__FILE__) . 'style.min.css') . '" type="text/css" media="all" />';
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
        $new_license_key = sanitize_text_field($_POST['license_key']);
        $old_license_valid = minazia_is_license_valid(); // Check current status
        update_option('minazia_framework_license_key', $new_license_key);
        
        if (minazia_is_license_valid()) {
            // Update a cache-busting timestamp when license becomes valid
            update_option('minazia_framework_cache_buster', time());
            echo '<div class="updated"><p>License key saved and validated successfully!</p></div>';
            
            // Redirect to front end with cache buster to force refresh
            if (!$old_license_valid) {
                wp_redirect(home_url('?minazia_cache_buster=' . get_option('minazia_framework_cache_buster', time())));
                exit;
            }
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

// Filter URLs to include cache buster when license is valid
add_filter('wp_redirect', 'minazia_add_cache_buster_to_redirect', 10, 2);
add_filter('home_url', 'minazia_add_cache_buster_to_home_url', 10, 4);
function minazia_add_cache_buster_to_home_url($url, $path, $orig_scheme, $blog_id) {
    if (minazia_is_license_valid()) {
        $cache_buster = get_option('minazia_framework_cache_buster', '');
        if (!empty($cache_buster) && !is_admin()) {
            $url = add_query_arg('minazia_cache_buster', $cache_buster, $url);
        }
    }
    return $url;
}
function minazia_add_cache_buster_to_redirect($location, $status) {
    if (minazia_is_license_valid() && strpos($location, 'minazia_cache_buster') === false) {
        $cache_buster = get_option('minazia_framework_cache_buster', '');
        if (!empty($cache_buster)) {
            $location = add_query_arg('minazia_cache_buster', $cache_buster, $location);
        }
    }
    return $location;
}