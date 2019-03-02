<?php

namespace App\Clients;

use App\Config;
use Goutte\Client;


class LinkedInClient
{
    private $client;

    public function __construct()
    {
        $client = new Client();

        $client->request('GET', 'https://www.linkedin.com/uas/login');

        $client->submitForm('Sign in', [
            'session_key'      => Config::get('login'),
            'session_password' => Config::get('password'),
        ]);

        $this->client = $client;


    }

    public function get()
    {
        return $this->client;
    }
}