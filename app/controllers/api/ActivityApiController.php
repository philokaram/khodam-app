<?php
class ActivityApiController
{
    public function index(): void
    {
        apiRequireLogin();
        apiRequirePermission('activities.view');
        apiSuccess(['activities' => (new Activity())->all(false)]);
    }

    public function create(): void
    {
        apiRequirePost();
        apiRequireLogin();
        apiRequirePermission('activities.create');
        apiVerifyCsrf();

        $in = apiInput();
        $name = trim((string)($in['name'] ?? ''));

        if ($name === '') {
            apiError('بيانات غير صحيحة', ['name' => 'يرجى إدخال اسم النشاط'], 422);
        }

        $id = (new Activity())->create([
            'name'        => $name,
            'description' => trim((string)($in['description'] ?? '')) ?: null,
            'is_active'   => !empty($in['is_active']) ? 1 : 0,
        ]);

        (new AuditLog())->write((int)currentUser()['id'], 'CREATE_ACTIVITY', 'activity', $id, null, [
            'name' => $name,
        ]);

        apiSuccess(['id' => $id], 'تم إضافة النشاط بنجاح', 201);
    }

    public function update(): void
    {
        apiRequirePost();
        apiRequireLogin();
        apiRequirePermission('activities.edit');
        apiVerifyCsrf();

        $in = apiInput();
        $id = (int)($in['id'] ?? 0);

        $old = (new Activity())->find($id);
        if (!$old) {
            apiError('النشاط غير موجود', [], 404);
        }

        $name = trim((string)($in['name'] ?? ''));
        if ($name === '') {
            apiError('بيانات غير صحيحة', ['name' => 'يرجى إدخال اسم النشاط'], 422);
        }

        $data = [
            'name'        => $name,
            'description' => trim((string)($in['description'] ?? '')) ?: null,
            'is_active'   => !empty($in['is_active']) ? 1 : 0,
        ];

        (new Activity())->update($id, $data);

        (new AuditLog())->write(
            (int)currentUser()['id'],
            'UPDATE_ACTIVITY',
            'activity',
            $id,
            $old,
            $data
        );

        apiSuccess(['id' => $id], 'تم تعديل النشاط بنجاح');
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