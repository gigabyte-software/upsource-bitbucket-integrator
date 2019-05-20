<?php

namespace Services;

use GuzzleHttp\Client;

class BitbucketService
{
    /** @var string */
    private const BITBUCKET_BRANCH_API = "https://api.bitbucket.org/2.0/repositories/gigabyte-software/review-creator/";

    /** @var Client */
    private $httpClient;

    /** @var string */
    private $username;

    /** @var string */
    private $password;

    /**
     * Use constructor so that a new (guzzle) client is always created when BitbucketService is instantiated
     * @param Client $httpClient
     * @param string $username
     * @param string $password
     */

    public function __construct(Client $httpClient, string $username, string $password)
    {
        // Need guzzle (http client) that to make requests with (like Chrome)
        // Use $this to access $httpClient variable in this class and set it to new Client(); - (guzzle)
        $this->httpClient = $httpClient;
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * @param integer|string $id
     * @param string $description
     * @return string
     */
    public function changePullRequestDescription(string $id, string $description): void
    {
        // Get $title and $originalDescription from get request
        $title = $this->getPullRequestTitle($id);
        $originalDescription = $this->getPullRequestDescription($id);

        // Create PUT request from guzzleResponse, pass in method (required), uri and an array (can be array of arrays -
        // auth and json are preset acceptable arrays). $id and $description are passed into method. $title from getReq
        $this->httpClient->request("PUT", 'pullrequests/' . $id,
            [
                'base_uri' => self::BITBUCKET_BRANCH_API,
                'auth' => $this->getAuth(),
                'json' => [
                    'id' => $id,
                    'title' => $title,
                    'description' => $originalDescription . " " . $description,
                ],
            ]
        );
    }

    /**
     * @param integer|string $id
     * @return string
     */
    private function getPullRequestTitle(string $id): string
    {
        // Get all pull request data
        $guzzleResponse = $this->httpClient->request("GET", "pullrequests/$id",
            [
                'base_uri' => self::BITBUCKET_BRANCH_API,
                'auth' => $this->getAuth(),
            ]
        );

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
    private function getPullRequestDescription(string $id): string
    {
        // Get all pull request data
        $guzzleResponse = $this->httpClient->request("GET", 'pullrequests/' . $id,
            [
                'base_uri' => self::BITBUCKET_BRANCH_API,
                'auth' => $this->getAuth(),
            ]
        );

        // Getting contents of body from guzzleResponse (json text)
        $pullRequestBody = $guzzleResponse->getBody()->getContents();
        // decode body of guzzle response (pullRequest) into an array, assoc (array) = true
        $pullRequestArray = json_decode($pullRequestBody, true);

        // Return the description from $pullRequestArray
        return $pullRequestArray['description'];
    }

    /**
     * @return array
     */
    private function getAuth(): array
    {
        return [
            $this->username,
            $this->password,
        ];
    }
}
