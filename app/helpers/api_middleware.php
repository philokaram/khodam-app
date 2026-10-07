<?php
/**
 * طبقة حماية موحّدة لكل الـAPI
 */

function apiRequireLogin(): void
{
    if (!isLoggedIn()) {
        apiError('يجب تسجيل الدخول', [], 401);
    }
}

function apiRequirePermission(string $permission): void
{
    if (!hasPermission($permission)) {
        apiError('ليس لديك صلاحية لتنفيذ هذا الإجراء', [], 403);
    }
}

function apiVerifyCsrf(): void
{
    $name = $_ENV['CSRF_TOKEN_NAME'] ?? '_csrf_token';
    $sent = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!is_string($sent) || !hash_equals($_SESSION['_csrf'] ?? '', $sent)) {
        apiError('انتهت صلاحية الجلسة، يرجى إعادة التحميل', ['csrf' => 'invalid'], 419);
    }
}

function apiRequirePost(): void
{
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
        apiError('طريقة غير مسموحة', [], 405);
    }
}

/**
 * قراءة مدخلات JSON أو Form
 */
function apiInput(): array
{
    static $cache = null;
    if ($cache !== null) return $cache;
    $ctype = $_SERVER['CONTENT_TYPE'] ?? '';
    if (str_contains($ctype, 'application/json')) {
        $raw = file_get_contents('php://input');
        $decoded = json_decode($raw, true);
        $cache = is_array($decoded) ? $decoded : [];
    } else {
        $cache = $_POST;
    }
    return $cache;
}

/**
 * Rate limiting بسيط بالجلسة (لتفادي brute force)
 */
function apiRateLimit(string $key, int $maxAttempts = 20, int $windowSeconds = 60): void
{
    $now = time();
    $bucket = $_SESSION['_rl'][$key] ?? ['count' => 0, 'reset' => $now + $windowSeconds];
    if ($now >= $bucket['reset']) {
        $bucket = ['count' => 0, 'reset' => $now + $windowSeconds];
    }
    $bucket['count']++;
    $_SESSION['_rl'][$key] = $bucket;
    if ($bucket['count'] > $maxAttempts) {
        apiError('تم تجاوز الحد المسموح من الطلبات، حاول لاحقاً', [], 429);
    }
}