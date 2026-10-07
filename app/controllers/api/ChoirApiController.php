<?php
class ChoirApiController
{
    public function index(): void
    {
        apiRequireLogin();
        apiRequirePermission('choirs.view');
        apiSuccess(['choirs' => (new Choir())->all(false)]);
    }

    public function create(): void
    {
        apiRequirePost();
        apiRequireLogin();
        apiRequirePermission('choirs.create');
        apiVerifyCsrf();

        $in = apiInput();
        $name = trim((string)($in['name'] ?? ''));

        if ($name === '') {
            apiError('بيانات غير صحيحة', ['name' => 'يرجى إدخال اسم الخورس'], 422);
        }

        $id = (new Choir())->create([
            'name'        => $name,
            'description' => trim((string)($in['description'] ?? '')) ?: null,
            'is_active'   => !empty($in['is_active']) ? 1 : 0,
        ]);

        (new AuditLog())->write((int)currentUser()['id'], 'CREATE_CHOIR', 'choir', $id, null, [
            'name' => $name,
        ]);

        apiSuccess(['id' => $id], 'تم إضافة الخورس بنجاح', 201);
    }

    public function update(): void
    {
        apiRequirePost();
        apiRequireLogin();
        apiRequirePermission('choirs.edit');
        apiVerifyCsrf();

        $in = apiInput();
        $id = (int)($in['id'] ?? 0);

        $old = (new Choir())->find($id);
        if (!$old) {
            apiError('الخورس غير موجود', [], 404);
        }

        $name = trim((string)($in['name'] ?? ''));
        if ($name === '') {
            apiError('بيانات غير صحيحة', ['name' => 'يرجى إدخال اسم الخورس'], 422);
        }

        $data = [
            'name'        => $name,
            'description' => trim((string)($in['description'] ?? '')) ?: null,
            'is_active'   => !empty($in['is_active']) ? 1 : 0,
        ];

        (new Choir())->update($id, $data);

        (new AuditLog())->write(
            (int)currentUser()['id'],
            'UPDATE_CHOIR',
            'choir',
            $id,
            $old,
            $data
        );

        apiSuccess(['id' => $id], 'تم تعديل الخورس بنجاح');
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

        // 1. هل فيه خدام؟
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

        // 2. هل فيه جلسات حضور؟
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

        // 3. هل مرتبط بمستخدمين؟
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