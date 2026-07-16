<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/vendor/autoload.php';
require_once dirname(__DIR__) . '/bootstrap/helpers.php';

$_ENV['JWT_SECRET'] = 'test-secret-key-for-jwt-signing-purposes';
$_ENV['APP_NAME'] = 'FMMP';
putenv('JWT_SECRET=test-secret-key-for-jwt-signing-purposes');
putenv('APP_NAME=FMMP');

\App\Services\LocaleService::set('th');

$cacheDir = dirname(__DIR__) . '/storage/cache';

if (!is_dir($cacheDir)) {
    mkdir($cacheDir, 0755, true);
}
