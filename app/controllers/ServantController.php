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
        if (!$servant) { http_response_code(404); exit(__('messages.not_found')); }

        $stats = (new StatisticsService())->forServant($id);
        $byActivity = (new StatisticsService())->forServantByActivity($id);

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
}