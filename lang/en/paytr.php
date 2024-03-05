<?php

return [
    'guide' => 'We provide local payment via paytr.com!',
    'failure' => [
        'missing-params' => 'Payment error. Contact our customer service for helps',
        'timeout' => 'Payment is timeout. Please retry!',
        'step1-error' => 'Payment error. Please retry before contact our customer service.',
        'step1-exception' => 'Payment error. Contact our customer service for helps'
    ],
    'status' => [
        0 => 'Not processed',
        1 => 'Payment pending',
        2 => 'Payment cancel',
        3 => 'Payment success',
        4 => 'Payment failure',
    ],
];