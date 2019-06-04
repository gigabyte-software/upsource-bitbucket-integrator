<?php

require_once '../vendor/autoload.php';

use Controllers\HookController;
use GuzzleHttp\Client;
use Psr\Container\ContainerInterface;
use Services\BitbucketService;
use Services\UpsourceService;
use Slim\App;

// Load environment variables (for user and pass) from .env file if running locally, otherwise they are set in Heroku
if (getenv("ENVIRONMENT") !== 'prod') {
    $dotenv = Dotenv\Dotenv::create(__DIR__ . "/..");
    $dotenv->load();
}

// Instantiate Slim container and set debugging/error settings
// Dependency container instance is injected into the Slim app's constructor???
$container = new \Slim\Container([
    'settings' => [
        'displayErrorDetails' => true,
        'determineRouteBeforeAppMiddleware' => true,
        'debug' => true
    ],
]);

$app = new App($container);

// Retrieving container (not sure why this is needed)
$container = $app->getContainer();

// Add bitbucketService to Slim container
$container[BitbucketService::class] = function () {

    // Get username and password from environment variables
    $username = getenv('BITBUCKET_USERNAME');
    $password = getenv('BITBUCKET_PASSWORD');

    // Instantiate object from class BitBucketService() and pass in username and password
    $bitbucketService = new BitbucketService(new Client(), $username, $password);

    return $bitbucketService;
};

// Add upsourceService to Slim container
$container[UpsourceService::class] = function () {

    // Get username and password from environment variables
    $username = getenv('UPSOURCE_USERNAME');
    $password = getenv('UPSOURCE_PASSWORD');

    // Instantiate object from class upsourceService() and pass in username and password
    $upsourceService = new UpsourceService(new Client(), $username, $password);

    return $upsourceService;
};

/**
 * @param ContainerInterface $container
 * @return HookController
 */
$container['\Controllers\HookController'] = function (ContainerInterface $container) {
    return new HookController(
        $container->get(UpsourceService::class),
        $container->get(BitbucketService::class)
    );
};

// Route for dealing with Bitbuckets POST request (Webhook) - app accepts POST requests to this URL
// (can't show in chrome as that's a GET request)
$app->post('/bitbucket', '\Controllers\HookController:createUpsourceReview');

$app->post('/create-pull-request', '\Controllers\HookController:createUpsourceReviewWithModel');

// Route for dealing with Bitbuckets POST request (Webhook) - app accepts POST requests to this URL
// (can't show in chrome as that's a GET request)
$app->post('/upsource/close-review', '\Controllers\HookController:closeUpsourceReview');

// Run app
$app->run();

