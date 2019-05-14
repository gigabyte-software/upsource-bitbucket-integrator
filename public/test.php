<?php

// This page is working test code with notes that I want to keep for reference!!!!

use GuzzleHttp\Client;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require '../vendor/autoload.php';


$app = new \Slim\App([
    'settings' => [
        'displayErrorDetails' => true,
    ],
]);

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Create GET request from Slim (Chrome) to Apache, searching for URL (with /bucket) in chrome triggers
// slim app to run this method (function?)
$app->get('/bucket', function (Request $request, Response $response) {

    // I need a guzzle client, which is an http client that I can make requests with (like Chrome)
    $guzzleHttpClient = new Client();
    $guzzleResponse = $guzzleHttpClient->request("GET",'https://api.bitbucket.org/2.0/repositories/gigabyte-software/review-creator/pullrequests',
        ['auth' => [
            'rowBawTick',
            'fr%XUtC7git'
        ]]);
    $pullRequest = $guzzleResponse->getBody();
    $response->getBody()->write($pullRequest);

    return $response;
});

// Create GET request from Slim (Chrome) to Apache, searching for URL (with /putbucket/1) in chrome triggers
// slim app to run this method (function?) - simulated id in URL - todo get id before this and then append it onto URL
$app->get('/putbucket/{id}', function (Request $request, Response $slimResponse, $args) {

    // I need a guzzle client, which is an http client that I can make requests with (like Chrome)
    $guzzleHttpClient = new Client();
    // First I need to get pull request data
    $guzzleResponse = $guzzleHttpClient->request("GET",'https://api.bitbucket.org/2.0/repositories/gigabyte-software/review-creator/pullrequests',
        ['auth' => [
            'rowBawTick',
            'fr%XUtC7git'
        ]]
    );

    // Getting contents of body from guzzleResponse (which is a long line of json text)
    $pullRequestBody = $guzzleResponse->getBody()->getContents();
    // decode body of guzzle response (pullRequest) into an array, assoc (array) = true
    $pullRequestArray = json_decode($pullRequestBody, true);

    //    Debugging...
    //    $debug = print_r($pullRequestArray['pagelen'], true);
    $debug = print_r($pullRequestArray['values'][0]['title'], true);
//    echo '<pre>',print_r($pullRequestBody,1),'</pre>';

    // Create PUT request from guzzleResponse, pass in method (required), uri and an array (can be array of arrays -
    // auth and json are preset acceptable arrays)
    $guzzleResponse = $guzzleHttpClient->request(
        "PUT",
        'https://api.bitbucket.org/2.0/repositories/gigabyte-software/review-creator/pullrequests/' . $args['id'],
        [
            'auth' => [
                'rowBawTick',
                'fr%XUtC7git',
            ],
            'json' => [
                'id' => 1,
                'title' => 'Test2',
//                'title' => $pullRequestArray['values'][0]['title'],
                'description' => "Test2 put request"
            ]
        ]
    );

    // Write $debug to body of slim response so I can see it in the browser
    $slimResponse->getBody()->write($debug);

    return $slimResponse;
});


// Create a POST request for a message to BitBucket??
$app->get('/bitbucket', function (Request $request, Response $response) {

    $guzzleHttpClient = new Client([
        'base_uri' => ' https://api.bitbucket.org/2.0/'
    ]);
    $guzzleResponse = $guzzleHttpClient->request('GET','repositories/gigabyte-software/review-creator/pullrequests/1', [
        'auth' => [
            'rowBawTick',
            'fr%XUtC7git'
        ]
    ]);

    $pullRequest = $guzzleResponse->getBody();
    $response->getBody()->write($pullRequest);

    return $response;
});

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

