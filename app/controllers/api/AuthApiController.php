<?php
class AuthApiController
{
    public function login(): void
    {
        apiRequirePost();
        apiRateLimit('login', 10, 60);

        $in = apiInput();
        $username = trim((string)($in['username'] ?? ''));
        $password = (string)($in['password'] ?? '');

        $errors = [];
        if ($username === '') $errors['username'] = __('validation.username_required');
        if ($password === '') $errors['password'] = __('validation.password_required');
        if ($errors) apiError('بيانات ناقصة', $errors, 422);

        if (!(new AuthService())->attempt($username, $password)) {
            apiError(__('auth.invalid_credentials'), [], 401);
        }
        apiSuccess(['user' => currentUser()], __('auth.login_success'));
    }

    public function logout(): void
    {
        (new AuthService())->logout();
        apiSuccess(null, 'تم تسجيل الخروج');
    }

    public function me(): void
    {
        apiRequireLogin();
        apiSuccess(['user' => currentUser()]);
    }
}