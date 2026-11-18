<?php

return [
    /*
    |--------------------------------------------------------------------------
    | The webhook URL for the Lark channel you want to send messages to.
    |--------------------------------------------------------------------------
    |
    | Example: https://open.feishu.cn/open-apis/bot/v2/hook/xxxxxx
    |
    */
    'webhook_url' => env('LARK_WEBHOOK_URL', ''),

    /*
    |--------------------------------------------------------------------------
    | The title of the message sent to Lark.
    |--------------------------------------------------------------------------
    |
    | Can set in config logging.php file
    |
    */
    'title' => env('LARK_TITLE', env('APP_NAME')),
];
