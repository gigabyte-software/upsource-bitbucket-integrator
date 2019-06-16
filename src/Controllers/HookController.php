<?php

namespace Controllers;

use BitBucket\PullRequest;
use Monolog\Logger;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Services\BitbucketService;
use Services\UpsourceService;

class HookController
{
    /**
     * @var UpsourceService
     */
    private $upsourceService;

    /**
     * @var BitbucketService
     */
    private $bitbucketService;
    /**
     * @var Logger
     */
    private $logger;

    /**
     * HookController constructor.
     * @param UpsourceService  $upsourceService
     * @param BitbucketService $bitbucketService
     * @param Logger           $logger
     */
    public function __construct(
        UpsourceService $upsourceService,
        BitbucketService $bitbucketService,
        Logger $logger
    ) {
        $this->upsourceService = $upsourceService;
        $this->bitbucketService = $bitbucketService;
        $this->logger = $logger;
    }

    /**
     * Take webhook POST request from Bitbucket and make a POST request to createReview in an UpsourceService
     * and return URL to Bitbucket
     * @param Request $request
     * @return Response
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function createUpsourceReviewWithModel(Request $request): Response
    {
        $json = $request->getBody()->getContents();
        $pullRequest = PullRequest::createFromJson($json);

        // Log debug data from Bitbucket PR Webhook and PR entity (debug allows compatibility with common interfaces)
        $this->logger->addDebug("HookController:createUpsourceReviewWithModel - Webhook retrieved... {$json}");
        $this->logger->debug(
            "PullRequest:serialiseToJson - pullRequest Entity in json... 
            {$pullRequest->serialiseToJson()}"
        );

        /** @var UpsourceService $upsourceService */
        $upsourceReviewUrl = $this->upsourceService->createReview($pullRequest);
        $this->logger->debug("UpsourceService:createReview upsourceReviewUrl... {$upsourceReviewUrl}");

        // Update Bitbucket description with upsource url (pass in bitbucket's full repo name, pullRequestId - also
        // need title and current description to append but these are retrieved in BitbucketService)
        $this->bitbucketService->changePullRequestDescription(
            $pullRequest,
            $upsourceReviewUrl
        );

        // Return response once all logic in app is completed, upsource review has been created and retrieved link
        return new \Slim\Http\Response();
    }

    /**
     * Close upsource branch when merged in Bitbucket - is this necessary?
     * @param Request $request
     * @return Response
     */
    public function closeUpsourceReview(Request $request): Response
    {
        // Get contents of body from Bitbucket POST request (webhook) and decode
        $requestBody = $request->getBody()->getContents();
        $bitbucketPullRequest = json_decode($requestBody, true);

        // Extract repo name and branch name (upsource-api-integration) from Bitbucket webhook
        $bitbucketRepositoryName = $bitbucketPullRequest['pullrequest']['source']['repository']['name'];
        $bitbucketBranchName = $bitbucketPullRequest['pullrequest']['source']['branch']['name'];

        $this->upsourceService->getReviews($bitbucketRepositoryName); //todo - just testing getReviews

//        $this->upsourceService->closeReview($bitbucketRepositoryName, $bitbucketBranchName);

        // Return response once all logic in app is completed, upsource review has been created and retrieved link
        return new \Slim\Http\Response();
    }
}
