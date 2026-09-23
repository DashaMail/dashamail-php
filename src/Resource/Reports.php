<?php

namespace DashaMail\Resource;

/**
 * Campaign statistics: sent/delivered/opened/clicked/bounced, click and bounce
 * breakdowns, geography, mail clients, event feed, A/B test results.
 * https://dashamail.ru/api/reports/
 */
class Reports extends AbstractResource
{
    /** GET /reports/{campaign_id}/summary */
    public function summary($campaignId, array $params = [])
    {
        return $this->client->request('GET', "/reports/{$campaignId}/summary", $params);
    }

    /** GET /reports/{campaign_id}/timeline — metric values bucketed over time. */
    public function timeline($campaignId, array $params = [])
    {
        return $this->client->request('GET', "/reports/{$campaignId}/timeline", $params);
    }

    /** GET /reports/{campaign_id}/variants — A/B test results. */
    public function ab($campaignId)
    {
        return $this->client->request('GET', "/reports/{$campaignId}/variants");
    }

    /** POST /reports/compare — compare metrics across periods/campaigns/lists. */
    public function compare(array $periods, array $params = [])
    {
        $params['periods'] = $periods;
        return $this->client->request('POST', '/reports/compare', [], $params);
    }

    /**
     * GET /reports/{campaign_id}/{metric} — recipient list for one event.
     * @param string $metric One of: sent, delivered, opened, clicked, bounced, complained, unsubscribed
     */
    public function metric($campaignId, $metric, array $params = [])
    {
        return $this->client->request('GET', "/reports/{$campaignId}/{$metric}", $params);
    }

    /** GET /reports/{campaign_id}/sent */
    public function sent($campaignId, array $params = [])
    {
        return $this->metric($campaignId, 'sent', $params);
    }

    /** GET /reports/{campaign_id}/delivered */
    public function delivered($campaignId, array $params = [])
    {
        return $this->metric($campaignId, 'delivered', $params);
    }

    /** GET /reports/{campaign_id}/opened */
    public function opened($campaignId, array $params = [])
    {
        return $this->metric($campaignId, 'opened', $params);
    }

    /** GET /reports/{campaign_id}/clicked */
    public function clicked($campaignId, array $params = [])
    {
        return $this->metric($campaignId, 'clicked', $params);
    }

    /** GET /reports/{campaign_id}/bounced */
    public function bounced($campaignId, array $params = [])
    {
        return $this->metric($campaignId, 'bounced', $params);
    }

    /** GET /reports/{campaign_id}/complained */
    public function complained($campaignId, array $params = [])
    {
        return $this->metric($campaignId, 'complained', $params);
    }

    /** GET /reports/{campaign_id}/unsubscribed */
    public function unsubscribed($campaignId, array $params = [])
    {
        return $this->metric($campaignId, 'unsubscribed', $params);
    }

    /** GET /reports/{campaign_id}/events — full event feed with filters. */
    public function events($campaignId, array $params = [])
    {
        return $this->client->request('GET', "/reports/{$campaignId}/events", $params);
    }

    /** GET /reports/{campaign_id}/clickstat — clicks broken down by link. */
    public function clickstat($campaignId)
    {
        return $this->client->request('GET', "/reports/{$campaignId}/clickstat");
    }

    /** GET /reports/{campaign_id}/userclicks — who clicked a specific link. */
    public function userclicks($campaignId, $url)
    {
        return $this->client->request('GET', "/reports/{$campaignId}/userclicks", ['url' => $url]);
    }

    /** GET /reports/{campaign_id}/bouncestat — bounces broken down by SMTP code. */
    public function bouncestat($campaignId)
    {
        return $this->client->request('GET', "/reports/{$campaignId}/bouncestat");
    }

    /** GET /reports/{campaign_id}/domains — metrics broken down by recipient mail domain. */
    public function domains($campaignId, array $params = [])
    {
        return $this->client->request('GET', "/reports/{$campaignId}/domains", $params);
    }

    /** GET /reports/{campaign_id}/geo — geography of opens. */
    public function geo($campaignId)
    {
        return $this->client->request('GET', "/reports/{$campaignId}/geo");
    }

    /** GET /reports/{campaign_id}/clients — mail clients and devices. */
    public function clients($campaignId)
    {
        return $this->client->request('GET', "/reports/{$campaignId}/clients");
    }

    /** GET /reports/{campaign_id}/codes — confirmation codes. */
    public function codes($campaignId, array $params = [])
    {
        return $this->client->request('GET', "/reports/{$campaignId}/codes", $params);
    }
}
