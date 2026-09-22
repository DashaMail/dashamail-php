<?php

namespace DashaMail\Resource;

/**
 * Saved subscriber segments: condition sets, field/operator reference, size counting.
 * https://dashamail.ru/api/segments/
 */
class Segments extends AbstractResource
{
    /** GET /segments */
    public function all(array $params = [])
    {
        return $this->client->request('GET', '/segments', $params);
    }

    /** GET /segments/{segment_id} */
    public function get($segmentId)
    {
        return $this->client->request('GET', "/segments/{$segmentId}");
    }

    /**
     * POST /segments
     * @param int    $listId
     * @param string $name
     * @param array  $esegment Condition tree, e.g. ['match' => 'all', 'c' => [...]]
     */
    public function create($listId, $name, array $esegment, array $params = [])
    {
        $params['list_id'] = $listId;
        $params['name'] = $name;
        $params['esegment'] = $esegment;
        return $this->client->request('POST', '/segments', [], $params);
    }

    /** PUT /segments/{segment_id} */
    public function update($segmentId, array $params = [])
    {
        return $this->client->request('PUT', "/segments/{$segmentId}", [], $params);
    }

    /** DELETE /segments/{segment_id} */
    public function delete($segmentId)
    {
        return $this->client->request('DELETE', "/segments/{$segmentId}");
    }

    /**
     * POST /segments/count — recompute a segment's size, either by id or by
     * passing list_id + esegment directly without saving it.
     */
    public function count(array $params = [])
    {
        return $this->client->request('POST', '/segments/count', [], $params);
    }

    /** GET /segments/fields — which fields/operators are available for a list's segments. */
    public function fields($listId)
    {
        return $this->client->request('GET', '/segments/fields', ['list_id' => $listId]);
    }
}
