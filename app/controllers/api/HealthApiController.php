<?php
class HealthApiController
{
    public function health(): void
    {
        $checks = [
            'php'       => version_compare(PHP_VERSION, '8.1.0', '>='),
            'pdo'       => extension_loaded('pdo_mysql'),
            'json'      => extension_loaded('json'),
            'mbstring'  => extension_loaded('mbstring'),
            'https'     => isHttps(),
            'db'        => false,
            'time'      => date('c'),
        ];

        try {
            Database::pdo()->query('SELECT 1');
            $checks['db'] = true;
        } catch (Throwable $e) {
            $checks['db'] = false;
            $checks['db_error'] = ($_ENV['APP_ENV'] ?? '') === 'local' ? $e->getMessage() : null;
        }

        $ok = $checks['php'] && $checks['pdo'] && $checks['json'] && $checks['db'];
        apiSuccess($checks, $ok ? 'النظام يعمل بشكل سليم' : 'هناك مشاكل في البيئة', $ok ? 200 : 503);
    }
}