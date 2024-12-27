<?php

namespace PosAppVN\LarkLogger;

use Illuminate\Support\ServiceProvider;

class LarkLoggerServiceProvider extends ServiceProvider
{
    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/lark-logger.php', 'lark-logger'
        );
    }

    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/lark-logger.php' => config_path('lark-logger.php'),
        ], 'config');
    }
}
