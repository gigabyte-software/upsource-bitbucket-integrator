<?php

namespace Controllers;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Services\BitbucketService;
use Services\UpsourceService;

class HookController
{
    /** @var UpsourceService */
    private $upsourceService;

    /** @var BitbucketService */
    private $bitbucketService;

    /**
     * HookController constructor.
     * @param UpsourceService  $upsourceService
     * @param BitbucketService $bitbucketService
     */
    public function __construct(UpsourceService $upsourceService, BitbucketService $bitbucketService)
    {
        $this->upsourceService = $upsourceService;
        $this->bitbucketService = $bitbucketService;
    }

    /**
     * Take webhook POST request from Bitbucket and make a POST request to createReview in an UpsourceService
     * and return URL to Bitbucket
     * @param Request $request
     * @return Response
     */
    public function createUpsourceReview(Request $request) : Response
    {
        // Get contents of body from Bitbucket POST request (webhook) and decode
        $requestBody = $request->getBody()->getContents();
        $bitbucketPullRequest = json_decode($requestBody, true);

        // Extract pullRequestId and branch name (upsource-api-integration) from Bitbucket webhook
        $bitbucketPullRequestId = $bitbucketPullRequest['pullrequest']['id'];
        $bitbucketBranchName = $bitbucketPullRequest['pullrequest']['source']['branch']['name'];

        // Get repository name from Bitbucket and convert to upsourceProjectId
        $bitbucketRepositoryName = $bitbucketPullRequest['pullrequest']['source']['repository']['name'];

        /** @var UpsourceService $upsourceService */
        // Create Upsource Review and pass in branchName amd repositoryName from Bitbucket webhook
        $upsourceReviewUrl = $this->upsourceService->createUpsourceReview($bitbucketRepositoryName,
            $bitbucketBranchName);

        // Update Bitbucket description with upsource url (pass in bitbucket's pullRequestId - also need title
        // and current description to append but these are retrieved in BitbucketService)
        $this->bitbucketService->changePullRequestDescription($bitbucketPullRequestId, $upsourceReviewUrl);

        // Return response once all logic in app is completed, upsource review has been created and retrieved link
        return new \Slim\Http\Response();
    }
}
