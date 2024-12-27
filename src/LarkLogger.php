<?php

namespace PosAppVN\LarkLogger;

use Monolog\Logger;

class LarkLogger
{
    /**
     * Create a custom Monolog instance.
     *
     * @param array $config
     * @return \Monolog\Logger
     */
    public function __invoke(array $config)
    {
        return new Logger(
            config('app.name'),
            [
                new LarkHandler($config)
            ]
        );
    }
}
