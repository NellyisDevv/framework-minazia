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