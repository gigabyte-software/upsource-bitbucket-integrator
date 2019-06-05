<?php

namespace BitBucket;

use Services\BitbucketService;

/**
 * PullRequestRepository
 * @author    Gigabyte Software Limited
 * @copyright Gigabyte Software Limited
 */
class PullRequestRepository
{
    /**
     * @var BitbucketService
     */
    private $bitbucketService;

    /**
     * PullRequestRepository constructor.
     * @param BitbucketService $bitbucketService
     */
    public function __construct(BitbucketService $bitbucketService)
    {
        $this->bitbucketService = $bitbucketService;
    }
}
