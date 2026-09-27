<?php

namespace DashaMail\Resource;

/**
 * One-off transactional emails: send, status, log, stats.
 * Requires a verified sending domain — see Account::addDomain().
 * https://dashamail.ru/api/transactional/
 */
class Transactional extends AbstractResource
{
    /**
     * POST /transactional/messages
     * @param string|array $to      A single address, or an array of addresses/objects
     * @param string       $fromEmail
     * @param string       $message HTML body
     * @param array        $params  from_name, subject, plain_text, message_id, cc, bcc, headers,
     *                              attachments, inline, delivery_time, domain, stat_domain...
     */
    public function send($to, $fromEmail, $message, array $params = [])
    {
        $params['to'] = $to;
        $params['from_email'] = $fromEmail;
        $params['message'] = $message;
        return $this->client->request('POST', '/transactional/messages', [], $params);
    }

    /** GET /transactional/messages/{transaction_id} — delivery status of one message. */
    public function check($transactionId)
    {
        return $this->client->request('GET', '/transactional/messages/' . rawurlencode($transactionId));
    }

    /** GET /transactional/log */
    public function log(array $params = [])
    {
        return $this->client->request('GET', '/transactional/log', $params);
    }

    /** GET /transactional/stats */
    public function stats(array $params = [])
    {
        return $this->client->request('GET', '/transactional/stats', $params);
    }
}
