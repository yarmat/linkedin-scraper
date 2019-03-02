<?php

namespace App\Clients;

use \Goutte\Client;

class SiteClient
{
    private $client;

    public function __construct()
    {
        $this->client = new Client();
    }

    public function get()
    {
        return $this->client;
    }

}