<?php

namespace DashaMail\Resource;

/**
 * Bulk campaigns: draft, build, launch, pause, attachments, folders.
 * https://dashamail.ru/api/campaigns/
 */
class Campaigns extends AbstractResource
{
    /** GET /campaigns */
    public function all(array $params = [])
    {
        return $this->client->request('GET', '/campaigns', $params);
    }

    /** GET /campaigns/{campaign_id} */
    public function get($campaignId, array $params = [])
    {
        return $this->client->request('GET', "/campaigns/{$campaignId}", $params);
    }

    /**
     * POST /campaigns — create a draft campaign.
     * @param array $params list_id*, subject*, from_email*, from_name*, plus html, plain_text,
     *                      amp, track_opens, track_clicks, esegment, status, and the rest — see docs.
     */
    public function create(array $params)
    {
        return $this->client->request('POST', '/campaigns', [], $params);
    }

    /**
     * PUT /campaigns/{campaign_id} — update a draft, or launch it by setting
     * status to SCHEDULE/MODERATING and (for SCHEDULE) delivery_time.
     */
    public function update($campaignId, array $params = [])
    {
        return $this->client->request('PUT', "/campaigns/{$campaignId}", [], $params);
    }

    /** DELETE /campaigns/{campaign_id} */
    public function delete($campaignId)
    {
        return $this->client->request('DELETE', "/campaigns/{$campaignId}");
    }

    /** POST /campaigns/{campaign_id}/copy */
    public function copy($campaignId, array $params = [])
    {
        return $this->client->request('POST', "/campaigns/{$campaignId}/copy", [], $params);
    }

    /** POST /campaigns/{campaign_id}/pause */
    public function pause($campaignId)
    {
        return $this->client->request('POST', "/campaigns/{$campaignId}/pause");
    }

    /** POST /campaigns/{campaign_id}/resume */
    public function resume($campaignId)
    {
        return $this->client->request('POST', "/campaigns/{$campaignId}/resume");
    }

    /** POST /campaigns/{campaign_id}/unschedule — pull a SCHEDULE campaign back to DRAFT. */
    public function unschedule($campaignId)
    {
        return $this->client->request('POST', "/campaigns/{$campaignId}/unschedule");
    }

    /** POST /campaigns/{campaign_id}/test — send a test copy to your own address. */
    public function test($campaignId, $email)
    {
        return $this->client->request('POST', "/campaigns/{$campaignId}/test", [], ['email' => $email]);
    }

    /** GET /campaigns/{campaign_id}/preview — browser preview link. */
    public function preview($campaignId)
    {
        return $this->client->request('GET', "/campaigns/{$campaignId}/preview");
    }

    /** GET /campaigns/{campaign_id}/estimate — how many emails would be sent. */
    public function estimate($campaignId)
    {
        return $this->client->request('GET', "/campaigns/{$campaignId}/estimate");
    }

    /** POST /campaigns/{campaign_id}/resend — resend to recipients who did not open. */
    public function resend($campaignId, array $params = [])
    {
        return $this->client->request('POST', "/campaigns/{$campaignId}/resend", [], $params);
    }

    /** GET /campaigns/{campaign_id}/attachments */
    public function getAttachments($campaignId)
    {
        return $this->client->request('GET', "/campaigns/{$campaignId}/attachments");
    }

    /** POST /campaigns/{campaign_id}/attachments — attach a file by URL. */
    public function addAttachment($campaignId, $url, array $params = [])
    {
        $params['url'] = $url;
        return $this->client->request('POST', "/campaigns/{$campaignId}/attachments", [], $params);
    }

    /** DELETE /campaigns/{campaign_id}/attachments/{id} */
    public function deleteAttachment($campaignId, $attachmentId)
    {
        return $this->client->request('DELETE', "/campaigns/{$campaignId}/attachments/{$attachmentId}");
    }

    /** GET /campaigns/folders */
    public function getFolders(array $params = [])
    {
        return $this->client->request('GET', '/campaigns/folders', $params);
    }

    /** POST /campaigns/{campaign_id}/move — move a campaign into a folder. */
    public function moveToFolder($campaignId, $folderId)
    {
        return $this->client->request('POST', "/campaigns/{$campaignId}/move", [], ['folder_id' => $folderId]);
    }
}
