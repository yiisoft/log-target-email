<?php

declare(strict_types=1);

use Psr\Log\LogLevel;

return [
    'yiisoft/log-target-email' => [
        'emailTarget' => [
            'emailTo' => 'admin@example.com',
            'subjectEmail' => 'Application Log',
            'levels' => [
                LogLevel::CRITICAL,
                LogLevel::EMERGENCY,
                LogLevel::ERROR,
                LogLevel::WARNING,
            ],
        ],
    ],
];
