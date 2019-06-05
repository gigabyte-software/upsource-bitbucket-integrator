<?php

namespace BitBucket;

use Assert\Assertion;
use Assert\Assert;

/**
 * PullRequest Entity
 * @author    Gigabyte Software Limited
 * @copyright Gigabyte Software Limited
 */
class PullRequest
{
    /** @var int */
    private $id;

    /**
     * fullRepositoryName includes Bitbucket username (e.g. gigabyte-software/review-creator)
     * @var string
     */
    private $fullRepositoryName;

    /**
     * repositoryName should match with upsource (e.g. review-creator)
     * @var string
     */
    private $repositoryName;

    /** @var string */
    private $branchName;

    /** @var string */
    private $title;

    /** @var string */
    private $description;

    /**
     * @param $id
     * @param $fullRepositoryName
     * @param $repositoryName
     * @param $branchName
     * @param $title
     * @param $description
     */
    public function __construct(
        int $id,
        string $fullRepositoryName,
        string $repositoryName,
        string $branchName,
        string $title,
        string $description
    ) {
        // Defensive validation to ensure that there are no spaces in these variables
        Assertion::integer($id);
        Assertion::notContains($fullRepositoryName, ' ');
        Assertion::notContains($repositoryName, ' ');
        Assertion::notContains($branchName, ' ');

        $this->id = $id;
        $this->fullRepositoryName = $fullRepositoryName;
        $this->repositoryName = $repositoryName;
        $this->branchName = $branchName;
        $this->title = $title;
        $this->description = $description;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getBranchName(): string
    {
        return $this->branchName;
    }

    /**
     * @return string
     */
    public function getFullRepositoryName(): string
    {
        return $this->fullRepositoryName;
    }

    /**
     * @return string
     */
    public function getRepositoryName(): string
    {
        return $this->repositoryName;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $jsonString
     * @return self
     */
    public static function createFromJson(string $jsonString): self
    {
        // json_decode the json string
        $pullRequestWebhook = json_decode($jsonString, true);

        // Set variables using json data from webhook
        $id = $pullRequestWebhook['pullrequest']['id'];
        $fullRepositoryName = $pullRequestWebhook['repository']['full_name'];
        $repositoryName = $pullRequestWebhook['pullrequest']['source']['repository']['name'];
        $branchName = $pullRequestWebhook['pullrequest']['source']['branch']['name'];
        $title = $pullRequestWebhook['pullrequest']['title'];
        $description = $pullRequestWebhook['pullrequest']['description'];

        // create new PullRequest object
        return new self($id, $fullRepositoryName, $repositoryName, $branchName, $title, $description);
    }

    /**
     * @param string $content
     * @return void
     */
    public function appendToDescription(string $content): void
    {
        // todo
    }
}
