<?php
class ServantApiController
{
    public function index(): void
    {
        apiRequireLogin();
        apiRequirePermission('servants.view');

        $filters = [
            'choir_id' => $_GET['choir_id'] ?? null,
            'status'   => $_GET['status']   ?? null,
            'search'   => $_GET['search']   ?? null,
        ];
        apiSuccess(['servants' => (new Servant())->allWithChoir($filters)]);
    }

 public function create(): void
{
    apiRequirePost();
    apiRequireLogin();
    apiRequirePermission('servants.create');
    apiVerifyCsrf();

    $in = apiInput();

    $data = [
        'name'      => trim((string)($in['name'] ?? '')),
        'emoji'     => trim((string)($in['emoji'] ?? '👤')) ?: '👤',
        'choir_id'  => (int)($in['choir_id'] ?? 0),
        'code'      => trim((string)($in['code'] ?? '')) ?: null,
        'phone'     => trim((string)($in['phone'] ?? '')) ?: null,
        'join_date' => ($in['join_date'] ?? '') ?: null,
        'status'    => SERVANT_ACTIVE,
    ];

    $username = trim((string)($in['username'] ?? ''));
    $password = (string)($in['password'] ?? '');

    // CHOIR_ADMIN: يُجبر على خورسه
    if (isChoirAdmin()) {
        $data['choir_id'] = (int)currentUser()['choir_id'];
    }

    $errors = $this->validateServant($data);
    if ($errors) {
        apiError('بيانات غير صحيحة', $errors, 422);
    }

    // التحقق من حساب الدخول
    if ($username === '' || strlen($username) < 3) {
        apiError('اسم المستخدم مطلوب (3 أحرف على الأقل)', ['username' => 'مطلوب'], 422);
    }
    if (!preg_match('/^[a-zA-Z0-9_\.]{3,50}$/', $username)) {
        apiError('اسم المستخدم غير صالح', ['username' => 'يجب أن يكون 3-50 حرفاً إنجليزياً/أرقام/_.'], 422);
    }
    if (strlen($password) < 6) {
        apiError('كلمة المرور يجب أن تكون 6 أحرف على الأقل', ['password' => 'قصيرة'], 422);
    }

    // تكرار username
    $exists = Database::one("SELECT id FROM users WHERE username = ?", [$username]);
    if ($exists) {
        apiError('اسم المستخدم مستخدم بالفعل', ['username' => 'مستخدم'], 422);
    }

    // تكرار code
    if ($data['code'] && (new Servant())->codeExists($data['code'])) {
        apiError('الكود مستخدم بالفعل', ['code' => 'مستخدم'], 422);
    }

    // ✅ استخدم transaction لضمان الاتساق
    $pdo = Database::pdo();
    $pdo->beginTransaction();

    try {
        // 1. أنشئ الخادم
        $servantId = (new Servant())->create($data);

        // 2. أنشئ المستخدم المرتبط
        $userId = Database::insert('users', [
            'name'         => $data['name'],
            'username'     => $username,
            'password_hash'=> password_hash($password, PASSWORD_BCRYPT),
            'role_id'      => 5, // SERVANT
            'choir_id'     => $data['choir_id'],
            'servant_id'   => $servantId,
            'status'       => 'active',
        ]);

        // 3. Audit log
        (new AuditLog())->write(
            (int)currentUser()['id'],
            'CREATE_SERVANT_WITH_USER',
            'servant',
            $servantId,
            null,
            [
                'servant' => $data,
                'user_id' => $userId,
                'username'=> $username,
            ]
        );

        $pdo->commit();

        apiSuccess(
            [
                'servant_id' => $servantId,
                'user_id'    => $userId,
                'username'   => $username,
                'password'   => $password,
            ],
            'تم إضافة الخادم وإنشاء حساب الدخول بنجاح',
            201
        );

    } catch (Throwable $e) {
        $pdo->rollBack();
        error_log('[create servant] ' . $e->getMessage());
        apiError('فشل إنشاء الخادم: ' . $e->getMessage(), [], 500);
    }
}
    public function parseImport(): void
{
    apiRequirePost();
    apiRequireLogin();
    apiRequirePermission('servants.create');
    apiVerifyCsrf();

    if (empty($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        apiError('لم يتم رفع الملف', [], 422);
    }

    $file = $_FILES['file'];

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if ($ext !== 'csv') {
        apiError('الملف يجب أن يكون CSV', [], 422);
    }

    if ($file['size'] > 2 * 1024 * 1024) {
        apiError('حجم الملف أكبر من 2 ميجابايت', [], 422);
    }

    $tmpPath = STORAGE_PATH . '/uploads/import-' . uniqid() . '.csv';
    if (!is_dir(dirname($tmpPath))) {
        @mkdir(dirname($tmpPath), 0755, true);
    }
    if (!move_uploaded_file($file['tmp_name'], $tmpPath)) {
        apiError('فشل رفع الملف', [], 500);
    }

    try {
        $result = (new ImportService())->parseServantsCsv($tmpPath);
        @unlink($tmpPath);
        apiSuccess($result);
    } catch (Throwable $e) {
        @unlink($tmpPath);
        error_log('[parseImport] ' . $e->getMessage());
        apiError('فشل تحليل الملف: ' . $e->getMessage(), [], 500);
    }
}

public function confirmImport(): void
{
    apiRequirePost();
    apiRequireLogin();
    apiRequirePermission('servants.create');
    apiVerifyCsrf();

    $in = apiInput();
    $rows = $in['rows'] ?? [];

    if (!is_array($rows) || empty($rows)) {
        apiError('لا توجد بيانات للاستيراد', [], 422);
    }

    try {
        $result = (new ImportService())->importServants($rows);

        if ($result['imported'] === 0) {
            apiError('لم يتم استيراد أي خادم', $result['errors'], 422);
        }

        apiSuccess(
            $result,
            "تم استيراد {$result['imported']} خادم بنجاح" .
            ($result['skipped'] > 0 ? " (تم تخطي {$result['skipped']})" : '')
        );
    } catch (Throwable $e) {
        error_log('[confirmImport] ' . $e->getMessage());
        apiError('فشل الاستيراد: ' . $e->getMessage(), [], 500);
    }
}

private function validateServant(array $d, ?int $id = null): array
{
    $e = [];
    if ($d['name'] === '') {
        $e['name'] = __('validation.servant_name_required');
    }
    if (!$d['choir_id']) {
        $e['choir_id'] = __('validation.choir_required');
    }
    if (!empty($d['code']) && (new Servant())->codeExists($d['code'], $id)) {
        $e['code'] = __('validation.code_exists');
    }
    return $e;
}

 public function update(): void
{
    apiRequirePost();
    apiRequireLogin();
    apiRequirePermission('servants.edit');
    apiVerifyCsrf();

    $in = apiInput();
    $id = (int)($in['id'] ?? 0);

    $old = (new Servant())->find($id);
    if (!$old) {
        apiError('الخادم غير موجود', [], 404);
    }

    $data = [
        'name'      => trim((string)($in['name'] ?? '')),
        'emoji'     => trim((string)($in['emoji'] ?? '👤')) ?: '👤',
        'choir_id'  => (int)($in['choir_id'] ?? 0),
        'code'      => trim((string)($in['code'] ?? '')) ?: null,
        'phone'     => trim((string)($in['phone'] ?? '')) ?: null,
        'join_date' => ($in['join_date'] ?? '') ?: null,
        'status'    => in_array($in['status'] ?? '', [SERVANT_ACTIVE, SERVANT_INACTIVE], true)
                        ? $in['status'] : SERVANT_ACTIVE,
    ];

    if (isChoirAdmin()) {
        $data['choir_id'] = (int)currentUser()['choir_id'];
    }

    $errors = $this->validateServant($data, $id);
    if ($errors) {
        apiError('بيانات غير صحيحة', $errors, 422);
    }

    $pdo = Database::pdo();
    $pdo->beginTransaction();

    try {
        // 1. حدّث الخادم
        (new Servant())->update($id, $data);

        // 2. حدّث المستخدم المرتبط (الاسم + الخورس)
        $linkedUser = Database::one("SELECT id FROM users WHERE servant_id = ?", [$id]);
        if ($linkedUser) {
            Database::update('users', [
                'name'     => $data['name'],
                'choir_id' => $data['choir_id'],
                'status'   => $data['status'] === SERVANT_ACTIVE ? 'active' : 'inactive',
            ], 'id = ?', [(int)$linkedUser['id']]);
        }

        (new AuditLog())->write(
            (int)currentUser()['id'],
            'UPDATE_SERVANT',
            'servant',
            $id,
            $old,
            $data
        );

        $pdo->commit();
        apiSuccess(['id' => $id], __('messages.servant_updated'));

    } catch (Throwable $e) {
        $pdo->rollBack();
        apiError('فشل التحديث: ' . $e->getMessage(), [], 500);
    }
}
    /* ============================================================
       DELETE — نسخة واحدة فقط
       - إن كان للخادم سجل حضور → تعطيل (soft delete)
       - إن لم يكن → حذف فعلي
    ============================================================ */
 public function delete(): void
{
    apiRequirePost();
    apiRequireLogin();
    apiRequirePermission('servants.delete');
    apiVerifyCsrf();

    $in = apiInput();
    $id = (int)($in['id'] ?? 0);

    if (!$id) {
        apiError('معرّف الخادم مطلوب', [], 422);
    }

    $servant = (new Servant())->find($id);
    if (!$servant) {
        apiError('الخادم غير موجود', [], 404);
    }

    $hasAttendance = (int)(Database::one(
        "SELECT COUNT(*) AS c FROM attendance WHERE servant_id = ?",
        [$id]
    )['c'] ?? 0);

    $pdo = Database::pdo();
    $pdo->beginTransaction();

    try {
        if ($hasAttendance > 0) {
            // Soft delete: عطّل الخادم + المستخدم
            (new Servant())->update($id, ['status' => SERVANT_INACTIVE]);
            Database::update('users', ['status' => 'inactive'], 'servant_id = ?', [$id]);

            (new AuditLog())->write(
                (int)currentUser()['id'],
                'DEACTIVATE_SERVANT',
                'servant',
                $id,
                $servant,
                ['status' => SERVANT_INACTIVE]
            );

            $pdo->commit();
            apiSuccess(
                ['deactivated' => true, 'records' => $hasAttendance],
                'الخادم له سجل حضور، تم تعطيله وحسابه بدلاً من حذفه'
            );
        } else {
            // احذف المستخدم المرتبط أولاً
            Database::query("DELETE FROM users WHERE servant_id = ?", [$id]);

            // ثم احذف الخادم
            Database::query("DELETE FROM servants WHERE id = ?", [$id]);

            (new AuditLog())->write(
                (int)currentUser()['id'],
                'DELETE_SERVANT',
                'servant',
                $id,
                $servant,
                null
            );

            $pdo->commit();
            apiSuccess(['deleted' => true], 'تم حذف الخادم وحساب الدخول');
        }

    } catch (Throwable $e) {
        $pdo->rollBack();
        apiError('فشل الحذف: ' . $e->getMessage(), [], 500);
    }
}

    /* ============================================================
       Helpers
    ============================================================ */
    private function extract(array $in): array
    {
        return [
            'name'      => trim((string)($in['name'] ?? '')),
            'choir_id'  => (int)($in['choir_id'] ?? 0),
            'code'      => trim((string)($in['code'] ?? '')) ?: null,
            'phone'     => trim((string)($in['phone'] ?? '')) ?: null,
            'join_date' => ($in['join_date'] ?? '') ?: null,
            'status'    => in_array($in['status'] ?? '', [SERVANT_ACTIVE, SERVANT_INACTIVE], true)
                ? $in['status'] : SERVANT_ACTIVE,
        ];
    }


}