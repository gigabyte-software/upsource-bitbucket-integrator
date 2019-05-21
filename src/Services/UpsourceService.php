<?php

namespace Services;

use GuzzleHttp\Client;

class UpsourceService
{
    /** @var string */
    private const UPSOURCE_PROJECT_BASE_URL = "http://upsource.warwickestates.net:8080/~rpc/";

    /**
     * @var Client
     */
    private $httpClient;

    /** @var string */
    private $username;

    /** @var string */
    private $password;

    // Use constructor so that a new (guzzle) client is always created when UpsourceService is instantiated

    /**
     * UpsourceService constructor.
     * @param Client $httpClient
     * @param string $username
     * @param string $password
     */
    public function __construct(Client $httpClient, string $username, string $password)
    {
        // Need a guzzle client, which is an http client that can make http requests (like Chrome)
        // Use $this to access $httpClient variable in this class and set it to new Client(); - (guzzle)
        $this->httpClient = $httpClient;
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * @param string $bitbucketRepositoryName
     * @param string $bitbucketBranchName
     * @return string
     */
    public function createUpsourceReview(string $bitbucketRepositoryName, string $bitbucketBranchName): string
    {
        $upsourceProjectId = $this->getUpsourceProjectId($bitbucketRepositoryName);

        // Extract upsourceBranchName (not always exactly the same as the bitbucketBranchName)
        $upsourceBranchName = $this->getUpsourceBranchName($upsourceProjectId, $bitbucketBranchName);

        // Extract reviewId
        $upsourceReviewId = $this->getUpsourceReviewId($upsourceProjectId, $upsourceBranchName);

        // if upsourceReviewId doesn't already exist, create a review. If not, pass to url and append description.
        if (!$upsourceReviewId) {
            // Creating POST request createReview and passing in upsourceProjectId (name) and bitbucketBranchName to Upsource
            $guzzleResponse = $this->httpClient->post('createReview',
                [
                    'base_uri' => self::UPSOURCE_PROJECT_BASE_URL,
                    'auth' => $this->getAuth(),
                    'json' => [
                        "projectId" => $upsourceProjectId,
                        "branch" => $upsourceBranchName, // todo fix this? - changed it from bitbucketBranchName to upsourceBranchName
                    ],
                ]
            );

            // Getting contents of body from guzzleResponse (Upsource Response)
            $upsourceResponseBody = $guzzleResponse->getBody()->getContents();
            // decode body of guzzle response (pullRequest) into an array, assoc (array) = true
            $upsourceResponseArray = json_decode($upsourceResponseBody, true);

            // Extract upsourceReviewId from createReview POST request to UpSource
            $upsourceReviewId = $upsourceResponseArray['result']['reviewId']['reviewId'];
        }

        // Add projectId (project name) to base of url and append the upsourceReviewId (e.g. HDR-CR-65)
        $upsourceBaseUrl = "http://upsource.warwickestates.net:8080/%s/review/%s";
        $upsourceReviewUrl = sprintf($upsourceBaseUrl, $upsourceProjectId, $upsourceReviewId);

        return $upsourceReviewUrl;
    }

    /**
     * Convert bitbucketRepositoryName to upsourceProjectId
     * @param string $bitbucketRepositoryName
     * @return string
     */
    private function getUpsourceProjectId(string $bitbucketRepositoryName): string
    {
        // map Bitbucket's repository name to Upsource's projectId todo - generalise this?
        $repositoryMap = [
            'hydra' => 'hydra',
            'frontend' => 'hydra',
            'development-performance-reports' => 'hydra',
            'box' => 'hydra',
            'mobile' => 'unicorn',
            'environments' => 'unicorn',
            'unicorn-domain' => 'unicorn',
            'fe1' => 'unicorn',
            'micro1' => 'unicorn',
            'infra' => 'unicorn',
            'review-creator' => 'review-creator',
        ];

        // Return upsourceProjectId
        return $repositoryMap[$bitbucketRepositoryName];
    }

    /**
     * Find the upsourceBranchName from the bitbucketBranchName (they are not always equal)
     * @param string $upsourceProjectId
     * @param string $bitbucketBranchName
     * @return string
     */
    private function getUpsourceBranchName(string $upsourceProjectId, string $bitbucketBranchName): string
    {
        $guzzleResponse = $this->httpClient->post('findBranches',
            [
                'base_uri' => self::UPSOURCE_PROJECT_BASE_URL,
                'auth' => $this->getAuth(),
                'json' => [
                    'projectId' => $upsourceProjectId,
                    'pattern' => $bitbucketBranchName,
                    'limit' => 100,
                ],
            ]
        );

        // Getting contents of body from guzzleResponse (Upsource Response)
        $upsourceResponseBody = $guzzleResponse->getBody()->getContents();

        // decode body of guzzle response (pullRequest) into an array, assoc (array) = true
        $upsourceResponseArray = json_decode($upsourceResponseBody, true);

        // return upsourceBranchName
        return $upsourceResponseArray['result']['branches'][0];
    }

    /**
     * @param string $upsourceProjectId
     * @param string $upsourceBranchName
     * @return string|void
     */
    private function getUpsourceReviewId(string $upsourceProjectId, string $upsourceBranchName): ?string
    {
        // Upsource uses RPC (remote Procedural API) and expects all requests to be POST
        $guzzleResponse = $this->httpClient->post('getBranchInfo',
            [
                'base_uri' => self::UPSOURCE_PROJECT_BASE_URL,
                'auth' => $this->getAuth(),
                'json' => [
                    "projectId" => $upsourceProjectId,
                    'branch' => $upsourceBranchName,
                ],
            ]
        );

        // Getting contents of body from guzzleResponse (Upsource Response)
        $upsourceResponseBody = $guzzleResponse->getBody()->getContents();

        // decode body of guzzle response (pullRequest) into an array, assoc (array) = true
        $upsourceResponseArray = json_decode($upsourceResponseBody, true);

        // Extract upsourceReviewId and return
        if(isset($upsourceResponseArray['result']['reviewInfo']['reviewId']['reviewId'])) {
            return $upsourceResponseArray['result']['reviewInfo']['reviewId']['reviewId'];
        } else {
            return null;
        }
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
