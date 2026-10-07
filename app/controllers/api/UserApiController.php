<?php
class UserApiController
{
    public function index(): void
    {
        apiRequireLogin();
        apiRequirePermission('users.view');

        $users = Database::all(
            "SELECT u.id, u.name, u.username, u.email, u.role_id,
                    u.choir_id, u.status, u.last_login_at, u.created_at,
                    r.name AS role_name, r.label_ar AS role_label,
                    c.name AS choir_name
             FROM users u
             JOIN roles r ON r.id = u.role_id
             LEFT JOIN choirs c ON c.id = u.choir_id
             ORDER BY u.id"
        );

        apiSuccess(['users' => $users]);
    }

    public function create(): void
    {
        apiRequirePost();
        apiRequireLogin();
        apiRequirePermission('users.create');
        apiVerifyCsrf();

        $in = apiInput();

        $data = [
            'name'     => trim((string)($in['name'] ?? '')),
            'username' => trim((string)($in['username'] ?? '')),
            'email'    => trim((string)($in['email'] ?? '')) ?: null,
            'password' => (string)($in['password'] ?? ''),
            'role_id'  => (int)($in['role_id'] ?? 0),
            'choir_id' => !empty($in['choir_id']) ? (int)$in['choir_id'] : null,
            'status'   => 'active',
        ];

        // 🔒 منع ADMIN من إنشاء SUPER_ADMIN
        if (!isSuperAdmin() && $data['role_id'] === 1) {
            apiError('لا يمكنك إنشاء مدير عام', [], 403);
        }

        $errors = $this->validate($data, null, true);
        if ($errors) {
            apiError('بيانات غير صحيحة', $errors, 422);
        }

        // فحص تكرار username
        $exists = Database::one("SELECT id FROM users WHERE username = ?", [$data['username']]);
        if ($exists) {
            apiError('اسم المستخدم مستخدم بالفعل', ['username' => 'مستخدم'], 422);
        }

        if ($data['email']) {
            $exists = Database::one("SELECT id FROM users WHERE email = ?", [$data['email']]);
            if ($exists) {
                apiError('البريد مستخدم بالفعل', ['email' => 'مستخدم'], 422);
            }
        }

        $data['password_hash'] = password_hash($data['password'], PASSWORD_BCRYPT);
        unset($data['password']);

        $id = Database::insert('users', $data);
        (new AuditLog())->write((int)currentUser()['id'], 'CREATE_USER', 'user', $id, null, [
            'name' => $data['name'],
            'username' => $data['username'],
            'role_id' => $data['role_id'],
        ]);

        apiSuccess(['id' => $id], 'تم إضافة المستخدم بنجاح', 201);
    }

    public function update(): void
    {
        apiRequirePost();
        apiRequireLogin();
        apiRequirePermission('users.edit');
        apiVerifyCsrf();

        $in = apiInput();
        $id = (int)($in['id'] ?? 0);

        $old = Database::one("SELECT * FROM users WHERE id = ?", [$id]);
        if (!$old) {
            apiError('المستخدم غير موجود', [], 404);
        }

        $data = [
            'name'     => trim((string)($in['name'] ?? '')),
            'username' => trim((string)($in['username'] ?? '')),
            'email'    => trim((string)($in['email'] ?? '')) ?: null,
            'role_id'  => (int)($in['role_id'] ?? 0),
            'choir_id' => !empty($in['choir_id']) ? (int)$in['choir_id'] : null,
            'status'   => in_array($in['status'] ?? '', ['active', 'inactive'], true)
                            ? $in['status'] : 'active',
        ];

        // 🔒 منع ADMIN من تعديل SUPER_ADMIN
        if (!isSuperAdmin() && (int)$old['role_id'] === 1) {
            apiError('لا يمكنك تعديل حساب مدير عام', [], 403);
        }

        // 🔒 منع ADMIN من ترقية أحد إلى SUPER_ADMIN
        if (!isSuperAdmin() && $data['role_id'] === 1) {
            apiError('لا يمكنك تعيين مستخدم كمدير عام', [], 403);
        }

        $errors = $this->validate($data, $id, false);
        if ($errors) {
            apiError('بيانات غير صحيحة', $errors, 422);
        }

        // فحص تكرار username
        $exists = Database::one("SELECT id FROM users WHERE username = ? AND id <> ?", [$data['username'], $id]);
        if ($exists) {
            apiError('اسم المستخدم مستخدم بالفعل', ['username' => 'مستخدم'], 422);
        }

        if ($data['email']) {
            $exists = Database::one("SELECT id FROM users WHERE email = ? AND id <> ?", [$data['email'], $id]);
            if ($exists) {
                apiError('البريد مستخدم بالفعل', ['email' => 'مستخدم'], 422);
            }
        }

        Database::update('users', $data, 'id = ?', [$id]);

        (new AuditLog())->write((int)currentUser()['id'], 'UPDATE_USER', 'user', $id, $old, $data);

        apiSuccess(['id' => $id], 'تم تعديل المستخدم بنجاح');
    }

