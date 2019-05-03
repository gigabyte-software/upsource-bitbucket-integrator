<?php

namespace Controllers;

use GuzzleHttp\Client;

class BitbucketService
{
    // Define $httpClient as private so it is only available within this class
    private $httpClient;

    // Use constructor so that a new (guzzle) client is always created when BitbucketService is called (instantiated?)
    public function __construct()
    {
        // I need a guzzle client, which is an http client that I can make requests with (like Chrome)
        // Use $this to access $httpClient variable in this class and set it to new Client(); - (guzzle)
        $this->httpClient = new Client();
    }

    // Method for retrieving title from first pull request - will need to make this more generic later
    private function getFirstPullRequestTitle()
    {
        // First I need to get all pull request data
        $guzzleResponse = $this->httpClient->request("GET",
            'https://api.bitbucket.org/2.0/repositories/gigabyte-software/review-creator/pullrequests/',
            [
                'auth' => [
                    'rowBawTick',
                    'fr%XUtC7git'
                ]
            ]
        );

        // Getting contents of body from guzzleResponse (which is a long line of json text)
        $pullRequestBody = $guzzleResponse->getBody()->getContents();
        // decode body of guzzle response (pullRequest) into an array, assoc (array) = true
        $pullRequestArray = json_decode($pullRequestBody, true);
        var_dump($pullRequestArray);
        //    Debugging...
        //    $debug = print_r($pullRequestArray['pagelen'], true);
        // Return the title from $pullRequestArray
        return $pullRequestArray['values'][0]['title'];
    }

    // Method for changing description - pass in $id and $description when called - still hard coded atm.
    public function changeDescription($id, $description)
    {
        // Set $title to title from get request (can access with $this as it's in the same class)
        $title = $this->getFirstPullRequestTitle();

//    echo '<pre>',print_r($pullRequestBody,1),'</pre>';

        // Create PUT request from guzzleResponse, pass in method (required), uri and an array (can be array of arrays -
        // auth and json are preset acceptable arrays). $id and $description are passed into method. $title from getReq
        $guzzleResponse = $this->httpClient->request(
            "PUT",
            'https://api.bitbucket.org/2.0/repositories/gigabyte-software/review-creator/pullrequests/' . $id,
            [
                'auth' => [
                    'rowBawTick',
                    'fr%XUtC7git',
                ],
                'json' => [
                    'id' => $id,
                    'title' => $title,
//                'title' => $pullRequestArray['values'][0]['title'],
                    'description' => $description
                ]
            ]
        );

        // returning this otherwise it isn't used - not sure why now...
        return $guzzleResponse;
    }
}
