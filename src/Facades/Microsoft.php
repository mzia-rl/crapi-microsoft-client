<?php

namespace Canzell\Facades;

use Canzell\Microsoft\MicrosoftClient;
use Illuminate\Support\Facades\Facade;

class Microsoft extends Facade
{

    static public function getFacadeAccessor()
    {
        return MicrosoftClient::class;
    }

}