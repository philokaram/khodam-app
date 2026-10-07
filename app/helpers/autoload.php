<?php
/**
 * autoload.php
 * - يسجّل autoloader للكلاسات (Controllers/Models/Services)
 * - يحمّل كل ملفات helpers بالترتيب الصحيح
 */

function appAutoload(string $class): void
{
    $paths = [
        APP_PATH . '/controllers/api/',
        APP_PATH . '/controllers/',
        APP_PATH . '/models/',
        APP_PATH . '/services/',
    ];
    foreach ($paths as $path) {
        $file = $path . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
}

/**
 * تحميل كل helpers بالترتيب الصحيح — استدعها مرة واحدة
 */
function loadAllHelpers(): void
{
    $helpers = [
        'env.php',             // loadEnv()
        'config.php',          // config()
        'response.php',        // apiSuccess, apiError, url, isHttps, isApiRequest
        'lang.php',            // __(), attendanceStatusLabel()
        'validation.php',      // vRequired, e()...
        'format.php',          // formatDateAr()
        'session.php',         // startSecureSession()
        'database.php',        // Database class
        'csrf.php',            // csrfToken, csrfField, verifyCsrf
        'auth.php',            // currentUser, isLoggedIn, hasPermission
        'error_handler.php',   // registerErrorHandlers()
        'view.php',            // view(), component()
        'api_middleware.php',  // apiRequireLogin, apiVerifyCsrf
    ];

    foreach ($helpers as $h) {
        $f = APP_PATH . '/helpers/' . $h;
        if (file_exists($f)) {
            require_once $f;
        } else {
            error_log("[loadAllHelpers] MISSING: $h");
        }
    }
}