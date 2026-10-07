<?php
class UserController
{
    public function index(): void
    {
        requireLogin();
        requirePermission('users.view');

        $users = Database::all(
            "SELECT u.*, r.name AS role_name, r.label_ar AS role_label, c.name AS choir_name
             FROM users u
             JOIN roles r ON r.id = u.role_id
             LEFT JOIN choirs c ON c.id = u.choir_id
             ORDER BY u.id"
        );

        view('users/index', [
            'title' => 'المستخدمون',
            'users' => $users,
            'roles' => (new Role())->all(),       // ← أضف هذا
            'choirs' => (new Choir())->all(),     // ← أضف هذا
        ]);
    }

    public function create(): void
    {
        requireLogin();
        requirePermission('users.create');

        view('users/create', [
            'title'  => 'إضافة مستخدم',
            'roles'  => (new Role())->all(),
            'choirs' => (new Choir())->all(),
        ]);
    }

    public function edit(): void
    {
        requireLogin();
        requirePermission('users.edit');

        $id = (int)($_GET['id'] ?? 0);
        $user = Database::one(
            "SELECT u.*, r.name AS role_name, r.label_ar AS role_label
             FROM users u JOIN roles r ON r.id = u.role_id
             WHERE u.id = ?",
            [$id]
        );
        if (!$user) {
            http_response_code(404);
            exit('المستخدم غير موجود');
        }

        view('users/edit', [
            'title'  => 'تعديل المستخدم',
            'user'   => $user,
            'roles'  => (new Role())->all(),
            'choirs' => (new Choir())->all(),
        ]);
    }
}