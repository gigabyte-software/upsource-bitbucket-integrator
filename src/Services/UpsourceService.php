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

    /**
     * @param integer|string $projectId
     * @param string $branchName
     * @return string
     */
    public function createUpsourceReview($projectId, $branchName)
    {
        // todo - extract project name from webhook and enter it is as projectName (Not always possible?)
        // Creating POST request createReview and passing in projectId (name) and branch name to Upsource
        $guzzleResponse = $this->httpClient->post('createReview', [
            'json' => [
                "projectId" => $projectId,
                "branch" => $branchName,
            ],
        ]);

        // Getting contents of body from guzzleResponse (Upsource Response)
        $upsourceResponseBody = $guzzleResponse->getBody()->getContents();
        // decode body of guzzle response (pullRequest) into an array, assoc (array) = true
        $upsourceResponseArray = json_decode($upsourceResponseBody, true);
        // Extract reviewId, this is appended to url
        $reviewId = $upsourceResponseArray['result']['reviewId']['reviewId'];
        // Add projectId (project name) to base of url and append the reviewId (HDR-CR-65)
        $upsourceBaseUrl = "http://upsource.warwickestates.net:8080/%s/review/%s";
        $upsourceReviewUrl = sprintf($upsourceBaseUrl, $projectId, $reviewId);

        return $upsourceReviewUrl;
    }

    public function getUpsourceProjectId($bitbucketRepositoryName)
    {
        // map Bitbucket's repository name to Upsource's projectId
        $repositoryMap = [
            'hydra' => 'hydra',
            'frontend' => 'hydra',
            'development-performance-reports' => 'hydra',
            'box' => 'hydra',
            'mobile' => 'unicorn',
            'environments' => 'unicorn',
            'unicron-domain' => 'unicorn',
            'fe1' => 'unicorn',
            'micro1' => 'unicorn',
            'infra' => 'unicorn',
            'review-creator' => 'review-creator',
        ];

        $upsourceProjectId = $repositoryMap[$bitbucketRepositoryName];

        return $upsourceProjectId;
    }
}
