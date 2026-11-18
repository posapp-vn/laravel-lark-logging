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

    /*
    |--------------------------------------------------------------------------
    | Show the log message in the card header title.
    |--------------------------------------------------------------------------
    |
    | Lark push notifications only show the card header title. When enabled,
    | the header title becomes "[LEVEL] message" and the title above moves
    | to the header subtitle, so the mobile notification shows the error.
    |
    */
    'message_in_title' => env('LARK_MESSAGE_IN_TITLE', true),
];
