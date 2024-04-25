<?php

return [
    'scope' => env('MICROSOFT_SCOPE', 'https://graph.microsoft.com/.default'),
    'version' => env('MICROSOFT_VERSION', 'v1.0'),
    'tenant_id' => env('MICROSOFT_TENANT_ID'),
    'client_id' => env('MICROSOFT_CLIENT_ID'),
    'client_secret' => env('MICROSOFT_CLIENT_SECRET'),
];