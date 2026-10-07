<?php
$requestUri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
$basePath   = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');
if ($basePath === '.' || $basePath === '/') $basePath = '';

if ($basePath !== '' && str_starts_with($requestUri, $basePath)) {
    $requestUri = substr($requestUri, strlen($basePath));
}
$requestUri = '/' . trim($requestUri, '/');

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if (!str_starts_with($requestUri, '/api')) {
    apiError('Not Found', [], 404);
}
$path = substr($requestUri, 4) ?: '/';

$apiRoutes = [
    'POST' => [
        '/auth/login'          => ['AuthApiController',  'login'],
        '/auth/logout'         => ['AuthApiController',  'logout'],

        '/attendance/save'     => ['AttendanceApiController', 'save'],
        '/attendance/bulk'     => ['AttendanceApiController', 'bulkUpdate'],

        '/servants/create'     => ['ServantApiController', 'create'],
        '/servants/update'     => ['ServantApiController', 'update'],
        '/servants/delete'     => ['ServantApiController', 'delete'],

        '/choirs/create'       => ['ChoirApiController', 'create'],
        '/choirs/update'       => ['ChoirApiController', 'update'],
        '/choirs/delete'       => ['ChoirApiController',    'delete'],

        '/activities/create'   => ['ActivityApiController', 'create'],
        '/activities/update'   => ['ActivityApiController', 'update'],
        '/activities/delete'   => ['ActivityApiController', 'delete'],
    ],
    'GET' => [
        '/health'                    => ['HealthApiController', 'health'],
        '/me'                        => ['AuthApiController', 'me'],

        '/servants'                  => ['ServantApiController', 'index'],
        '/choirs'                    => ['ChoirApiController', 'index'],
        '/activities'                => ['ActivityApiController', 'index'],

        '/attendance/session'        => ['AttendanceApiController', 'session'],
        '/attendance/history'        => ['AttendanceApiController', 'history'],

        '/statistics/servant'        => ['StatisticsApiController', 'servant'],
        '/statistics/choir'          => ['StatisticsApiController', 'choir'],
        '/statistics/overall'        => ['StatisticsApiController', 'overall'],

        '/dashboard'                 => ['DashboardApiController', 'index'],
    ],
];

if (!isset($apiRoutes[$method][$path])) {
    apiError('المسار غير موجود', [], 404);
}

[$class, $action] = $apiRoutes[$method][$path];
$file = APP_PATH . '/controllers/api/' . $class . '.php';

if (!file_exists($file)) {
    apiError('المتحكم غير موجود', [], 500);
}

require $file;

try {
    (new $class())->$action();
} catch (Throwable $e) {
    error_log('[API] ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());
    $debug = ($_ENV['APP_ENV'] ?? 'production') === 'local';
    apiError(
        $debug ? $e->getMessage() : 'حدث خطأ في الخادم',
        $debug ? ['file' => $e->getFile(), 'line' => $e->getLine()] : [],
        500
    );
}