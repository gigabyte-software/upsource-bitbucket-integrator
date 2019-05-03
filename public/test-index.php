<?php

use GuzzleHttp\Client;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require '../../vendor/autoload.php';


$app = new \Slim\App([
    'settings' => [
        'displayErrorDetails' => true,
    ],
]);

error_reporting(E_ALL);
ini_set('display_errors', 1);

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

$app->run();

