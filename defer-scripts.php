<?php
// purpose: Video modules slow down website here is the fix
// defer-scripts.php
// Prevent direct access to this file
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

function defer_non_critical_scripts($tag, $handle, $src) {
    // List of script handles or sources to defer (customize this)
    $scripts_to_defer = array(
        'youtube-iframe-api', // Example handle for YouTube scripts
        'vimeo-player',       // Example handle for Vimeo scripts
        'some-custom-script', // Replace with your script handles
    );

    // Check if the script is in our defer list or contains common video script URLs
    if (in_array($handle, $scripts_to_defer) || strpos($src, 'youtube.com') !== false || strpos($src, 'vimeo.com') !== false) {
        // Add defer attribute to the script tag
        $tag = str_replace(' src', ' defer="defer" src', $tag);
    }

    return $tag;
}
add_filter('script_loader_tag', 'defer_non_critical_scripts', 10, 3);