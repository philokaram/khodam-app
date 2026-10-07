<?php
class ActivityController
{
    public function index(): void
    {
        requireLogin(); requirePermission('activities.view');
        view('activities/index', [
            'title' => __('nav.activities'),
            'activities' => (new Activity())->all(false),
        ]);
    }

    public function create(): void
    {
        requireLogin(); requirePermission('activities.create');
        view('activities/create', ['title' => __('activities.add')]);
    }

    public function store(): void
    {
        requireLogin(); requirePermission('activities.create'); verifyCsrf();
        $name = trim($_POST['name'] ?? '');
        if (!vRequired($name)) {
            $_SESSION['_errors'] = [__('validation.activity_name_required')];
            redirect('/activities/create');
        }
        $id = (new Activity())->create([
            'name' => $name,
            'description' => trim($_POST['description'] ?? '') ?: null,
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
        ]);
        (new AuditLog())->write((int)currentUser()['id'], 'CREATE_ACTIVITY', 'activity', $id);
        $_SESSION['_success'] = __('messages.activity_added');
        redirect('/activities');
    }

    public function edit(): void
    {
        requireLogin(); requirePermission('activities.edit');
        $id = (int)($_GET['id'] ?? 0);
        $activity = (new Activity())->find($id);
        if (!$activity) { http_response_code(404); exit(__('messages.not_found')); }
        view('activities/edit', ['title' => __('activities.edit'), 'activity' => $activity]);
    }

    public function update(): void
    {
        requireLogin(); requirePermission('activities.edit'); verifyCsrf();
        $id = (int)($_POST['id'] ?? 0);
        $old = (new Activity())->find($id);
        if (!$old) { http_response_code(404); exit(__('messages.not_found')); }
        $data = [
            'name' => trim($_POST['name'] ?? ''),
            'description' => trim($_POST['description'] ?? '') ?: null,
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
        ];
        (new Activity())->update($id, $data);
        (new AuditLog())->write((int)currentUser()['id'], 'UPDATE_ACTIVITY', 'activity', $id, $old, $data);
        $_SESSION['_success'] = __('messages.activity_updated');
        redirect('/activities');
    }
    
    public function delete(): void
{
    apiRequirePost();
    apiRequireLogin();
    apiRequirePermission('activities.delete');
    apiVerifyCsrf();

    $in = apiInput();
    $id = (int)($in['id'] ?? 0);

    if (!$id) {
        apiError('معرّف النشاط مطلوب', [], 422);
    }

    $activity = (new Activity())->find($id);
    if (!$activity) {
        apiError('النشاط غير موجود', [], 404);
    }

    // فحص: هل له جلسات حضور؟
    $sessionsCount = (int)(Database::one(
        "SELECT COUNT(*) AS c FROM attendance_sessions WHERE activity_id = ?",
        [$id]
    )['c'] ?? 0);

    if ($sessionsCount > 0) {
        apiError(
            "لا يمكن حذف النشاط — له {$sessionsCount} جلسة حضور مسجلة",
            ['sessions_count' => $sessionsCount],
            409
        );
    }

    Database::query("DELETE FROM activities WHERE id = ?", [$id]);

    (new AuditLog())->write(
        (int)currentUser()['id'],
        'DELETE_ACTIVITY',
        'activity',
        $id,
        $activity,
        null
    );

    apiSuccess(null, 'تم حذف النشاط بنجاح');
}
}