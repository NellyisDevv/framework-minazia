<?php

require __DIR__ . '/plugin-update-checker/plugin-update-checker.php';
// require 'plugin-update-checker/plugin-update-checker.php';



use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

$myUpdateChecker = PucFactory::buildUpdateChecker(
    'https://github.com/NellyisDevv/framework-minazia/',
    __FILE__,
    'framework-minazia'
);

//Set the branch that contains the stable release.
$myUpdateChecker->setBranch('main');  // or 'master' depending on your default branch
?>

<?php
defined('ABSPATH') || exit;

add_action('wp_enqueue_scripts', function() {
    // Enqueue parent theme (Divi) style
    wp_enqueue_style('divi-parent-style', get_template_directory_uri() . '/style.css', array(), null);

    // Enqueue child theme (framework-minazia) style
    wp_enqueue_style(
        'framework-minazia-style',
        get_stylesheet_uri(),
        array('divi-parent-style'), // Dependency ensures parent loads first
        '1.0.3' // Set to your current version
        // Or use filemtime(get_stylesheet_directory() . '/style.css') for dynamic versioning
    );
});
?>