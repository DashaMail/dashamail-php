<?php

namespace DashaMail\Tests;

use DashaMail\Exception\AuthenticationException;
use DashaMail\Exception\NotFoundException;
use DashaMail\Exception\RateLimitException;
use DashaMail\Exception\ValidationException;
use DashaMail\Response;
use PHPUnit\Framework\TestCase;

class ClientTest extends TestCase
{
    public function testSuccessfulRequestUnwrapsResponseData()
    {
        $client = new FakeClient();
        $client->queueResponse(200, [
            'response' => [
                'msg' => ['err_code' => 0, 'text' => 'OK', 'type' => 'message'],
                'data' => [['id' => 1, 'name' => 'Клиенты']],
            ],
        ]);

        $result = $client->request('GET', '/lists');

        $this->assertInstanceOf(Response::class, $result);
        $this->assertSame([['id' => 1, 'name' => 'Клиенты']], $result->getData());
        $this->assertSame('OK', $result->getMessage());
        $this->assertCount(1, $result);
        $this->assertSame(1, $result[0]['id']);
    }

    public function testPaginationMetaIsExposed()
    {
        $client = new FakeClient();
        $client->queueResponse(200, [
            'response' => ['msg' => ['err_code' => 0, 'text' => 'OK', 'type' => 'message'], 'data' => [1, 2, 3]],
            'meta' => ['has_more' => true, 'limit' => 3],
        ]);

        $result = $client->request('GET', '/lists/1/members');

        $this->assertTrue($result->hasMore());
        $this->assertSame(3, $result->getLimit());
    }

    public function test204NoContentReturnsNull()
    {
        $client = new FakeClient();
        $client->queueResponse(204, '');

        $result = $client->request('DELETE', '/lists/1');

        $this->assertNull($result);
    }

    public function testAuthorizationHeaderIsSent()
    {
        $client = new FakeClient('secret-key-123');
        $client->queueResponse(200, ['response' => ['msg' => ['err_code' => 0, 'text' => 'OK'], 'data' => []]]);

        $client->request('GET', '/lists');

        $this->assertStringContainsString('Authorization: Bearer secret-key-123', implode("\n", $client->calls[0]['headers']));
    }

    public function testJsonBodyIsEncodedAndContentTypeSet()
    {
        $client = new FakeClient();
        $client->queueResponse(201, ['response' => ['msg' => ['err_code' => 0, 'text' => 'OK'], 'data' => ['list_id' => 5]]]);

        $client->request('POST', '/lists', [], ['name' => 'Тест']);

        $this->assertStringContainsString('Content-Type: application/json', implode("\n", $client->calls[0]['headers']));
        $this->assertSame('{"name":"Тест"}', $client->calls[0]['body']);
    }

    /**
     * @dataProvider errorStatusProvider
     */
    public function testErrorStatusesMapToExceptionClasses($status, $expectedClass)
    {
        $client = new FakeClient();
        $client->queueResponse($status, ['error' => ['code' => 999, 'message' => 'boom', 'details' => []]]);

        $this->expectException($expectedClass);
        $client->request('GET', '/whatever');
    }

    public function errorStatusProvider()
    {
        return [
            [401, AuthenticationException::class],
            [404, NotFoundException::class],
            [422, ValidationException::class],
            [429, RateLimitException::class],
        ];
    }

    public function testRateLimitExposesRetryAfterFromDetails()
    {
        $client = new FakeClient();
        $client->queueResponse(429, [
            'error' => ['code' => 58, 'message' => 'Limit', 'details' => ['limit_per_minute' => 120, 'retry_after' => 60]],
        ]);

        try {
            $client->request('GET', '/lists');
            $this->fail('Expected RateLimitException');
        } catch (RateLimitException $e) {
            $this->assertSame(60, $e->getRetryAfter());
            $this->assertSame(58, $e->getApiCode());
        }
    }

    public function testQueryParamsAreAppendedToUrl()
    {
        $client = new FakeClient();
        $client->queueResponse(200, ['response' => ['msg' => ['err_code' => 0, 'text' => 'OK'], 'data' => []]]);

        $client->request('GET', '/lists', ['state' => 'active']);

        $this->assertStringContainsString('state=active', $client->calls[0]['url']);
    }
}
