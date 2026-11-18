# Laravel Lark logger

Send logs to Lark chat via Lark bot

## Install

```
composer require posapp-vn/laravel-lark-logging
```

Define Lark Bot Webhook URL in your `.env` file

```
LARK_WEBHOOK_URL=https://open.feishu.cn/open-apis/bot/v2/hook/<your-hook-id-xxxxxx>
```


Add to `config/logging.php` file new channel:

```php
'lark' => [
    'driver' => 'custom',
    'via'    => PosAppVN\LarkLogger\LarkLogger::class,
    'level'  => 'debug',
    'title' => env('APP_NAME', 'Laravel Log'), // optional - title of the message
    'retries' => 3, // optional - retry sending message 3 times
]
```

Publish config file
```
php artisan vendor:publish --provider "PosAppVN\LarkLogger\LarkLoggerServiceProvider"
```

## Message in push notification

Lark mobile push notifications for card messages only show the card header title.
By default the header title is `[LEVEL] message` (e.g. `[ERROR] Rejected internal system request: invalid token.`)
and the configured `title` is shown as the header subtitle, so the notification shows the actual log message.

To keep the old behaviour (configured `title` in the header), set in `.env`:

```
LARK_MESSAGE_IN_TITLE=false
```

or per channel in `config/logging.php`: `'message_in_title' => false`.

## Create bot

For using this package you need to create Lark bot: https://open.larksuite.com/document/client-docs/bot-v3/add-custom-bot

## Configuring a different chat id or token per channel

1. Add `webhook_url` or `title` to channels in `config/logging.php`.
```php
[
    'channels' => [

        'lark-log' => [
            'driver' => 'custom',
            'via'    => PosAppVN\LarkLogger\LarkLogger::class,
            'level'  => 'debug',
            'webhook_url' => 'https://open.feishu.cn/open-apis/bot/v2/hook/<your-hook-id-xxxxxx>',
            'title' => 'Laravel Log',
        ],
        
        'lark-system' => [
            'driver' => 'custom',
            'via'    => PosAppVN\LarkLogger\LarkLogger::class,
            'level'  => 'error',
            'webhook_url' => 'https://open.feishu.cn/open-apis/bot/v2/hook/<your-hook-id-xxxxxx>',
            'title' => 'System Log',
        ],

    ]
]
```
