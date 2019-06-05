<?php

namespace BitBucket;

/**
 * PullRequest Entity
 *
 * @author    Gigabyte Software Limited
 * @copyright Gigabyte Software Limited
 */
class PullRequest
{
    /*** @var int */
    private $id;

    /*** @var string */
    private $branchName;

    /*** @var string */
    private $fullRepositoryName;

    /*** @var string */
    private $repositoryName;

    /*** @var string */
    private $title;

    /*** @var string */
    private $description;

    /**
     * @param $id
     * @param $fullRepositoryName
     * @param $repositoryName
     * @param $branchName
     */
    public function __construct($id, $fullRepositoryName, $repositoryName, $branchName, $title, $description)
    {
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
     * @return PullRequest
     */
    public static function createFromJson(string $jsonString) : PullRequest
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

        // create new PullRequest object by doing new PullRequest(...vars required to construct the pull request...)
        return new self($id, $fullRepositoryName, $repositoryName, $branchName, $title, $description);
        // return the PullRequest object
    }

    /**
     * @param string $content
     * @return void
     */
    public function appendToDescription(string $content) : void
    {

    }
}
