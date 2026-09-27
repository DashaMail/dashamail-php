<?php

require __DIR__ . '/../vendor/autoload.php';

use DashaMail\DashaMail;
use DashaMail\Exception\ApiException;
use DashaMail\Exception\RateLimitException;

$dashamail = new DashaMail(getenv('DASHAMAIL_API_KEY') ?: 'YOUR_API_KEY');

try {
    // Баланс аккаунта.
    $balance = $dashamail->account->balance();
    echo "Баланс: {$balance['balance']} {$balance['currency']}\n";

    // Создаём базу и добавляем подписчика.
    $list = $dashamail->lists->create('Example list');
    $listId = $list['list_id'];

    $dashamail->lists->addMember($listId, 'subscriber@example.com', [
        'merge_1' => 'Иван',
    ]);

    // Перебираем подписчиков постранично.
    $start = 0;
    do {
        $page = $dashamail->lists->members($listId, ['start' => $start, 'limit' => 100]);
        foreach ($page as $member) {
            echo $member['email'] . "\n";
        }
        $start += $page->getLimit();
    } while ($page->hasMore());

    // Отправляем транзакционное письмо.
    $dashamail->transactional->send(
        'subscriber@example.com',
        'sender@yourdomain.com',
        '<p>Спасибо за подписку!</p>',
        ['subject' => 'Добро пожаловать']
    );
} catch (RateLimitException $e) {
    echo "Превышен лимит запросов, повтор через {$e->getRetryAfter()} с\n";
} catch (ApiException $e) {
    echo "Ошибка DashaMail API {$e->getApiCode()}: {$e->getMessage()}\n";
}
