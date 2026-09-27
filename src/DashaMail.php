<?php

namespace DashaMail;

use DashaMail\Resource\Account;
use DashaMail\Resource\Automations;
use DashaMail\Resource\Campaigns;
use DashaMail\Resource\Dialogs;
use DashaMail\Resource\Images;
use DashaMail\Resource\Lists;
use DashaMail\Resource\Reports;
use DashaMail\Resource\Router;
use DashaMail\Resource\Segments;
use DashaMail\Resource\Templates;
use DashaMail\Resource\Transactional;
use DashaMail\Resource\Workflows;

/**
 * Entry point of the DashaMail PHP SDK.
 *
 *   $dashamail = new DashaMail('YOUR_API_KEY');
 *   $lists = $dashamail->lists->all();
 *   $dashamail->transactional->send('user@example.com', 'sender@yourdomain.com', '<p>Hi!</p>');
 *
 * @property-read Lists         $lists
 * @property-read Segments      $segments
 * @property-read Campaigns     $campaigns
 * @property-read Automations   $automations
 * @property-read Workflows     $workflows
 * @property-read Templates     $templates
 * @property-read Reports       $reports
 * @property-read Transactional $transactional
 * @property-read Account       $account
 * @property-read Dialogs       $dialogs
 * @property-read Router        $router
 * @property-read Images        $images
 */
class DashaMail
{
    /** @var Client */
    private $client;

    public $lists;
    public $segments;
    public $campaigns;
    public $automations;
    public $workflows;
    public $templates;
    public $reports;
    public $transactional;
    public $account;
    public $dialogs;
    public $router;
    public $images;

    /**
     * @param string $apiKey Account API key — Личный кабинет → Аккаунт → API и интеграции.
     * @param array  $options See Client::__construct() for base_url/timeout/user_agent.
     */
    public function __construct($apiKey, array $options = [])
    {
        $this->client = new Client($apiKey, $options);

        $this->lists = new Lists($this->client);
        $this->segments = new Segments($this->client);
        $this->campaigns = new Campaigns($this->client);
        $this->automations = new Automations($this->client);
        $this->workflows = new Workflows($this->client);
        $this->templates = new Templates($this->client);
        $this->reports = new Reports($this->client);
        $this->transactional = new Transactional($this->client);
        $this->account = new Account($this->client);
        $this->dialogs = new Dialogs($this->client);
        $this->router = new Router($this->client);
        $this->images = new Images($this->client);
    }

    /** The underlying HTTP client, for calling an endpoint the SDK doesn't wrap yet. */
    public function getClient()
    {
        return $this->client;
    }
}
