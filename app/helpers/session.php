<?php
/**
 * session.php — نسخة متوافقة 100% مع ByetHost
 */

function startSecureSession(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) return;

    // ============================================================
    // 1. مسار تخزين مخصص لتجنب /php_sessions المشترك
    // ============================================================
    if (defined('STORAGE_PATH')) {
        $customPath = STORAGE_PATH . '/sessions';
        if (!is_dir($customPath)) {
            @mkdir($customPath, 0755, true);
        }
        if (is_dir($customPath) && is_writable($customPath)) {
            session_save_path($customPath);
        }
    }

    // ============================================================
    // 2. إعدادات PHP للجلسة
    // ============================================================
    @ini_set('session.use_strict_mode', '0');
    @ini_set('session.use_only_cookies', '1');
    @ini_set('session.use_trans_sid', '0');
    @ini_set('session.gc_maxlifetime', '86400');

    // ============================================================
    // 3. الكوكي — secure=false دائماً على ByetHost
    // ============================================================
    // ByetHost: المتصفح يتصل HTTPS، لكن PHP يرى HTTP خلف الـproxy.
    // إذا فعّلنا Secure، بعض الطلبات لا ترسل الكوكي → جلسة فارغة.
    // الحماية الحقيقية تأتي من httponly + samesite + HTTPS على الـproxy.
    $params = [
        'lifetime' => 0,
        'path'     => '/',
        'domain'   => '',
        'secure'   => false,
        'httponly' => true,
        'samesite' => 'Lax',
    ];

    if (PHP_VERSION_ID >= 70300) {
        session_set_cookie_params($params);
    } else {
        session_set_cookie_params(
            $params['lifetime'],
            $params['path'] . '; samesite=' . $params['samesite'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }

    session_name('CHOIR_SESS');
    session_start();

    // ============================================================
    // 4. CSRF token مضمون
    // ============================================================
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }
}