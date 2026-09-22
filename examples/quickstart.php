<?php

require __DIR__ . '/../vendor/autoload.php';

use DashaMail\DashaMail;
use DashaMail\Exception\ApiException;
use DashaMail\Exception\RateLimitException;

$dashamail = new DashaMail(getenv('DASHAMAIL_API_KEY') ?: 'YOUR_API_KEY');

try {
    // Account balance.
    $balance = $dashamail->account->balance();
    echo "Balance: {$balance['balance']} {$balance['currency']}\n";

    // Create a list and add a subscriber.
    $list = $dashamail->lists->create('Example list');
    $listId = $list['list_id'];

    $dashamail->lists->addMember($listId, 'subscriber@example.com', [
        'merge_1' => 'Иван',
    ]);

    // Iterate subscribers, page by page.
    $start = 0;
    do {
        $page = $dashamail->lists->members($listId, ['start' => $start, 'limit' => 100]);
        foreach ($page as $member) {
            echo $member['email'] . "\n";
        }
        $start += $page->getLimit();
    } while ($page->hasMore());

    // Send a transactional email.
    $dashamail->transactional->send(
        'subscriber@example.com',
        'sender@yourdomain.com',
        '<p>Спасибо за подписку!</p>',
        ['subject' => 'Добро пожаловать']
    );
} catch (RateLimitException $e) {
    echo "Rate limited, retry after {$e->getRetryAfter()}s\n";
} catch (ApiException $e) {
    echo "DashaMail API error {$e->getApiCode()}: {$e->getMessage()}\n";
}
