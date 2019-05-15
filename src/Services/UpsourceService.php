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

    /** @var string */
    private $username;

    /** @var string */
    private $password;

    // Use constructor so that a new (guzzle) client is always created when UpsourceService is instantiated
    public function __construct(Client $httpClient, string $username, string $password)
    {
        // Need a guzzle client, which is an http client that can make http requests (like Chrome)
        // Use $this to access $httpClient variable in this class and set it to new Client(); - (guzzle)
        $this->httpClient = $httpClient;
        $this->username = $username;
        $this->password = $password;

//        $this->httpClient = $this->createClient($username, $password);
    }

    /**
     * @param integer|string $projectId
     * @param string $branchName
     * @return string
     */
    public function createUpsourceReview(string $projectId, string $branchName) : string
    {
        // Creating POST request createReview and passing in projectId (name) and branch name to Upsource
        $guzzleResponse = $this->httpClient->post('http://upsource.warwickestates.net:8080/~rpc/createReview',

            [
                'auth' => $this->getAuth(),
                'json' => [
                    "projectId" => $projectId,
                    "branch" => $branchName,
                ],
            ]
        );

        // Getting contents of body from guzzleResponse (Upsource Response)
        $upsourceResponseBody = $guzzleResponse->getBody()->getContents();
        // decode body of guzzle response (pullRequest) into an array, assoc (array) = true
        $upsourceResponseArray = json_decode($upsourceResponseBody, true);
        // Extract reviewId
        $reviewId = $upsourceResponseArray['result']['reviewId']['reviewId'];
        // Add projectId (project name) to base of url and append the reviewId (HDR-CR-65)
        $upsourceBaseUrl = "http://upsource.warwickestates.net:8080/%s/review/%s";
        $upsourceReviewUrl = sprintf($upsourceBaseUrl, $projectId, $reviewId);

        var_dump($upsourceReviewUrl);

        return $upsourceReviewUrl;
    }

    // Cpnvert bitbucketRepositoryName to upsourceProjectId
    public function getUpsourceProjectId(string $bitbucketRepositoryName) : string
    {
        // map Bitbucket's repository name to Upsource's projectId todo - generalise this?
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

    private function getAuth() : array
    {
        return [
            $this->username,
            $this->password,
        ];
    }
}
