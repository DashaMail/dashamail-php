<?php

namespace DashaMail\Tests;

use DashaMail\Client;

/**
 * A Client that never touches the network: execute() is stubbed to return a
 * canned HTTP response queued up front, so resource classes can be tested
 * against realistic API payloads.
 */
class FakeClient extends Client
{
    /** @var array<int, array{status:int, body:string, headers:array}> */
    private $queue = [];

    /** @var array<int, array{method:string, url:string, headers:array, body:mixed}> */
    public $calls = [];

    public function __construct($apiKey = 'test-key', array $options = [])
    {
        parent::__construct($apiKey, $options);
    }

    public function queueResponse($status, $decodedBodyOrRaw, array $headers = [])
    {
        $body = is_string($decodedBodyOrRaw) ? $decodedBodyOrRaw : json_encode($decodedBodyOrRaw, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $this->queue[] = ['status' => $status, 'body' => $body, 'headers' => $headers];
        return $this;
    }

    protected function execute($method, $url, array $headers, $body)
    {
        $this->calls[] = ['method' => $method, 'url' => $url, 'headers' => $headers, 'body' => $body];

        if (!$this->queue) {
            throw new \RuntimeException('FakeClient: no queued response for ' . $method . ' ' . $url);
        }

        return array_shift($this->queue);
    }
}
