<?php
class ChoirController
{
    public function index(): void
    {
        requireLogin(); requirePermission('choirs.view');
        view('choirs/index', [
            'title'  => __('nav.choirs'),
            'choirs' => (new Choir())->all(false),
        ]);
    }

    public function create(): void
    {
        requireLogin(); requirePermission('choirs.create');
        view('choirs/create', ['title' => __('choirs.add')]);
    }

    public function store(): void
    {
        requireLogin(); requirePermission('choirs.create'); verifyCsrf();
        $name = trim($_POST['name'] ?? '');
        if (!vRequired($name)) {
            $_SESSION['_errors'] = [__('validation.choir_name_required')];
            redirect('/choirs/create');
        }
        $id = (new Choir())->create([
            'name' => $name,
            'description' => trim($_POST['description'] ?? '') ?: null,
            'is_active'   => isset($_POST['is_active']) ? 1 : 0,
        ]);
        (new AuditLog())->write((int)currentUser()['id'], 'CREATE_CHOIR', 'choir', $id);
        $_SESSION['_success'] = __('messages.choir_added');
        redirect('/choirs');
    }

    public function edit(): void
    {
        requireLogin(); requirePermission('choirs.edit');
        $id = (int)($_GET['id'] ?? 0);
        $choir = (new Choir())->find($id);
        if (!$choir) { http_response_code(404); exit(__('messages.not_found')); }
        view('choirs/edit', ['title' => __('choirs.edit'), 'choir' => $choir]);
    }

    public function update(): void
    {
        requireLogin(); requirePermission('choirs.edit'); verifyCsrf();
        $id = (int)($_POST['id'] ?? 0);
        $old = (new Choir())->find($id);
        if (!$old) { http_response_code(404); exit(__('messages.not_found')); }
        $data = [
            'name' => trim($_POST['name'] ?? ''),
            'description' => trim($_POST['description'] ?? '') ?: null,
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
        ];
        (new Choir())->update($id, $data);
        (new AuditLog())->write((int)currentUser()['id'], 'UPDATE_CHOIR', 'choir', $id, $old, $data);
        $_SESSION['_success'] = __('messages.choir_updated');
        redirect('/choirs');
    }
    
    public function delete(): void
{
    apiRequirePost();
    apiRequireLogin();
    apiRequirePermission('choirs.delete');
    apiVerifyCsrf();

    $in = apiInput();
    $id = (int)($in['id'] ?? 0);

    if (!$id) {
        apiError('معرّف الخورس مطلوب', [], 422);
    }

    $choir = (new Choir())->find($id);
    if (!$choir) {
        apiError('الخورس غير موجود', [], 404);
    }

    // ⚠️ فحص: هل فيه خدام؟
    $servantsCount = (int)(Database::one(
        "SELECT COUNT(*) AS c FROM servants WHERE choir_id = ?",
        [$id]
    )['c'] ?? 0);

    if ($servantsCount > 0) {
        apiError(
            "لا يمكن حذف الخورس — يحتوي على {$servantsCount} خادم",
            ['servants_count' => $servantsCount],
            409
        );
    }

    // فحص: هل فيه جلسات حضور؟
    $sessionsCount = (int)(Database::one(
        "SELECT COUNT(*) AS c FROM attendance_sessions WHERE choir_id = ?",
        [$id]
    )['c'] ?? 0);

    if ($sessionsCount > 0) {
        apiError(
            "لا يمكن حذف الخورس — له {$sessionsCount} جلسة حضور مسجلة",
            ['sessions_count' => $sessionsCount],
            409
        );
    }

    // فحص: هل مرتبط بمستخدمين؟
    $usersCount = (int)(Database::one(
        "SELECT COUNT(*) AS c FROM users WHERE choir_id = ?",
        [$id]
    )['c'] ?? 0);

    if ($usersCount > 0) {
        apiError(
            "لا يمكن حذف الخورس — مرتبط بـ{$usersCount} مستخدم",
            ['users_count' => $usersCount],
            409
        );
    }

    // الآن آمن للحذف
    Database::query("DELETE FROM choirs WHERE id = ?", [$id]);
    (new AuditLog())->write(
        (int)currentUser()['id'],
        'DELETE_CHOIR',
        'choir',
        $id,
        $choir,
        null
    );

    apiSuccess(null, 'تم حذف الخورس بنجاح');
}
}