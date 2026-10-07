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
        $data = $this->extract($in);
        $errors = $this->validate($data);

        if ($errors) apiError('بيانات غير صحيحة', $errors, 422);

        $id = (new Servant())->create($data);
        (new AuditLog())->write((int)currentUser()['id'], 'CREATE_SERVANT', 'servant', $id, null, $data);

        apiSuccess(['id' => $id], __('messages.servant_added'), 201);
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
        if (!$old) apiError('الخادم غير موجود', [], 404);

        $data = $this->extract($in);
        $errors = $this->validate($data, $id);
        if ($errors) apiError('بيانات غير صحيحة', $errors, 422);

        (new Servant())->update($id, $data);
        (new AuditLog())->write((int)currentUser()['id'], 'UPDATE_SERVANT', 'servant', $id, $old, $data);

        apiSuccess(['id' => $id], __('messages.servant_updated'));
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

        // هل له سجل حضور؟
        $hasAttendance = (int)(Database::one(
            "SELECT COUNT(*) AS c FROM attendance WHERE servant_id = ?",
            [$id]
        )['c'] ?? 0);

        if ($hasAttendance > 0) {
            // Soft delete: عطّل بدل الحذف
            (new Servant())->update($id, ['status' => SERVANT_INACTIVE]);

            (new AuditLog())->write(
                (int)currentUser()['id'],
                'DEACTIVATE_SERVANT',
                'servant',
                $id,
                $servant,
                ['status' => SERVANT_INACTIVE]
            );

            apiSuccess(
                ['deactivated' => true, 'records' => $hasAttendance],
                'الخادم له سجل حضور، تم تعطيله بدلاً من حذفه'
            );
        } else {
            // احذف فعلاً
            Database::query("DELETE FROM servants WHERE id = ?", [$id]);

            (new AuditLog())->write(
                (int)currentUser()['id'],
                'DELETE_SERVANT',
                'servant',
                $id,
                $servant,
                null
            );

            apiSuccess(['deleted' => true], 'تم حذف الخادم بنجاح');
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

    private function validate(array $d, ?int $id = null): array
    {
        $e = [];
        if ($d['name'] === '')  $e['name']     = __('validation.servant_name_required');
        if (!$d['choir_id'])    $e['choir_id'] = __('validation.choir_required');
        if ($d['code'] && (new Servant())->codeExists($d['code'], $id)) {
            $e['code'] = __('validation.code_exists');
        }
        return $e;
    }
}