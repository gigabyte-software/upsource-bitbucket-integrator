<?php

use Services\BitbucketService;

require_once '../vendor/autoload.php';

// Load environment variables (for user and pass) from .env file
$dotenv = Dotenv\Dotenv::create(__DIR__ . "/..");
$dotenv->load();

// Instantiate Slim container and set debugging/error settings
// Dependency container instance is injected into the Slim app's constructor???
$container = new \Slim\Container([
    'settings' => [
        'displayErrorDetails' => true,
        'determineRouteBeforeAppMiddleware' => true,
        'debug' => true],
]);

$app = new \Slim\App($container);

// Retrieving container (not sure why this is needed)
$container = $app->getContainer();
// Add bitbucketService to Slim container
$container['bitbucketService'] = function ($container) {

    // Get username and password from .env file
    $username = getenv('BITBUCKET_USERNAME');
    $password = getenv('BITBUCKET_PASSWORD');

    // Instantiate object from class BitBucketService() and pass in username and password
    $bitbucketService = new BitbucketService($username, $password);

    return $bitbucketService;
};

// Define route - searching for URL (with dev.review-creator/bucket) in chrome (slim) triggers GET request to Apache
// (dev.review-creator points to review creator from config file in vhost (vagrant). Slim app then runs HookController
$app->get('/bitbucket/{id}', '\Controllers\HookController:index');

// Run app
$app->run();
