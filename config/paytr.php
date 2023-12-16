<?php

use Illuminate\Support\Facades\App;

return [
    'api_url' => 'https://www.paytr.com/odeme',
    'test_mode' => App::environment(['local', 'staging']),
    'report_email' => 'game.oh.vn@gmail.com',
];