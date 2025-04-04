<?php

use Illuminate\Support\Facades\App;

return [
    'api_url' => 'https://www.paytr.com/odeme',
    'test_mode' => App::environment(['local', 'staging']),
    'report_email' => 'bombheroes2@gmail.com',
    'api' => 'iframe',
    'token_url' => 'https://www.paytr.com/odeme/api/get-token',
    'lang' => 'tr'
];