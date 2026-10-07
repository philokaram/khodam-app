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
}