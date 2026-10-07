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
            'title' => __('nav.users'),
            'users' => $users,
        ]);
    }

    public function create(): void
    {
        requireLogin();
        requirePermission('users.create');

        view('users/create', [
            'title' => 'إضافة مستخدم',
            'roles' => (new Role())->all(),
            'choirs' => (new Choir())->all(),
        ]);
    }

    public function store(): void
    {
        requireLogin();
        requirePermission('users.create');
        verifyCsrf();

        $data = [
            'name'     => trim($_POST['name'] ?? ''),
            'username' => trim($_POST['username'] ?? ''),
            'email'    => trim($_POST['email'] ?? '') ?: null,
            'password' => $_POST['password'] ?? '',
            'role_id'  => (int)($_POST['role_id'] ?? 0),
            'choir_id' => !empty($_POST['choir_id']) ? (int)$_POST['choir_id'] : null,
            'status'   => 'active',
        ];

        $errors = [];
        if ($data['name'] === '')     $errors[] = 'الاسم مطلوب.';
        if ($data['username'] === '') $errors[] = 'اسم المستخدم مطلوب.';
        if (strlen($data['password']) < 6) $errors[] = 'كلمة المرور 6 أحرف على الأقل.';
        if (!$data['role_id'])        $errors[] = 'الدور مطلوب.';

        $existing = Database::one("SELECT id FROM users WHERE username = ?", [$data['username']]);
        if ($existing) $errors[] = 'اسم المستخدم مستخدم بالفعل.';

        if ($errors) {
            $_SESSION['_errors'] = $errors;
            redirect('/users/create');
        }

        $data['password_hash'] = password_hash($data['password'], PASSWORD_BCRYPT);
        unset($data['password']);

        $id = Database::insert('users', $data);
        (new AuditLog())->write((int)currentUser()['id'], 'CREATE_USER', 'user', $id);

        $_SESSION['_success'] = 'تم إضافة المستخدم بنجاح.';
        redirect('/users');
    }
}