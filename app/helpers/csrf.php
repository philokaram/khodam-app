<?php
/**
 * csrf.php
 */

function csrfToken(): string
{
    // تأكد من الجلسة نشطة
    if (session_status() !== PHP_SESSION_ACTIVE) {
        return '';
    }

    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf'];
}

function csrfField(): string
{
    $name = $_ENV['CSRF_TOKEN_NAME'] ?? '_csrf_token';
    return '<input type="hidden" name="' . htmlspecialchars($name, ENT_QUOTES) . '" value="' . csrfToken() . '">';
}

function verifyCsrf(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        http_response_code(419);
        exit('الجلسة غير نشطة. يرجى تفعيل الكوكيز.');
    }

    $name = $_ENV['CSRF_TOKEN_NAME'] ?? '_csrf_token';
    $sent = $_POST[$name]
         ?? $_SERVER['HTTP_X_CSRF_TOKEN']
         ?? '';

    $stored = $_SESSION['_csrf'] ?? '';

    if ($stored === '' || $sent === '' || !hash_equals($stored, $sent)) {
        http_response_code(419);
        if (function_exists('isApiRequest') && isApiRequest()) {
            apiError('انتهت صلاحية الجلسة، يرجى إعادة التحميل', ['csrf' => 'invalid'], 419);
        }
        exit('انتهت صلاحية الجلسة. يرجى إعادة تحميل الصفحة.');
    }
}