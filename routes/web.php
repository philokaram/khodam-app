<?php
// حساب المسار الحالي (مع دعم المشروع في مجلد فرعي)
$requestUri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

// استخرج base path من SCRIPT_NAME
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
$basePath   = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');
if ($basePath === '.' || $basePath === '/') $basePath = '';

// إزالة الـbase من بداية الـURI
$uri = $requestUri;
if ($basePath !== '' && str_starts_with($uri, $basePath)) {
    $uri = substr($uri, strlen($basePath));
}
$uri = '/' . trim($uri, '/');

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// توجيه /api
if (str_starts_with($uri, '/api')) {
    require BASE_PATH . '/routes/api.php';
    return;
}

$webRoutes = [
    'GET' => [
        '/'                    => ['AuthController', 'showLogin'],
        '/login'               => ['AuthController', 'showLogin'],
        '/logout'              => ['AuthController', 'logout'],
        '/dashboard'           => ['DashboardController', 'index'],

        '/servants'            => ['ServantController', 'index'],
        '/servants/create'     => ['ServantController', 'create'],
        '/servants/edit'       => ['ServantController', 'edit'],
        '/servants/show'       => ['ServantController', 'show'],
        '/servants/export'     => ['ExportController', 'servants'],

        '/choirs'              => ['ChoirController', 'index'],
        '/choirs/create'       => ['ChoirController', 'create'],
        '/choirs/edit'         => ['ChoirController', 'edit'],

        '/activities'          => ['ActivityController', 'index'],
        '/activities/create'   => ['ActivityController', 'create'],
        '/activities/edit'     => ['ActivityController', 'edit'],

        '/attendance'          => ['AttendanceController', 'index'],
        '/attendance/create'   => ['AttendanceController', 'create'],
        '/attendance/history'  => ['AttendanceController', 'history'],

        '/reports'             => ['ReportController', 'index'],
        '/reports/servant'     => ['ReportController', 'servant'],
        '/reports/export/attendance'        => ['ExportController', 'attendance'],
    	'/reports/export/servants-stats'    => ['ExportController', 'servantsStats'],
    	'/reports/export/activities-stats'  => ['ExportController', 'activitiesStats'],

        '/users'               => ['UserController', 'index'],
        '/users/create'        => ['UserController', 'create'],
        '/users/edit'          => ['UserController', 'edit'],
    ],
    'POST' => [
        // تسجيل الدخول عبر الصفحة (fallback بدون JS)
        '/login'               => ['AuthController', 'login'],
    ],
];

if (!isset($webRoutes[$method][$uri])) {
    http_response_code(404);
    if (isApiRequest()) apiError('الصفحة غير موجودة', [], 404);
    exit(__('messages.not_found'));
}

[$class, $action] = $webRoutes[$method][$uri];
require APP_PATH . '/controllers/' . $class . '.php';
(new $class())->$action();