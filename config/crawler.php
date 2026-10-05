<?php

use Illuminate\Support\Facades\Facade;

return [
    'token' => env('CRAWLER_TOKEN', ''),
    'username' => env('CRAWLER_USERNAME', ''),
    'password' => env('CRAWLER_PASSWORD', ''),

    'url' => env('CRAWLER_URL', ''),

    'kpdn_token' => env('KPDN_TOKEN', ''),
];
