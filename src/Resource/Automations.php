<?php

namespace DashaMail\Resource;

/**
 * Event-triggered emails (subscribe, add, open, click, field change...).
 * https://dashamail.ru/api/automations/
 */
class Automations extends AbstractResource
{
    /** GET /automations */
    public function all(array $params = [])
    {
        return $this->client->request('GET', '/automations', $params);
    }

    /**
     * POST /automations
     * @param array $params list_id*, subject*, from_email*, from_name*, event*, plus action,
     *                      delay_1, delay_2, html, plain_text, esegment, track_opens, track_clicks...
     */
    public function create(array $params)
    {
        return $this->client->request('POST', '/automations', [], $params);
    }

    /** PUT /automations/{campaign_id} */
    public function update($campaignId, array $params = [])
    {
        return $this->client->request('PUT', "/automations/{$campaignId}", [], $params);
    }

    /** DELETE /automations/{campaign_id} */
    public function delete($campaignId)
    {
        return $this->client->request('DELETE', "/automations/{$campaignId}");
    }

    /** POST /automations/{campaign_id}/trigger — force-run for one subscriber (fails with code 37 before moderation). */
    public function trigger($campaignId, $email, array $params = [])
    {
        $params['email'] = $email;
        return $this->client->request('POST', "/automations/{$campaignId}/trigger", [], $params);
    }

    /** POST /automations/{campaign_id}/copy */
    public function copy($campaignId, array $params = [])
    {
        return $this->client->request('POST', "/automations/{$campaignId}/copy", [], $params);
    }
}
