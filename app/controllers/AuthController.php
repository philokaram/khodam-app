<?php
class AuthController
{
    public function showLogin(): void
    {
        if (isLoggedIn()) redirect('/dashboard');
        view('auth/login', ['title' => __('auth.login_title')]);
    }

    public function login(): void
    {
        verifyCsrf();
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        $errors = [];
        if (!vRequired($username)) $errors[] = __('validation.username_required');
        if (!vRequired($password)) $errors[] = __('validation.password_required');

        if ($errors) {
            $_SESSION['_errors'] = $errors;
            $_SESSION['_old'] = ['username' => $username];
            redirect('/login');
        }

        if (!(new AuthService())->attempt($username, $password)) {
            $_SESSION['_errors'] = [__('auth.invalid_credentials')];
            $_SESSION['_old'] = ['username' => $username];
            redirect('/login');
        }
        redirect('/dashboard');
    }

    public function logout(): void
    {
        (new AuthService())->logout();
        redirect('/login');
    }
}