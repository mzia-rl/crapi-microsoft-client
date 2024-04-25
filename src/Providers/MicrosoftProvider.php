<?php

namespace Canzell\Providers;

use Canzell\Microsoft\MicrosoftClient;
use Illuminate\Support\ServiceProvider;

class MicrosoftProvider extends ServiceProvider
{

    public $singletons = [
        MicrosoftClient::class => MicrosoftClient::class 
    ];

    public function boot()
    {
        $this->publishes([
            __DIR__.'/../../config/microsoft-client.php' => config_path('microsoft-client.php')
        ]);
        $this->mergeConfigFrom(
            __DIR__.'/../../config/microsoft-client.php', 'microsoft-client'
        );
    }

}