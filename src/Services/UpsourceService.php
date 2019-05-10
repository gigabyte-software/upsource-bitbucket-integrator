<?php

namespace Services;

use GuzzleHttp\Client;

class UpsourceService
{

    /**
     * Define $httpClient as private so it is only available within this class
     * @var Client
     */
    private $httpClient;

    // Use constructor so that a new (guzzle) client is always created when UpsourceService is instantiated
    public function __construct($username, $password)
    {
        // I need a guzzle client, which is an http client that I can make requests with (like Chrome)
        // Use $this to access $httpClient variable in this class and set it to new Client(); - (guzzle)
        $this->httpClient = $this->createClient($username, $password);
    }

    public function createUpsourceReview($projectId, $branchName)
    {
        // todo - extract project name from webhook and enter it is as projectName
        $guzzleResponse = $this->httpClient->post('createReview', [
            'json' => [
                "projectId" => $projectId,
                "branch" => $branchName,
            ]
        ]);

        // Getting contents of body from guzzleResponse
        $upsourceResponseBody = $guzzleResponse->getBody()->getContents();
        // decode body of guzzle response (pullRequest) into an array, assoc (array) = true
        $upsourceResponseArray = json_decode($upsourceResponseBody, true);

        $reviewId = $upsourceResponseArray['result']['reviewId']['reviewId'];

        $upsourceBaseUrl = "http://upsource.warwickestates.net:8080/%s/review/%s";
        $upsourceReviewUrl = sprintf($upsourceBaseUrl, $projectId, $reviewId);

        return $upsourceReviewUrl;
    }

    /**
     * @param string $username
     * @param string $password
     * @return Client
     */
    private function createClient($username, $password)
    {
        return new Client([
            'base_uri' => "http://upsource.warwickestates.net:8080/~rpc/",
            'auth' => [$username, $password],
        ]);
    }

}
