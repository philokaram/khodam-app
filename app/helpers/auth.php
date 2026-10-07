<?php
function currentUser(): ?array
{
    return $_SESSION['user'] ?? null;
}

function isLoggedIn(): bool
{
    return !empty($_SESSION['user']);
}

function requireLogin(): void
{
    if (!isLoggedIn()) {
        if (function_exists('isApiRequest') && isApiRequest()) {
            apiError('يجب تسجيل الدخول', [], 401);
        }
        redirect('/login');
    }
}

function hasPermission(string $permission): bool
{
    static $cache = null;
    $u = currentUser();
    if (!$u) return false;
    if ($cache === null) {
        if (!class_exists('Permission')) return false;
        try {
            $cache = (new Permission())->forRole((int)$u['role_id']);
        } catch (Throwable $e) {
            error_log('[hasPermission] ' . $e->getMessage());
            return false;
        }
    }
    return in_array('*', $cache, true) || in_array($permission, $cache, true);
}

function requirePermission(string $permission): void
{
    if (!hasPermission($permission)) {
        if (function_exists('isApiRequest') && isApiRequest()) {
            apiError('ليس لديك صلاحية لتنفيذ هذا الإجراء', [], 403);
        }
        http_response_code(403);
        exit(__('messages.no_permission') ?? 'Forbidden');
    }
}

function canAccessChoir(int $choirId): bool
{
    $u = currentUser();
    if (!$u) return false;
    $role = $u['role_name'] ?? '';
    if (in_array($role, ['SUPER_ADMIN', 'ADMIN'], true)) return true;
    return (int)($u['choir_id'] ?? 0) === $choirId;
}