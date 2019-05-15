<?php

namespace Services;

use GuzzleHttp\Client;

class BitbucketService
{
    /** @var Client */
    private $httpClient;

    /** @var string */
    private $username;

    /** @var string */
    private $password;

    /**
     * @param Client $httpClient
     * @param string $username
     * @param string $password
     */
    // Use constructor so that a new (guzzle) client is always created when BitbucketService is called (instantiated?)
    public function __construct(Client $httpClient, string $username, string $password)
    {
        // Need guzzle (http client) that to make requests with (like Chrome)
        // Use $this to access $httpClient variable in this class and set it to new Client(); - (guzzle)
        $this->httpClient = $httpClient;
        $this->username = $username;
        $this->password = $password;
    }

    // Method for retrieving title from first pull request
    /**
     * @param integer|string $id
     * @return string
     */
    private function getFirstPullRequestTitle(string $id) : string
    {
        // Get all pull request data
        $guzzleResponse = $this->httpClient->request("GET",
            "https://api.bitbucket.org/2.0/repositories/gigabyte-software/review-creator/pullrequests/$id",
            ['auth' => $this->getAuth()]);

        // Getting contents of body from guzzleResponse (a long line of json text)
        $pullRequestBody = $guzzleResponse->getBody()->getContents();

        // decode body of guzzle response (pullRequest) into an array, assoc (array) = true
        $pullRequest = json_decode($pullRequestBody, true);

        // Return the title from $pullRequest
        return $pullRequest['title'];
    }

    /**
     * @param integer|string $id
     * @return string
     */
    private function getPullRequestDescription(string $id) : string
    {
        // Get all pull request data
        $guzzleResponse = $this->httpClient->request("GET",
            'https://api.bitbucket.org/2.0/repositories/gigabyte-software/review-creator/pullrequests/' . $id,
            ['auth' => $this->getAuth()]);

        // Getting contents of body from guzzleResponse (json text)
        $pullRequestBody = $guzzleResponse->getBody()->getContents();
        // decode body of guzzle response (pullRequest) into an array, assoc (array) = true
        $pullRequestArray = json_decode($pullRequestBody, true);

        // Return the title from $pullRequestArray
        return $pullRequestArray['description'];
    }

    // Method for changing description - pass in $id, $title and $description when called
    /**
     * @param integer|string $id
     * @param string $description
     * @return string
     */
    public function changeDescription(string $id, string $description) : void
    {
        // Get $title and $originalDescription from get request (access method with $this as it's in the same class)
        $title = $this->getFirstPullRequestTitle($id);
        $originalDescription = $this->getPullRequestDescription($id);

        // Create PUT request from guzzleResponse, pass in method (required), uri and an array (can be array of arrays -
        // auth and json are preset acceptable arrays). $id and $description are passed into method. $title from getReq
        $this->httpClient->request(
            "PUT",
            'https://api.bitbucket.org/2.0/repositories/gigabyte-software/review-creator/pullrequests/' . $id,
            [
                'auth' => $this->getAuth(),
                'json' => [
                    'id' => $id,
                    'title' => $title,
                    'description' => $originalDescription . " " . $description,
//                    'description' => '', // reset description
                ]
            ]
        );
    }

    private function getAuth() : array
    {
        return [
            $this->username,
            $this->password,
        ];
    }
}
