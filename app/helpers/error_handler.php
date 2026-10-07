<?php
function registerErrorHandlers(): void
{
    $isLocal = ($_ENV['APP_ENV'] ?? 'production') === 'local';

    error_reporting(E_ALL);
    ini_set('display_errors', $isLocal ? '1' : '0');
    ini_set('log_errors', '1');

    // سجل الأخطاء
    if (defined('STORAGE_PATH')) {
        $logDir = STORAGE_PATH . '/logs';
        if (!is_dir($logDir)) @mkdir($logDir, 0755, true);
        if (is_dir($logDir) && is_writable($logDir)) {
            ini_set('error_log', $logDir . '/error-' . date('Y-m-d') . '.log');
        }
    }

    set_error_handler(function ($severity, $message, $file, $line) {
        if (!(error_reporting() & $severity)) return false;
        throw new ErrorException($message, 0, $severity, $file, $line);
    });

    set_exception_handler(function (Throwable $e) use ($isLocal) {
        error_log('[UNCAUGHT] ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());

        // إن كان الطلب API → JSON
        if (function_exists('isApiRequest') && isApiRequest()) {
            if (function_exists('apiError')) {
                apiError(
                    $isLocal ? $e->getMessage() : 'حدث خطأ في الخادم',
                    $isLocal ? ['file' => $e->getFile(), 'line' => $e->getLine()] : [],
                    500
                );
            } else {
                header('Content-Type: application/json; charset=utf-8');
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'message' => $isLocal ? $e->getMessage() : 'حدث خطأ في الخادم',
                    'data'    => null,
                    'errors'  => [],
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }
        }

        // صفحة HTML
        http_response_code(500);
        if ($isLocal) {
            echo '<pre style="font-family:monospace;direction:ltr;padding:20px;background:#fff3f3">';
            echo "<strong>ERROR:</strong> " . htmlspecialchars($e->getMessage()) . "\n";
            echo "<strong>FILE:</strong> " . htmlspecialchars($e->getFile()) . ":" . $e->getLine() . "\n\n";
            echo "<strong>TRACE:</strong>\n" . htmlspecialchars($e->getTraceAsString());
            echo '</pre>';
        } else {
            echo '<!DOCTYPE html><html lang="ar" dir="rtl"><head><meta charset="utf-8">';
            echo '<title>خطأ</title></head><body style="font-family:sans-serif;padding:40px;text-align:center">';
            echo '<h1>حدث خطأ غير متوقع</h1><p>يرجى المحاولة لاحقاً.</p>';
            echo '</body></html>';
        }
        exit;
    });

    register_shutdown_function(function () use ($isLocal) {
        $err = error_get_last();
        if ($err && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
            error_log('[FATAL] ' . $err['message'] . ' @ ' . $err['file'] . ':' . $err['line']);
        }
    });
}