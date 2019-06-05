<?php

namespace Services;

use BitBucket\PullRequest;
use GuzzleHttp\Client;

class UpsourceService
{
    /** @var string */
    private const UPSOURCE_PROJECT_BASE_URL = "http://upsource.warwickestates.net:8080/~rpc/";

    /*** @var Client */
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
     * @param PullRequest $pullRequest
     * @return string
     */
    public function createUpsourceReview(PullRequest $pullRequest): string
    {
        $upsourceProjectId = $this->getUpsourceProjectId($pullRequest->getRepositoryName());

        // Extract upsourceBranchName (not always exactly the same as the bitbucketBranchName)
        $upsourceBranchName = $this->getUpsourceBranchName($upsourceProjectId, $pullRequest->getBranchName());

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
                        "branch" => $upsourceBranchName,
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
     * @param string $bitbucketRepositoryName
     * @param string $bitbucketBranchName
     * @return void
     */
    public function closeReview(string $bitbucketRepositoryName, string $bitbucketBranchName)
    {
        $upsourceProjectId = $this->getUpsourceProjectId($bitbucketRepositoryName);

        // Extract upsourceBranchName (not always exactly the same as the bitbucketBranchName)
        $upsourceBranchName = $this->getUpsourceBranchName($upsourceProjectId, $bitbucketBranchName);

        // Extract reviewId
        $upsourceReviewId = $this->getUpsourceReviewId($upsourceProjectId, $upsourceBranchName);

        // Creating POST request createReview and passing in upsourceProjectId (name) and bitbucketBranchName to Upsource
        $guzzleResponse = $this->httpClient->post('closeReview',
            [
                'base_uri' => self::UPSOURCE_PROJECT_BASE_URL,
                'auth' => $this->getAuth(),
                'json' => [
                    "reviewId" => [
                        "reviewId" => $upsourceReviewId,
                        "projectId" => $upsourceProjectId,
                    ],
                    "isFlagged" => true, // true will close review, false reopens a closed review
                ],
            ]
        );

        // Getting contents of body from guzzleResponse (Upsource Response)
        $upsourceResponseBody = $guzzleResponse->getBody()->getContents();
        // decode body of guzzle response (pullRequest) into an array, assoc (array) = true
        $upsourceResponseArray = json_decode($upsourceResponseBody, true);

        // Extract upsourceReviewId from createReview POST request to UpSource todo - still needed?
        $upsourceReviewId = $upsourceResponseArray['result']['reviewId']['reviewId'];
    }

    /**
     * Convert bitbucketRepositoryName to upsourceProjectId
     * @param string $bitbucketRepositoryName
     * @return string
     */
    private function getUpsourceProjectId(string $bitbucketRepositoryName): string
    {

        // Get all project (no argument required) - Upsource wants a GET this time!!
        $guzzleResponse = $this->httpClient->get('getAllProjects',
            [
                'base_uri' => self::UPSOURCE_PROJECT_BASE_URL,
                'auth' => $this->getAuth(),
                'json' => [],
            ]
        );

        // Getting contents of body from guzzleResponse (Upsource Response)
        $upsourceResponseBody = $guzzleResponse->getBody()->getContents();

        // decode body of guzzle response (pullRequest) into an array, assoc (array) = true
        $upsourceResponseArray = json_decode($upsourceResponseBody, true);

        // Initialise repository map
        $repositoryMap = [];

//        var_dump($upsourceResponseArray['result']['project'][1]);exit;

        // Loop through projects on upsource and get VCS links from bitbucket (or github)
        foreach ($upsourceResponseArray['result']['project'] as $project) {
            $projectId = $project['projectId'];

            // Get all projectVcsLinks using a POST request again (pass in projectId's)
            $guzzleResponse = $this->httpClient->post('getProjectVcsLinks',
                [
                    'base_uri' => self::UPSOURCE_PROJECT_BASE_URL,
                    'auth' => $this->getAuth(),
                    'json' => [
                        'projectId' => $projectId,
                    ],
                ]
            );
            // Getting contents of body from guzzleResponse (Upsource Response) and decode
            $upsourceResponseBody = $guzzleResponse->getBody()->getContents();
            $upsourceResponseArray = json_decode($upsourceResponseBody, true);

            // Loop through Bitbucket repositories and map to Upsource projectId's - sometimes doesn't work?...
//            foreach ($upsourceResponseArray['result']['repo'] as $repository) {
//                $repositoryMap[$repository['id']] = $projectId;
//            }
            foreach ($upsourceResponseArray['result']['repo'] as $repository) {
                // Extract Bitbucket repositoryName from VCS links url - note that url must be a git@ not http://
                preg_match_all("%/([a-z0-9_-]+)\.git$%i", $repository['url'][0], $match);
                $repositoryName = $match[1][0];
                $repositoryMap[$repositoryName] = $projectId;
            }
        }

        // Hard-coding review-creator for testing merges with Bitbucket todo - delete once working
        $repositoryMap['review-creator'] = 'review-creator';

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
     * @param string $bitbucketRepositoryName
     * @return void
     */
    public function getReviews(string $bitbucketRepositoryName)
    {

        $upsourceProjectId = $this->getUpsourceProjectId($bitbucketRepositoryName);

        // Upsource uses RPC (remote Procedural API) and expects all requests to be POST
        $guzzleResponse = $this->httpClient->post('getReviews',
            [
                'base_uri' => self::UPSOURCE_PROJECT_BASE_URL,
                'auth' => $this->getAuth(),
                'json' => [
                    "limit" => 1000,
                    "projectId" => $upsourceProjectId,
                    'query' => 'state: open'
                ],
            ]
        );

        // Getting contents of body from guzzleResponse (Upsource Response)
        $upsourceResponseBody = $guzzleResponse->getBody()->getContents();

        // decode body of guzzle response (pullRequest) into an array, assoc (array) = true
        $upsourceResponseArray = json_decode($upsourceResponseBody, true);

        var_dump($upsourceResponseArray);exit;
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
