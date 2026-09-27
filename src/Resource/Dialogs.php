<?php

namespace DashaMail\Resource;

/**
 * Subscriber replies to campaigns, threaded per subscriber.
 * https://dashamail.ru/api/dialogs/
 */
class Dialogs extends AbstractResource
{
    /** GET /dialogs */
    public function all(array $params = [])
    {
        return $this->client->request('GET', '/dialogs', $params);
    }

    /** GET /dialogs/{dialog_id} */
    public function get($dialogId)
    {
        return $this->client->request('GET', "/dialogs/{$dialogId}");
    }

    /** GET /dialogs/{dialog_id}/messages */
    public function messages($dialogId, array $params = [])
    {
        return $this->client->request('GET', "/dialogs/{$dialogId}/messages", $params);
    }

    /** POST /dialogs/{dialog_id}/reply — reply as the campaign's sender. */
    public function reply($dialogId, $bodyText, array $params = [])
    {
        $params['body_text'] = $bodyText;
        return $this->client->request('POST', "/dialogs/{$dialogId}/reply", [], $params);
    }

    /** POST /dialogs/{dialog_id}/read */
    public function markRead($dialogId, array $params = [])
    {
        return $this->client->request('POST', "/dialogs/{$dialogId}/read", [], $params);
    }

    /** POST /dialogs/{dialog_id}/unread */
    public function markUnread($dialogId)
    {
        return $this->client->request('POST', "/dialogs/{$dialogId}/unread");
    }

    /** POST /dialogs/{dialog_id}/close */
    public function close($dialogId)
    {
        return $this->client->request('POST', "/dialogs/{$dialogId}/close");
    }

    /** POST /dialogs/{dialog_id}/open */
    public function open($dialogId)
    {
        return $this->client->request('POST', "/dialogs/{$dialogId}/open");
    }

    /** GET /dialogs/unread-count */
    public function unreadCount()
    {
        return $this->client->request('GET', '/dialogs/unread-count');
    }

    /** GET /dialogs/attachments/{attachment_id} — link to a reply's attachment. */
    public function attachment($attachmentId)
    {
        return $this->client->request('GET', "/dialogs/attachments/{$attachmentId}");
    }
}
