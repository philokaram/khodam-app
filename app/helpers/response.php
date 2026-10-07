<?php
/**
 * response.php — استجابات JSON موحّدة + أدوات URL
 */

function jsonResponse(array $payload, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('X-Content-Type-Options: nosniff');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function apiSuccess($data = null, string $message = '', int $status = 200): void
{
    jsonResponse([
        'success' => true,
        'message' => $message,
        'data'    => $data,
        'errors'  => [],
    ], $status);
}

function apiError(string $message, array $errors = [], int $status = 400): void
{
    jsonResponse([
        'success' => false,
        'message' => $message,
        'data'    => null,
        'errors'  => $errors,
    ], $status);
}

function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

/**
 * هل الطلب موجه لـ API؟
 */
function isApiRequest(): bool
{
    $uri = $_SERVER['REQUEST_URI'] ?? '';
    if (str_contains($uri, '/api/')) return true;
    if (str_starts_with($uri, '/api')) return true;
    if (str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json')) return true;
    if (str_contains($_SERVER['CONTENT_TYPE'] ?? '', 'application/json')) return true;
    if (($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest'
        && str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'json')) return true;
    return false;
}

/**
 * هل الاتصال HTTPS؟ (متوافق مع proxy)
 */
function isHttps(): bool
{
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') return true;
    if (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https') return true;
    if (($_SERVER['HTTP_X_FORWARDED_SSL'] ?? '') === 'on') return true;
    if (($_SERVER['HTTP_CF_VISITOR'] ?? '') && str_contains($_SERVER['HTTP_CF_VISITOR'], 'https')) return true;
    if ((int)($_SERVER['SERVER_PORT'] ?? 0) === 443) return true;
    return false;
}

/**
 * Base URL للتطبيق (يعمل حتى في مجلد فرعي)
 */
function appBaseUrl(): string
{
    $configured = trim((string)($_ENV['APP_URL'] ?? ''));
    if ($configured !== '') return rtrim($configured, '/');

    $scheme = isHttps() ? 'https' : 'http';
    $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $script = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
    $dir    = rtrim(str_replace('\\', '/', dirname($script)), '/');
    if ($dir === '.' || $dir === '/') $dir = '';

    return $scheme . '://' . $host . $dir;
}

function url(string $path = ''): string
{
    return appBaseUrl() . '/' . ltrim($path, '/');
}