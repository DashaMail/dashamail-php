<?php

namespace DashaMail\Tests;

use DashaMail\Resource\Campaigns;
use DashaMail\Resource\Images;
use DashaMail\Resource\Lists;
use DashaMail\Resource\Reports;
use DashaMail\Resource\Transactional;
use DashaMail\Resource\Workflows;
use PHPUnit\Framework\TestCase;

class ResourceTest extends TestCase
{
    private function ok($data = [], array $meta = [])
    {
        $body = ['response' => ['msg' => ['err_code' => 0, 'text' => 'OK', 'type' => 'message'], 'data' => $data]];
        if ($meta) {
            $body['meta'] = $meta;
        }
        return $body;
    }

    public function testListsCreateSendsNameInBody()
    {
        $client = new FakeClient();
        $client->queueResponse(201, $this->ok(['list_id' => 42]));
        $lists = new Lists($client);

        $result = $lists->create('Клиенты', ['company' => 'ООО Ромашка']);

        $this->assertSame(42, $result['list_id']);
        $call = $client->calls[0];
        $this->assertSame('POST', $call['method']);
        $this->assertStringEndsWith('/lists', $call['url']);
        $decoded = json_decode($call['body'], true);
        $this->assertSame('Клиенты', $decoded['name']);
        $this->assertSame('ООО Ромашка', $decoded['company']);
    }

    public function testListsGetMemberEncodesEmailInPath()
    {
        $client = new FakeClient();
        $client->queueResponse(200, $this->ok(['email' => 'a+b@example.com']));
        $lists = new Lists($client);

        $lists->getMember(1, 'a+b@example.com');

        $this->assertStringContainsString(rawurlencode('a+b@example.com'), $client->calls[0]['url']);
    }

    public function testListsMoveMemberSendsRequiredFields()
    {
        $client = new FakeClient();
        $client->queueResponse(200, $this->ok());
        $lists = new Lists($client);

        $lists->moveMember(1, 'a@example.com', 2, 555);

        $decoded = json_decode($client->calls[0]['body'], true);
        $this->assertSame(2, $decoded['to_list_id']);
        $this->assertSame(555, $decoded['member_id']);
    }

    public function testListsFindMemberHitsAccountWideEndpoint()
    {
        $client = new FakeClient();
        $client->queueResponse(200, $this->ok([['list_id' => 1, 'email' => 'a@example.com']]));
        $lists = new Lists($client);

        $lists->findMember('a@example.com');

        $this->assertStringContainsString('/members?', $client->calls[0]['url']);
        $this->assertStringContainsString('email=a%40example.com', $client->calls[0]['url']);
    }

    public function testCampaignsScheduleSendsDeliveryTime()
    {
        $client = new FakeClient();
        $client->queueResponse(200, $this->ok());
        $campaigns = new Campaigns($client);

        $campaigns->schedule(1, '2026-01-01 10:00:00');

        $decoded = json_decode($client->calls[0]['body'], true);
        $this->assertSame('2026-01-01 10:00:00', $decoded['delivery_time']);
        $this->assertStringEndsWith('/campaigns/1/schedule', $client->calls[0]['url']);
    }

    public function testCampaignsSendHitsSendEndpoint()
    {
        $client = new FakeClient();
        $client->queueResponse(200, $this->ok());
        $campaigns = new Campaigns($client);

        $campaigns->send(1);

        $this->assertStringEndsWith('/campaigns/1/send', $client->calls[0]['url']);
        $this->assertSame('POST', $client->calls[0]['method']);
    }

    public function testCampaignsAbWinnerSendsRequiredFields()
    {
        $client = new FakeClient();
        $client->queueResponse(200, $this->ok());
        $campaigns = new Campaigns($client);

        $campaigns->abWinner(1, 2, '2026-01-01 10:00:00');

        $decoded = json_decode($client->calls[0]['body'], true);
        $this->assertSame(2, $decoded['variant_id']);
        $this->assertSame('2026-01-01 10:00:00', $decoded['delivery_time']);
    }

    public function testReportsAbHitsVariantsEndpoint()
    {
        $client = new FakeClient();
        $client->queueResponse(200, $this->ok(['variants' => []]));
        $reports = new Reports($client);

        $reports->ab(1);

        $this->assertStringEndsWith('/reports/1/variants', $client->calls[0]['url']);
    }

    public function testWorkflowsCopySendsParams()
    {
        $client = new FakeClient();
        $client->queueResponse(200, $this->ok(['workflow_id' => 2]));
        $workflows = new Workflows($client);

        $workflows->copy(1, ['format' => 'json']);

        $decoded = json_decode($client->calls[0]['body'], true);
        $this->assertSame('json', $decoded['format']);
        $this->assertStringEndsWith('/workflows/1/copy', $client->calls[0]['url']);
    }

    public function testTransactionalSendBuildsBody()
    {
        $client = new FakeClient();
        $client->queueResponse(201, $this->ok(['transaction_id' => 'abc']));
        $transactional = new Transactional($client);

        $result = $transactional->send('to@example.com', 'from@yourdomain.com', '<p>Hi</p>', ['subject' => 'Hello']);

        $this->assertSame('abc', $result['transaction_id']);
        $decoded = json_decode($client->calls[0]['body'], true);
        $this->assertSame('to@example.com', $decoded['to']);
        $this->assertSame('from@yourdomain.com', $decoded['from_email']);
        $this->assertSame('<p>Hi</p>', $decoded['message']);
        $this->assertSame('Hello', $decoded['subject']);
    }

    public function testImagesOptimizeBase64EncodesBinaryData()
    {
        $client = new FakeClient();
        $client->queueResponse(200, $this->ok(['image' => base64_encode('binary'), 'saved_bytes' => 10]));
        $images = new Images($client);

        $images->optimize('raw-bytes', ['max_width' => 800]);

        $decoded = json_decode($client->calls[0]['body'], true);
        $this->assertSame(base64_encode('raw-bytes'), $decoded['image']);
        $this->assertSame(800, $decoded['max_width']);
    }

    public function testPaginatedMembersExposesHasMore()
    {
        $client = new FakeClient();
        $client->queueResponse(200, $this->ok([['email' => 'a@example.com']], ['has_more' => true, 'limit' => 100]));
        $lists = new Lists($client);

        $result = $lists->members(1);

        $this->assertTrue($result->hasMore());
        $this->assertSame(100, $result->getLimit());
    }
}
