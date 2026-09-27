<?php

namespace DashaMail\Resource;

/**
 * Inbound mail processing: receiving domains, routing rules, stored
 * messages, webhook delivery log.
 * https://dashamail.ru/api/router/
 */
class Router extends AbstractResource
{
    // ── Domains ──────────────────────────────────────────────────────────

    /** GET /router/domains */
    public function domains()
    {
        return $this->client->request('GET', '/router/domains');
    }

    /** POST /router/domains — connect your own inbound domain (needs an MX record). */
    public function createDomain($domain)
    {
        return $this->client->request('POST', '/router/domains', [], ['domain' => $domain]);
    }

    /** POST /router/domains/{domain_id}/verify — check the MX record. */
    public function verifyDomain($domainId)
    {
        return $this->client->request('POST', "/router/domains/{$domainId}/verify");
    }

    /** DELETE /router/domains/{domain_id} */
    public function deleteDomain($domainId)
    {
        return $this->client->request('DELETE', "/router/domains/{$domainId}");
    }

    /** GET /router/domains/mx — DNS records to configure. */
    public function mxInstructions()
    {
        return $this->client->request('GET', '/router/domains/mx');
    }

    // ── Routes ───────────────────────────────────────────────────────────

    /** GET /router/routes */
    public function routes()
    {
        return $this->client->request('GET', '/router/routes');
    }

    /** GET /router/routes/{route_id} */
    public function getRoute($routeId)
    {
        return $this->client->request('GET', "/router/routes/{$routeId}");
    }

    /**
     * POST /router/routes
     * @param array $actions 1..5 action objects, each with a `type` key (webhook, store, forward, stop)
     * @param array $params  filter, expression, description, is_active, priority
     */
    public function createRoute(array $actions, array $params = [])
    {
        $params['actions'] = $actions;
        return $this->client->request('POST', '/router/routes', [], $params);
    }

    /** PUT /router/routes/{route_id} */
    public function updateRoute($routeId, array $params = [])
    {
        return $this->client->request('PUT', "/router/routes/{$routeId}", [], $params);
    }

    /** DELETE /router/routes/{route_id} */
    public function deleteRoute($routeId)
    {
        return $this->client->request('DELETE', "/router/routes/{$routeId}");
    }

    /** POST /router/routes/{route_id}/rekey — reissue the webhook signing key. */
    public function rekeyRoute($routeId)
    {
        return $this->client->request('POST', "/router/routes/{$routeId}/rekey");
    }

    /** POST /router/routes/reorder — $order is an array of route ids in the desired priority order. */
    public function reorderRoutes(array $order)
    {
        return $this->client->request('POST', '/router/routes/reorder', [], ['order' => $order]);
    }

    // ── Stored messages ──────────────────────────────────────────────────

    /** GET /router/messages */
    public function messages(array $params = [])
    {
        return $this->client->request('GET', '/router/messages', $params);
    }

    /** GET /router/messages/{message_id} */
    public function getMessage($messageId)
    {
        return $this->client->request('GET', "/router/messages/{$messageId}");
    }

    /** DELETE /router/messages/{message_id} */
    public function deleteMessage($messageId)
    {
        return $this->client->request('DELETE', "/router/messages/{$messageId}");
    }

    /** GET /router/messages/{message_id}/attachments/{attachment_id} */
    public function getMessageAttachment($messageId, $attachmentId)
    {
        return $this->client->request('GET', "/router/messages/{$messageId}/attachments/{$attachmentId}");
    }

    // ── Webhook delivery log ─────────────────────────────────────────────

    /** GET /router/deliveries */
    public function deliveries(array $params = [])
    {
        return $this->client->request('GET', '/router/deliveries', $params);
    }

    /** GET /router/deliveries/{delivery_id} */
    public function getDelivery($deliveryId)
    {
        return $this->client->request('GET', "/router/deliveries/{$deliveryId}");
    }

    // ── Settings ─────────────────────────────────────────────────────────

    /** GET /router/settings */
    public function settings()
    {
        return $this->client->request('GET', '/router/settings');
    }

    /** PUT /router/settings — pass_autoreply, pass_list_mail */
    public function updateSettings(array $params)
    {
        return $this->client->request('PUT', '/router/settings', [], $params);
    }
}
