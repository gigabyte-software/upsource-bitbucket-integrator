<?php

//$login = "chris@gigabyte.software";
//$password = "fr%XUtC7Balloon";
//
//$auth = "chris@gigabyte.software:fr%XUtC7Balloon";
//
//echo 'login:- ' . base64_encode($login);
//echo '<br>';
//echo 'password - ' . base64_encode($password);
//echo '<br>';
//echo 'login: password - ' . base64_encode($auth);


// I actually want to map the repo name from Bitbucket to many projectId's in Upsource - how to know which one to map
// to? Don't think I can get this info from the Bitbucket hook...??
// Try the other way first
//function mapRepositoryNameToProjectId($projectId)
//{
//    if ($projectId === "unicorn1" || "unicorn2" || "unicorn3")
//    {
//        return "unicorn";
//    }
//}
//
//$a = "unicorn1";
//echo mapRepositoryNameToProjectId($a);

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

echo $repositoryMap['frontend'];
//$upsourceProjectName = $repoMap[$bitBucketRepoName];



//print_r(array_map())
