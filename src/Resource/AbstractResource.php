<?php

namespace DashaMail\Resource;

use DashaMail\Client;

abstract class AbstractResource
{
    /** @var Client */
    protected $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }
}
