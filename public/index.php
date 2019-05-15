<?php

use Services\BitbucketService;
use Services\UpsourceService;

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
$container[BitbucketService::class] = function () {

    // Get username and password from .env file
    $username = getenv('BITBUCKET_USERNAME');
    $password = getenv('BITBUCKET_PASSWORD');

    // Instantiate object from class BitBucketService() and pass in username and password
    $bitbucketService = new BitbucketService(new \GuzzleHttp\Client(), $username, $password);

    return $bitbucketService;
};

// Add upsourceService to Slim container
$container[UpsourceService::class] = function () {

    // Get username and password from .env file
    $username = getenv('UPSOURCE_USERNAME');
    $password = getenv('UPSOURCE_PASSWORD');

    // Instantiate object from class upsourceService() and pass in username and password
    $upsourceService = new UpsourceService($username, $password);

    return $upsourceService;
};

$container['\Controllers\HookController'] = function (\Psr\Container\ContainerInterface $container) {
    return new \Controllers\HookController(
        $container->get(UpsourceService::class),
        $container->get(BitbucketService::class)
    );
};

// Route for dealing with Bitbuckets POST request (Webhook) - Use Postman to simulate and see responses.
// Telling app to accept POST requests to this URL (can't show in chrome as that's a GET request)
$app->post('/bitbucket', '\Controllers\HookController:createUpsourceReview');

$app->get('/hello', function($request) {
    echo 'hello';
});

// Run app
$app->run();
