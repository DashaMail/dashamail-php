# dashamail-php

Официальный PHP SDK для [DashaMail REST API v2](https://dashamail.ru/api/) — адресные базы,
рассылки, автоматизации, транзакционные письма, отчёты, диалоги, обработка входящей почты
и оптимизация изображений из одного клиента без единой зависимости, кроме `ext-curl`.

## Требования

- PHP 7.2 или новее
- `ext-curl`, `ext-json` (входят практически в любую сборку PHP)

## Установка

```bash
composer require dashamail/dashamail-php
```

## Где взять API-ключ

Личный кабинет → Аккаунт → API и интеграции. Обращайтесь с ним как с паролем: он действует
от имени всего аккаунта.

## Быстрый старт

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

Более полный пример — в [`examples/quickstart.php`](examples/quickstart.php), а полный
справочник параметров каждого эндпоинта — на [dashamail.ru/api](https://dashamail.ru/api/):
SDK повторяет его метод в метод.

## Ресурсы

`DashaMail` — это объект с одним публичным свойством на каждый раздел API. Каждый метод
возвращает [`DashaMail\Response`](src/Response.php) (ведёт себя как массив/список полезной
нагрузки `data`) либо бросает `DashaMail\Exception\ApiException`.

| Свойство           | Класс                               | Что покрывает |
|--------------------|--------------------------------------|--------|
| `->lists`          | `DashaMail\Resource\Lists`          | Адресные базы, подписчики, дополнительные поля, импорт |
| `->segments`       | `DashaMail\Resource\Segments`       | Сохранённые сегменты подписчиков |
| `->campaigns`      | `DashaMail\Resource\Campaigns`      | Рассылки: черновик, запуск, пауза, A/B-тесты, вложения, папки |
| `->automations`    | `DashaMail\Resource\Automations`    | Письма по событиям подписчика |
| `->workflows`      | `DashaMail\Resource\Workflows`      | Сценарии визуального конструктора |
| `->templates`      | `DashaMail\Resource\Templates`      | Сохранённые HTML-шаблоны и шаблонные рассылки |
| `->reports`        | `DashaMail\Resource\Reports`        | Статистика рассылок, лента событий, разбивки по кликам/возвратам/гео, результаты A/B |
| `->transactional`  | `DashaMail\Resource\Transactional`  | Одиночные транзакционные письма: отправка, статус, журнал, статистика |
| `->account`        | `DashaMail\Resource\Account`        | Баланс, отправители, домены отправки, webhooks |
| `->dialogs`        | `DashaMail\Resource\Dialogs`        | Ответы подписчиков на рассылки |
| `->router`         | `DashaMail\Resource\Router`         | Обработка входящей почты: домены, правила маршрутизации, сохранённые письма |
| `->images`         | `DashaMail\Resource\Images`         | Уменьшение веса изображения перед вставкой в рассылку |

## Работа с ответом

```php
$members = $dashamail->lists->members($listId, ['limit' => 100]);

foreach ($members as $member) {   // Response реализует Traversable
    echo $member['email'], "\n";
}

count($members);                  // число записей на этой странице
$members->getData();              // сырой массив, если array-доступ не нужен
$members->hasMore();              // true, если есть следующая страница (см. «Пагинация» ниже)
```

## Пагинация

Списочные эндпоинты (`Lists::members()`, `Lists::unsubscribed()`, `Transactional::log()` и
другие) не возвращают общее количество записей — вместо этого DashaMail сообщает, есть ли
ещё страница:

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

## Обработка ошибок

Любой ответ не из диапазона 2xx бросает подкласс `DashaMail\Exception\ApiException`,
выбранный по HTTP-статусу:

| Исключение                 | HTTP-статус |
|-----------------------------|------|
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
    // $e->getApiCode()    — собственный устойчивый код ошибки DashaMail (см. https://dashamail.ru/api/errors/)
    // $e->getHttpStatus() — HTTP-статус ответа
    // $e->getDetails()    — дополнительный структурированный контекст от API, если есть
    error_log($e->getApiCode() . ': ' . $e->getMessage());
}
```

Если ответ вообще не пришёл (обрыв DNS, TLS, таймаут...), бросается
`DashaMail\Exception\NetworkException`.

## Изображения

`POST /images/optimize` — единственный эндпоинт, у которого вход и выход не просто JSON:

```php
// Из переменной, уже загруженной в память:
$result = $dashamail->images->optimize(file_get_contents('banner.png'), ['max_width' => 1600]);
file_put_contents('banner-optimized.png', base64_decode($result['image']));

// Прямо с диска, без предварительной загрузки в строку PHP:
$result = $dashamail->images->optimizeFile('/path/to/banner.png');

// То же самое, но без промежуточного base64 — сразу сырые байты:
$binary = $dashamail->images->optimizeFileBinary('/path/to/banner.png');
$binary->saveTo('/path/to/banner-optimized.png');
```

## Расширенная настройка

```php
$dashamail = new DashaMail('YOUR_API_KEY', [
    'timeout' => 60,                                  // секунды, по умолчанию 30
    'base_url' => 'https://api.dashamail.com/v2',      // переопределить для тестов/прокси
    'user_agent' => 'my-app/1.0 (+dashamail-php)',
]);

// Способ вызвать эндпоинт, для которого в SDK ещё нет отдельного метода:
$dashamail->getClient()->request('GET', '/some/new/endpoint', ['foo' => 'bar']);
```

## Тесты

```bash
composer install
composer test
```

## Лицензия

MIT, см. [LICENSE](LICENSE).
