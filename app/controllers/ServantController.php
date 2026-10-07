<?php
class ServantController
{
    public function index(): void
    {
        requireLogin();
        requirePermission('servants.view');
        $filters = [
            'choir_id' => $_GET['choir_id'] ?? null,
            'status'   => $_GET['status']   ?? null,
            'search'   => $_GET['search']   ?? null,
        ];
        view('servants/index', [
            'title'    => __('nav.servants'),
            'servants' => (new Servant())->allWithChoir($filters),
            'choirs'   => (new Choir())->all(),
            'filters'  => $filters,
        ]);
    }

    public function create(): void
    {
        requireLogin();
        requirePermission('servants.create');
        view('servants/create', [
            'title'  => __('servants.add'),
            'choirs' => (new Choir())->all(),
        ]);
    }

    public function store(): void
    {
        requireLogin();
        requirePermission('servants.create');
        verifyCsrf();

        $data = [
            'name'      => trim($_POST['name'] ?? ''),
            'choir_id'  => (int)($_POST['choir_id'] ?? 0),
            'code'      => trim($_POST['code'] ?? '') ?: null,
            'phone'     => trim($_POST['phone'] ?? '') ?: null,
            'join_date' => $_POST['join_date'] ?: null,
            'status'    => $_POST['status'] ?? SERVANT_ACTIVE,
        ];
        $emojis = ['😀', '😎', '🥳', '🤓', '😇', '🙂', '😄', '😊'];
$data['emoji'] = $_POST['emoji'] ?? $emojis[array_rand($emojis)];

        $errors = $this->validate($data);
        if ($errors) {
            $_SESSION['_errors'] = $errors;
            $_SESSION['_old'] = $data;
            redirect('/servants/create');
        }

        $id = (new Servant())->create($data);
        (new AuditLog())->write((int)currentUser()['id'], 'CREATE_SERVANT', 'servant', $id, null, $data);

        $_SESSION['_success'] = __('messages.servant_added');
        redirect('/servants');
    }

    public function edit(): void
    {
        requireLogin();
        requirePermission('servants.edit');
        $id = (int)($_GET['id'] ?? 0);
        $servant = (new Servant())->find($id);
        if (!$servant) { http_response_code(404); exit(__('messages.not_found')); }
        view('servants/edit', [
            'title'   => __('servants.edit'),
            'servant' => $servant,
            'choirs'  => (new Choir())->all(),
        ]);
    }

    public function update(): void
    {
        requireLogin();
        requirePermission('servants.edit');
        verifyCsrf();

        $id = (int)($_POST['id'] ?? 0);
        $old = (new Servant())->find($id);
        if (!$old) { http_response_code(404); exit(__('messages.not_found')); }

        $data = [
            'name'      => trim($_POST['name'] ?? ''),
            'choir_id'  => (int)($_POST['choir_id'] ?? 0),
            'code'      => trim($_POST['code'] ?? '') ?: null,
            'phone'     => trim($_POST['phone'] ?? '') ?: null,
            'join_date' => $_POST['join_date'] ?: null,
            'status'    => $_POST['status'] ?? SERVANT_ACTIVE,
        ];

        $errors = $this->validate($data, $id);
        if ($errors) {
            $_SESSION['_errors'] = $errors;
            redirect('/servants/edit?id=' . $id);
        }

        (new Servant())->update($id, $data);
        (new AuditLog())->write((int)currentUser()['id'], 'UPDATE_SERVANT', 'servant', $id, $old, $data);

        $_SESSION['_success'] = __('messages.servant_updated');
        redirect('/servants');
    }

    public function show(): void
{
    requireLogin();
    requirePermission('servants.view');

    $id = (int)($_GET['id'] ?? 0);
    $servant = (new Servant())->find($id);
    if (!$servant) {
        http_response_code(404);
        exit(__('messages.not_found'));
    }

    $svc = new StatisticsService();
    $stats = $svc->forServant($id);
    $byActivity = $svc->forServantByActivity($id);

    view('servants/show', [
        'title'      => $servant['name'],
        'servant'    => $servant,
        'stats'      => $stats,
        'byActivity' => $byActivity,
    ]);
}
    private function validate(array $d, ?int $id = null): array
    {
        $e = [];
        if (!vRequired($d['name']))     $e[] = __('validation.servant_name_required');
        if (!$d['choir_id'])            $e[] = __('validation.choir_required');
        if ($d['code'] && (new Servant())->codeExists($d['code'], $id)) {
            $e[] = __('validation.code_exists');
        }
        return $e;
    }
    
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

    // ⚠️ Soft delete: تعطيل بدل حذف
    // هذا يحفظ سجل الحضور التاريخي
    
    $hasAttendance = (int)(Database::one(
        "SELECT COUNT(*) AS c FROM attendance WHERE servant_id = ?",
        [$id]
    )['c'] ?? 0);

    if ($hasAttendance > 0) {
        // عطّله بدل حذفه
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
        // لا يوجد سجل → احذفه فعلاً
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
}