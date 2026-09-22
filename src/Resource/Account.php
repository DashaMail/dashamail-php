<?php

namespace DashaMail\Resource;

/**
 * Account balance and limits, confirmed senders, sending domains, webhooks.
 * https://dashamail.ru/api/account/
 */
class Account extends AbstractResource
{
    /** GET /account/balance */
    public function balance()
    {
        return $this->client->request('GET', '/account/balance');
    }

    /** GET /account/senders — confirmed From: addresses. */
    public function senders()
    {
        return $this->client->request('GET', '/account/senders');
    }

    /** POST /account/senders/confirm — confirm a sender address with the code emailed to it. */
    public function confirmSender($email, $code)
    {
        return $this->client->request('POST', '/account/senders/confirm', [], ['email' => $email, 'code' => $code]);
    }

    /** GET /account/domains — sending domains. */
    public function domains(array $params = [])
    {
        return $this->client->request('GET', '/account/domains', $params);
    }

    /** POST /account/domains — add a sending domain. */
    public function addDomain($domain, array $params = [])
    {
        $params['domain'] = $domain;
        return $this->client->request('POST', '/account/domains', [], $params);
    }

    /** GET /account/domains/check — check DNS (DKIM/SPF) validity of sending domains. */
    public function checkDomains(array $params = [])
    {
        return $this->client->request('GET', '/account/domains/check', $params);
    }

    /** DELETE /account/domains/{domain} */
    public function deleteDomain($domain, array $params = [])
    {
        return $this->client->request('DELETE', '/account/domains/' . rawurlencode($domain), [], $params);
    }

    /** GET /account/webhooks — bulk-campaign webhooks. */
    public function webhooks(array $params = [])
    {
        return $this->client->request('GET', '/account/webhooks', $params);
    }

    /**
     * POST /account/webhooks
     * @param string $event One of: open, click, hard, spam, unsub, subscribe, confirm
     * @param string $url
     */
    public function addWebhook($event, $url, array $params = [])
    {
        $params['event'] = $event;
        $params['url'] = $url;
        return $this->client->request('POST', '/account/webhooks', [], $params);
    }

    /** DELETE /account/webhooks/{event_name} */
    public function deleteWebhook($eventName)
    {
        return $this->client->request('DELETE', '/account/webhooks/' . rawurlencode($eventName));
    }

    /** GET /account/webhooks/transactional */
    public function transactionalWebhooks(array $params = [])
    {
        return $this->client->request('GET', '/account/webhooks/transactional', $params);
    }

    /**
     * POST /account/webhooks/transactional
     * @param string $event One of: send, delivered, dropped, open, click, hard, spam, unsub
     * @param string $url
     */
    public function addTransactionalWebhook($event, $url, array $params = [])
    {
        $params['event'] = $event;
        $params['url'] = $url;
        return $this->client->request('POST', '/account/webhooks/transactional', [], $params);
    }

    /** DELETE /account/webhooks/transactional/{event_name} */
    public function deleteTransactionalWebhook($eventName)
    {
        return $this->client->request('DELETE', '/account/webhooks/transactional/' . rawurlencode($eventName));
    }
}
