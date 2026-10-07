<?php
class AuthService
{
    public function attempt(string $username, string $password): bool
    {
        $user = (new User())->findByUsername($username);
        if (!$user) return false;
        if (!password_verify($password, $user['password_hash'])) return false;

        // احتفظ بالـCSRF token القديم قبل تجديد الجلسة
        $oldCsrf = $_SESSION['_csrf'] ?? null;

        // أعد توليد session id لمنع session fixation
        session_regenerate_id(true);

        // استرجع token أو أنشئ واحداً جديداً
        $_SESSION['_csrf'] = $oldCsrf ?: bin2hex(random_bytes(32));

        unset($user['password_hash']);
        $_SESSION['user'] = $user;
        $_SESSION['_started'] = time();

        (new User())->updateLastLogin((int)$user['id']);

        try {
            (new AuditLog())->write((int)$user['id'], 'LOGIN', 'user', (int)$user['id']);
        } catch (Throwable $e) {
            error_log('[AuthService] audit failed: ' . $e->getMessage());
        }

        return true;
    }

    public function logout(): void
    {
        $u = currentUser();
        if ($u) {
            try {
                (new AuditLog())->write((int)$u['id'], 'LOGOUT', 'user', (int)$u['id']);
            } catch (Throwable $e) {
                error_log('[AuthService] logout audit failed: ' . $e->getMessage());
            }
        }

        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(
                session_name(), '',
                time() - 42000,
                $p['path'], $p['domain'],
                $p['secure'], $p['httponly']
            );
        }

        session_destroy();
    }
}