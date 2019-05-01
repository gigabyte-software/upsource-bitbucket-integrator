<?php

use GuzzleHttp\Client;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require '../../vendor/autoload.php';

$app = new \Slim\App([
    'debug' => true,
]);

// app is dealing with a get request of the format http://dev.review-creator/hello/chris/chambers
// Taking $args from {}, setting $request variable and instantiating $response variable
$app->get('/hello/{name}/{last}', function (Request $request, Response $response, array $args) {

    // Setting $accept = "Accept" object in Request Header (which slim makes from HTTP request)
    $accept = $request->getHeader('Accept');
    #var_dump($accept);
    $name = $args['name'];
    $lastName = $args['last'];
    // writing string + $name etc to body of response (not changing the HTTP response)
    $response->getBody()->write("Hello, $name $lastName. My header: $accept[0]");
    $errorResponse = $response->withStatus(400);

    return $response;
});


$app->get('/', function (Request $request, Response $response) {

    $response->getBody()->write("Yo, you're on the home page");

    return $response;
});


// This is a route which returns the gigabyte home page
$app->get('/gigabyte', function (Request $request, Response $response) {

    // I need a guzzle client, which is an http client that I can make requests with (like Chrome)
    $guzzleHttpClient = new Client();

    // I want to use guzzle to make a get request to https://gigabyte.software
    $guzzleResponse = $guzzleHttpClient->request('GET', 'https://gigabyte.software/');

    // I want to get the body of the response from the guzzle response object which guzzle returns from my request
    $gigabyteHomePage = $guzzleResponse->getBody();

    // I now want to create a slim response object and populate/write the contents of the guzzle body to it and return
    // Writing $gigabyteHomePage to body of response without changing response (General, response headers, request headers)
    $response->getBody()->write($gigabyteHomePage);

//    echo "this is the body ...." . $response->getBody();

    return $response;
});

$app->get('/gigabot', function (Request $request, Response $response) {

    $client = new Client([
        'base_uri' => 'https://gigabyte.software'
    ]);

    $response = $client->request('GET', '');
    $body = $response->getBody();
    $response->getBody()->write($body);

    return $response;
});

$app->run();

