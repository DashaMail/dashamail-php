# dashamail-php

Official PHP SDK for the [DashaMail REST API v2](https://dashamail.ru/api/) — address lists,
campaigns, automations, transactional email, reports, dialogs, inbound mail routing and
image optimization, from a single client with no dependencies beyond `ext-curl`.

## Requirements

- PHP 7.2 or newer
- `ext-curl`, `ext-json` (bundled with virtually every PHP install)

## Installation

```bash
composer require dashamail/dashamail-php
```

## Getting an API key

Личный кабинет → Аккаунт → API и интеграции. Treat it like a password: it acts on behalf
of the whole account.

## Quickstart

```php
use DashaMail\DashaMail;

$dashamail = new DashaMail('YOUR_API_KEY');

$balance = $dashamail->account->balance();
echo $balance['balance'];

$list = $dashamail->lists->create('Newsletter');
$dashamail->lists->addMember($list['list_id'], 'subscriber@example.com', [
    'merge_1' => 'Иван',
]);

$dashamail->transactional->send(
    'subscriber@example.com',
    'sender@yourdomain.com',
    '<p>Спасибо за подписку!</p>',
    ['subject' => 'Добро пожаловать']
);
```

See [`examples/quickstart.php`](examples/quickstart.php) for a fuller walkthrough, and
[dashamail.ru/api](https://dashamail.ru/api/) for the full parameter reference of every
endpoint — the SDK mirrors it method-for-method.

## Resources

`DashaMail` exposes one property per section of the API. Every method returns a
[`DashaMail\Response`](src/Response.php) (behaves like an array/list of the `data` payload)
or throws a `DashaMail\Exception\ApiException`.

| Property         | Class                              | Covers |
|------------------|-------------------------------------|--------|
| `->lists`        | `DashaMail\Resource\Lists`          | Address lists, subscribers, merge fields, imports |
| `->segments`      | `DashaMail\Resource\Segments`       | Saved subscriber segments |
| `->campaigns`     | `DashaMail\Resource\Campaigns`      | Bulk campaigns: draft, launch, pause, attachments, folders |
| `->automations`   | `DashaMail\Resource\Automations`    | Event-triggered emails |
| `->templates`     | `DashaMail\Resource\Templates`      | Saved HTML templates and templated campaigns |
| `->reports`       | `DashaMail\Resource\Reports`        | Campaign statistics, events, click/bounce/geo breakdowns |
| `->transactional` | `DashaMail\Resource\Transactional`  | One-off transactional email: send, status, log, stats |
| `->account`       | `DashaMail\Resource\Account`        | Balance, senders, sending domains, webhooks |
| `->dialogs`       | `DashaMail\Resource\Dialogs`        | Subscriber replies to campaigns |
| `->router`        | `DashaMail\Resource\Router`         | Inbound mail: domains, routing rules, stored messages |
| `->images`        | `DashaMail\Resource\Images`         | Resize/recompress an image before using it in a campaign |

## Working with responses

```php
$members = $dashamail->lists->members($listId, ['limit' => 100]);

foreach ($members as $member) {   // Response is Traversable
    echo $member['email'], "\n";
}

count($members);                  // number of rows in this page
$members->getData();              // the raw array, if you'd rather not use array access
$members->hasMore();              // true if there's another page (see Pagination below)
```

## Pagination

List endpoints (`Lists::members()`, `Lists::unsubscribed()`, `Transactional::log()`, ...)
don't return a total count — DashaMail tells you instead whether there's another page:

```php
$start = 0;
do {
    $page = $dashamail->lists->members($listId, ['start' => $start, 'limit' => 100]);
    foreach ($page as $member) {
        // ...
    }
    $start += $page->getLimit();
} while ($page->hasMore());
```

## Error handling

Every non-2xx response throws a subclass of `DashaMail\Exception\ApiException`, chosen by
HTTP status:

| Exception                 | HTTP status |
|----------------------------|------|
| `AuthenticationException`  | 401 |
| `PaymentRequiredException` | 402 |
| `AuthorizationException`   | 403 |
| `NotFoundException`        | 404 |
| `ConflictException`        | 409 |
| `PayloadTooLargeException` | 413 |
| `ValidationException`      | 422 |
| `RateLimitException`       | 429 |
| `ServerException`          | 5xx |

```php
use DashaMail\Exception\ApiException;
use DashaMail\Exception\RateLimitException;

try {
    $dashamail->campaigns->create([...]);
} catch (RateLimitException $e) {
    sleep($e->getRetryAfter() ?: 60);
} catch (ApiException $e) {
    // $e->getApiCode()    — DashaMail's own stable error code (see https://dashamail.ru/api/errors/)
    // $e->getHttpStatus() — the HTTP status of the response
    // $e->getDetails()    — any extra structured context the API attached
    error_log($e->getApiCode() . ': ' . $e->getMessage());
}
```

A request that never got an HTTP response at all (DNS, TLS, timeout...) throws
`DashaMail\Exception\NetworkException` instead.

## Images

`POST /images/optimize` is the one endpoint that isn't plain JSON in and out:

```php
// From a variable already in memory:
$result = $dashamail->images->optimize(file_get_contents('banner.png'), ['max_width' => 1600]);
file_put_contents('banner-optimized.png', base64_decode($result['image']));

// Straight from disk, without loading it into a PHP string first:
$result = $dashamail->images->optimizeFile('/path/to/banner.png');

// Same, but skip the base64 round-trip and get raw bytes back:
$binary = $dashamail->images->optimizeFileBinary('/path/to/banner.png');
$binary->saveTo('/path/to/banner-optimized.png');
```

## Advanced configuration

```php
$dashamail = new DashaMail('YOUR_API_KEY', [
    'timeout' => 60,                                  // seconds, default 30
    'base_url' => 'https://api.dashamail.com/v2',      // override for testing/proxying
    'user_agent' => 'my-app/1.0 (+dashamail-php)',
]);

// Escape hatch for an endpoint the SDK doesn't wrap yet:
$dashamail->getClient()->request('GET', '/some/new/endpoint', ['foo' => 'bar']);
```

## Testing

```bash
composer install
composer test
```

## License

MIT, see [LICENSE](LICENSE).
