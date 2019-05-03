<?php

require_once '../vendor/autoload.php';

$app = new \Slim\App([
    'settings' => [
        'displayErrorDetails' => true,
    ],
]);

// Define route
// Searching for URL (with dev.review-creator/bucket) in chrome (slim) triggers GET request to Apache
// (dev.review-creator points to review creator from config file in vhost (vagrant). Slim app then runs HookController
$app->get('/bitbucket/{id}', '\Controllers\HookController:index');

// Run app
$app->run();