    public function changePassword(): void
    {
        apiRequirePost();
        apiRequireLogin();
        apiRequirePermission('users.edit');
        apiVerifyCsrf();

        $in = apiInput();
        $id = (int)($in['id'] ?? 0);
        $newPassword = (string)($in['new_password'] ?? '');

        $user = Database::one("SELECT id, username, name, role_id FROM users WHERE id = ?", [$id]);
        if (!$user) {
            apiError('المستخدم غير موجود', [], 404);
        }

        // 🔒 منع ADMIN من تغيير كلمة مرور SUPER_ADMIN
        if (!isSuperAdmin() && (int)$user['role_id'] === 1) {
            apiError('لا يمكنك تغيير كلمة مرور مدير عام', [], 403);
        }

        if (strlen($newPassword) < 6) {
            apiError('كلمة المرور يجب أن تكون 6 أحرف على الأقل', ['new_password' => 'قصيرة'], 422);
        }

        $hash = password_hash($newPassword, PASSWORD_BCRYPT);
        Database::update('users', ['password_hash' => $hash], 'id = ?', [$id]);

        (new AuditLog())->write(
            (int)currentUser()['id'],
            'CHANGE_PASSWORD',
            'user',
            $id,
            null,
            ['username' => $user['username']]
        );

        apiSuccess(null, 'تم تغيير كلمة المرور بنجاح');
    }

    public function delete(): void
    {
        apiRequirePost();
        apiRequireLogin();
        apiRequirePermission('users.delete');
        apiVerifyCsrf();

        $in = apiInput();
        $id = (int)($in['id'] ?? 0);

        if (!$id) {
            apiError('معرّف المستخدم مطلوب', [], 422);
        }

        $me = currentUser();
        if ((int)$me['id'] === $id) {
            apiError('لا يمكنك حذف حسابك الخاص', [], 403);
        }

        $user = Database::one("SELECT * FROM users WHERE id = ?", [$id]);
        if (!$user) {
            apiError('المستخدم غير موجود', [], 404);
        }

        // 🔒 منع ADMIN من تعطيل SUPER_ADMIN
        if (!isSuperAdmin() && (int)$user['role_id'] === 1) {
            apiError('لا يمكنك تعطيل حساب مدير عام', [], 403);
        }

        // 🔒 لا يمكن تعطيل آخر SUPER_ADMIN
        if ((int)$user['role_id'] === 1) {
            $superAdmins = (int)(Database::one(
                "SELECT COUNT(*) AS c FROM users WHERE role_id = 1 AND status = 'active'"
            )['c'] ?? 0);
            if ($superAdmins <= 1) {
                apiError('لا يمكن حذف آخر مدير عام', [], 403);
            }
        }

        Database::update('users', ['status' => 'inactive'], 'id = ?', [$id]);

        (new AuditLog())->write(
            (int)$me['id'],
            'DEACTIVATE_USER',
            'user',
            $id,
            $user,
            ['status' => 'inactive']
        );

        apiSuccess(null, 'تم تعطيل المستخدم بنجاح');
    }

    public function activate(): void
    {
        apiRequirePost();
        apiRequireLogin();
        apiRequirePermission('users.edit');
        apiVerifyCsrf();

        $in = apiInput();
        $id = (int)($in['id'] ?? 0);

        $user = Database::one("SELECT * FROM users WHERE id = ?", [$id]);
        if (!$user) {
            apiError('المستخدم غير موجود', [], 404);
        }

        Database::update('users', ['status' => 'active'], 'id = ?', [$id]);

        (new AuditLog())->write(
            (int)currentUser()['id'],
            'ACTIVATE_USER',
            'user',
            $id,
            $user,
            ['status' => 'active']
        );

        apiSuccess(null, 'تم تنشيط المستخدم');
    }

    private function validate(array $d, ?int $id, bool $requirePassword): array
    {
        $e = [];

        if ($d['name'] === '') {
            $e['name'] = 'الاسم مطلوب';
        }
        if ($d['username'] === '') {
            $e['username'] = 'اسم المستخدم مطلوب';
        } elseif (!preg_match('/^[a-zA-Z0-9_\.]{3,50}$/', $d['username'])) {
            $e['username'] = 'يجب أن يكون 3-50 حرفاً (إنجليزي، أرقام، _، .)';
        }
        if ($d['email'] && !filter_var($d['email'], FILTER_VALIDATE_EMAIL)) {
            $e['email'] = 'البريد الإلكتروني غير صالح';
        }
        if (!$d['role_id']) {
            $e['role_id'] = 'الدور مطلوب';
        }

        if ($requirePassword) {
            $pw = (string)($d['password'] ?? '');
            if (strlen($pw) < 6) {
                $e['password'] = 'كلمة المرور 6 أحرف على الأقل';
            }
        }

        return $e;
    }
public function onboardingDone(): void
{
    apiRequirePost();
    apiRequireLogin();
    apiVerifyCsrf();

    $me = currentUser();
    Database::update('users', ['onboarding_step' => 99], 'id = ?', [(int)$me['id']]);
    $_SESSION['user']['onboarding_step'] = 99;

    apiSuccess(null, '');
}

public function onboardingReset(): void
{
    apiRequirePost();
    apiRequireLogin();
    apiVerifyCsrf();

    $me = currentUser();
    Database::update('users', ['onboarding_step' => 0], 'id = ?', [(int)$me['id']]);
    $_SESSION['user']['onboarding_step'] = 0;

    apiSuccess(null, '');
}
}