<?php

namespace Services;

use GuzzleHttp\Client;

class BitbucketService
{
    /** @var string */
    private const BITBUCKET_API = "https://api.bitbucket.org/2.0/repositories/";

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
     * @param string         $fullRepositoryName
     * @param integer|string $id
     * @param string         $description
     * @return void
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function changePullRequestDescription(string $fullRepositoryName, $id, string $description): void
    {
        // Get $title and $originalDescription from get request
        $title = $this->getPullRequestTitle($fullRepositoryName, $id);
        $originalDescription = $this->getPullRequestDescription($fullRepositoryName, $id);
        $baseUrl = $this->getBitbucketRepositoryUrl($fullRepositoryName);

        // Create PUT request from guzzleResponse, pass in method (required), uri and an array (can be array of arrays -
        // auth and json are preset acceptable arrays). $id and $description are passed into method. $title from getReq
        $this->httpClient->request("PUT", 'pullrequests/' . $id,
            [
                'base_uri' => $baseUrl,
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
     * @param string $fullRepositoryName
     * @param string $id
     * @return string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function getPullRequestTitle(string $fullRepositoryName, string $id): string
    {
        $baseUrl = $this->getBitbucketRepositoryUrl($fullRepositoryName);

        // Get all pull request data - can't start guzzle url with a /
        $guzzleResponse = $this->httpClient->request("GET", "pullrequests/$id",
            [
                'base_uri' => $baseUrl,
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
     * @param string         $fullRepositoryName
     * @param integer|string $id
     * @return string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function getPullRequestDescription(string $fullRepositoryName, string $id): string
    {
        $baseUrl = $this->getBitbucketRepositoryUrl($fullRepositoryName);
        // Get all pull request data
        $guzzleResponse = $this->httpClient->request("GET", 'pullrequests/' . $id,
            [
                'base_uri' => $baseUrl,
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
     * @param string $fullRepositoryName
     * @return string
     */
    private function getBitbucketRepositoryUrl(string $fullRepositoryName)
    {
        return self::BITBUCKET_API . $fullRepositoryName . "/";
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
