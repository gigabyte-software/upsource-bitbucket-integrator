<?php

namespace Services;

use GuzzleHttp\Client;

class UpsourceService
{

    // Define $httpClient as private so it is only available within this class
    private $httpClient;
    private $auth;

    // Use constructor so that a new (guzzle) client is always created when UpsourceService is instantiated
    public function __construct($username, $password)
    {
        // I need a guzzle client, which is an http client that I can make requests with (like Chrome)
        // Use $this to access $httpClient variable in this class and set it to new Client(); - (guzzle)
        $this->httpClient = new Client();

        $this->auth = [$username, $password];
    }

}
