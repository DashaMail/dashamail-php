<?php

namespace DashaMail\Resource;

/**
 * Saved markup: legacy HTML-template store (/templates) plus saved campaigns
 * in TEMPLATE status (/templates/saved), which is where the account UI keeps them.
 * https://dashamail.ru/api/templates/
 */
class Templates extends AbstractResource
{
    /** GET /templates — legacy HTML templates. */
    public function all(array $params = [])
    {
        return $this->client->request('GET', '/templates', $params);
    }

    /** GET /templates/{id} */
    public function get($id)
    {
        return $this->client->request('GET', "/templates/{$id}");
    }

    /**
     * POST /templates
     * @param string $name
     * @param string $template HTML markup
     * @param string $body     Plain-text markup
     */
    public function create($name, $template, $body, array $params = [])
    {
        $params['name'] = $name;
        $params['template'] = $template;
        $params['body'] = $body;
        return $this->client->request('POST', '/templates', [], $params);
    }

    /** PUT /templates/{id} */
    public function update($id, array $params = [])
    {
        return $this->client->request('PUT', "/templates/{$id}", [], $params);
    }

    /** DELETE /templates/{id} */
    public function delete($id)
    {
        return $this->client->request('DELETE', "/templates/{$id}");
    }

    /** GET /templates/saved — campaigns saved as reusable templates (status TEMPLATE). */
    public function saved(array $params = [])
    {
        return $this->client->request('GET', '/templates/saved', $params);
    }
}
