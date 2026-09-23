<?php

namespace DashaMail\Resource;

/**
 * Visual-builder automation scenarios.
 * https://dashamail.ru/api/automations/#workflows
 */
class Workflows extends AbstractResource
{
    /** GET /workflows */
    public function all()
    {
        return $this->client->request('GET', '/workflows');
    }

    /** GET /workflows/{workflow_id} */
    public function get($workflowId)
    {
        return $this->client->request('GET', "/workflows/{$workflowId}");
    }

    /** DELETE /workflows/{workflow_id} */
    public function delete($workflowId)
    {
        return $this->client->request('DELETE', "/workflows/{$workflowId}");
    }

    /** POST /workflows/{workflow_id}/copy */
    public function copy($workflowId, array $params = [])
    {
        return $this->client->request('POST', "/workflows/{$workflowId}/copy", [], $params);
    }
}
