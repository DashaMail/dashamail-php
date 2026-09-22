<?php

namespace DashaMail\Resource;

/**
 * Address lists (address books), their subscribers and merge fields.
 * https://dashamail.ru/api/lists/
 */
class Lists extends AbstractResource
{
    /** GET /lists — all address lists, newest first. */
    public function all(array $params = [])
    {
        return $this->client->request('GET', '/lists', $params);
    }

    /** GET /lists/{list_id} */
    public function get($listId, array $params = [])
    {
        return $this->client->request('GET', "/lists/{$listId}", $params);
    }

    /** POST /lists */
    public function create($name, array $params = [])
    {
        $params['name'] = $name;
        return $this->client->request('POST', '/lists', [], $params);
    }

    /** PUT /lists/{list_id} */
    public function update($listId, array $params = [])
    {
        return $this->client->request('PUT', "/lists/{$listId}", [], $params);
    }

    /** DELETE /lists/{list_id} */
    public function delete($listId)
    {
        return $this->client->request('DELETE', "/lists/{$listId}");
    }

    // ── Members ──────────────────────────────────────────────────────────

    /**
     * GET /lists/{list_id}/members
     * @param array $params start, limit, order, state, email, member_id, segment_id
     */
    public function members($listId, array $params = [])
    {
        return $this->client->request('GET', "/lists/{$listId}/members", $params);
    }

    /** GET /lists/{list_id}/members/{email} */
    public function getMember($listId, $email)
    {
        return $this->client->request('GET', "/lists/{$listId}/members/" . rawurlencode($email));
    }

    /** POST /lists/{list_id}/members — merge_1..merge_N and the rest go in $params. */
    public function addMember($listId, $email, array $params = [])
    {
        $params['email'] = $email;
        return $this->client->request('POST', "/lists/{$listId}/members", [], $params);
    }

    /**
     * POST /lists/{list_id}/members/batch
     * @param array $batch Array of member objects, each at least ['email' => ...]
     * @param array $params update, background, webhook, no_check, send_confirm
     */
    public function addMembersBatch($listId, array $batch, array $params = [])
    {
        $params['batch'] = $batch;
        return $this->client->request('POST', "/lists/{$listId}/members/batch", [], $params);
    }

    /**
     * POST /lists/{list_id}/members/import — import subscribers from a file.
     *
     * @param string $email Contact email for import status notifications
     * @param string $type  File type, e.g. "csv", "xls", "xlsx", "txt"
     * @param array  $params file (base64 or URL, per API docs), merge_1..merge_N, gender,
     *                       region, mode, update, send_confirm, sheet_index, sheet_name, webhook
     */
    public function importMembers($listId, $email, $type, array $params = [])
    {
        $params['email'] = $email;
        $params['type'] = $type;
        return $this->client->request('POST', "/lists/{$listId}/members/import", [], $params);
    }

    /** GET /lists/{list_id}/members/import — result of the last import job. */
    public function getImportResult($listId)
    {
        return $this->client->request('GET', "/lists/{$listId}/members/import");
    }

    /** GET /lists/{list_id}/import-history */
    public function getImportHistory($listId, array $params = [])
    {
        return $this->client->request('GET', "/lists/{$listId}/import-history", $params);
    }

    /** PUT /lists/{list_id}/members/{email} */
    public function updateMember($listId, $email, array $params = [])
    {
        return $this->client->request('PUT', "/lists/{$listId}/members/" . rawurlencode($email), [], $params);
    }

    /** DELETE /lists/{list_id}/members/{email} */
    public function deleteMember($listId, $email, $memberId)
    {
        return $this->client->request('DELETE', "/lists/{$listId}/members/" . rawurlencode($email), [], ['member_id' => $memberId]);
    }

    /** POST /lists/{list_id}/members/{email}/unsubscribe */
    public function unsubscribeMember($listId, $email, array $params = [])
    {
        return $this->client->request('POST', "/lists/{$listId}/members/" . rawurlencode($email) . '/unsubscribe', [], $params);
    }

    /** POST /lists/{list_id}/members/{email}/move — move a subscriber to another list. */
    public function moveMember($listId, $email, $toListId, $memberId)
    {
        return $this->client->request('POST', "/lists/{$listId}/members/" . rawurlencode($email) . '/move', [], [
            'to_list_id' => $toListId,
            'member_id' => $memberId,
        ]);
    }

    /** POST /lists/{list_id}/members/{email}/copy — copy a subscriber to another list. */
    public function copyMember($listId, $email, $toListId, $memberId)
    {
        return $this->client->request('POST', "/lists/{$listId}/members/" . rawurlencode($email) . '/copy', [], [
            'to_list_id' => $toListId,
            'member_id' => $memberId,
        ]);
    }

    /** GET /lists/{list_id}/members/{email}/activity */
    public function memberActivity($listId, $email, array $params = [])
    {
        return $this->client->request('GET', "/lists/{$listId}/members/" . rawurlencode($email) . '/activity', $params);
    }

    /** GET /lists/{list_id}/last-status — current subscription state of an address. */
    public function lastStatus($listId, $email)
    {
        return $this->client->request('GET', "/lists/{$listId}/last-status", ['email' => $email]);
    }

    /** GET /lists/{list_id}/check-email — validate an address before subscribing it. */
    public function checkEmail($listId, $email)
    {
        return $this->client->request('GET', "/lists/{$listId}/check-email", ['email' => $email]);
    }

    /** POST /lists/{list_id}/clean — purge bounced/complained/unsubscribed members. */
    public function clean($listId, array $params = [])
    {
        return $this->client->request('POST', "/lists/{$listId}/clean", [], $params);
    }

    /** GET /lists/{list_id}/unsubscribed */
    public function unsubscribed($listId, array $params = [])
    {
        return $this->client->request('GET', "/lists/{$listId}/unsubscribed", $params);
    }

    /** GET /lists/{list_id}/complaints */
    public function complaints($listId, array $params = [])
    {
        return $this->client->request('GET', "/lists/{$listId}/complaints", $params);
    }

    // ── Merge fields ─────────────────────────────────────────────────────

    /** POST /lists/{list_id}/fields — $type is one of the merge field types, e.g. "text", "choice". */
    public function addField($listId, $type, array $params = [])
    {
        $params['type'] = $type;
        return $this->client->request('POST', "/lists/{$listId}/fields", [], $params);
    }

    /** PUT /lists/{list_id}/fields/{merge_id} */
    public function updateField($listId, $mergeId, array $params = [])
    {
        $params['merge_id'] = $mergeId;
        return $this->client->request('PUT', "/lists/{$listId}/fields/{$mergeId}", [], $params);
    }

    /** DELETE /lists/{list_id}/fields/{merge_id} */
    public function deleteField($listId, $mergeId)
    {
        return $this->client->request('DELETE', "/lists/{$listId}/fields/{$mergeId}");
    }
}
