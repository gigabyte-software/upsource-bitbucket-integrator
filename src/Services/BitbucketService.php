<?php

namespace Services;

use BitBucket\PullRequest;
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
     * @param PullRequest $pullRequest
     * @param string      $upsourceUrl
     * @return void
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function changePullRequestDescription(PullRequest $pullRequest, string $upsourceUrl): void
    {
        // Get $title, $id and $originalDescription from $pullRequest object
        $title = $pullRequest->getTitle();
        $id = $pullRequest->getId();
        $originalDescription = $pullRequest->getDescription();
        $baseUrl = $this->getBitbucketRepositoryUrl($pullRequest->getFullRepositoryName());

        // Create PUT request from guzzleResponse, pass in method (required), uri and an array (can be array of arrays -
        // auth and json are preset acceptable arrays). $id and $description are passed into method. $title from getReq
        $this->httpClient->request("PUT", 'pullrequests/' . $id,
            [
                'base_uri' => $baseUrl,
                'auth' => $this->getAuth(),
                'json' => [
                    'id' => $id,
                    'title' => $title,
                    'description' => $originalDescription . " " . $upsourceUrl,
                ],
            ]
        );
    }

    public function checkForMerge() {
        // todo - check for merge and if branch was closed?
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
