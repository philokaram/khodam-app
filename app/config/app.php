<?php
return [
    'name'      => $_ENV['APP_NAME'] ?? 'حضور الخدام',
    'url'       => $_ENV['APP_URL'] ?? '',
    'env'       => $_ENV['APP_ENV'] ?? 'production',
    'timezone'  => $_ENV['APP_TIMEZONE'] ?? 'Africa/Cairo',
    'locale'    => 'ar',
    'dir'       => 'rtl',
    'session_lifetime' => (int)($_ENV['SESSION_LIFETIME'] ?? 120),
];