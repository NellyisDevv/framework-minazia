<?php

require __DIR__ . '/plugin-update-checker/plugin-update-checker.php';
// require 'plugin-update-checker/plugin-update-checker.php';

use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

$myUpdateChecker = PucFactory::buildUpdateChecker(
    'https://github.com/NellyisDevv/framework-minazia/',
    __FILE__,
    'framework-minazia'
);

// Set the branch that contains the stable release.
$myUpdateChecker->setBranch('main');  // or 'master' depending on your default branch

defined('ABSPATH') || exit;

add_action('wp_enqueue_scripts', function() {
    // Enqueue parent theme (Divi) style
    wp_enqueue_style('divi-parent-style', get_template_directory_uri() . '/style.css', array(), null);

    // Enqueue combined and minified child theme style dynamically
    wp_enqueue_style(
        'framework-minazia-style',
        get_stylesheet_directory_uri() . '/style.min.css',
        array('divi-parent-style'), // Dependency ensures parent loads first
        filemtime(get_stylesheet_directory() . '/style.min.css') // Dynamic versioning based on file modification time
    );
});

add_action('admin_menu', 'register_theme_license_menu');
function register_theme_license_menu() {
    add_menu_page('Theme License', 'License', 'manage_options', 'theme-license', 'license_page_callback', 'dashicons-lock');
}

function license_page_callback() {
    if (isset($_POST['submit_license'])) {
        update_option('child_theme_license_key', sanitize_text_field($_POST['license_key']));
        if (is_theme_license_valid()) {
            echo '<div class="updated"><p>License key saved and validated successfully!</p></div>';
        } else {
            echo '<div class="error"><p>Invalid or inactive license key. Please try again.</p></div>';
        }
    }
    $license_key = get_option('child_theme_license_key', '');
    ?>
    <div class="wrap">
        <h1>Activate Your Theme</h1>
        <form method="post">
            <label for="license_key">Enter License Key:</label>
            <input type="text" name="license_key" value="<?php echo esc_attr($license_key); ?>" size="40" />
            <input type="submit" name="submit_license" class="button-primary" value="Activate" />
        </form>
    </div>
    <?php
}

function is_theme_license_valid() {
    $license_key = get_option('child_theme_license_key', '');
    if (empty($license_key)) {
        error_log("License key is empty.");
        return false;
    }

    global $wpdb;
    $table = $wpdb->prefix . 'wckm_keys'; // Matches your table name
    $result = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $table WHERE `code` = %s AND status = 'sold'", // Use 'code' column and check for 'sold'
        $license_key
    ));

    error_log("Checking key: " . $license_key . ", Table: " . $table . ", Result (code, status=sold): " . $result);

    return $result > 0;
}

add_action('wp', 'restrict_theme_without_license');
function restrict_theme_without_license() {
    if (!is_theme_license_valid() && !is_admin()) {
        wp_die('Please activate your theme with a valid license key. Go to <a href="' . admin_url('admin.php?page=theme-license') . '">License</a> to enter your key.');
    }
}
