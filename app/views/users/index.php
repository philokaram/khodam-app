<div class="page-head">
    <h1 class="page-title"><?= e(__('nav.users')) ?></h1>
    <?php if (hasPermission('users.create')): ?>
        <a href="<?= e(appBaseUrl()) ?>/users/create" class="btn btn-primary">+ إضافة مستخدم</a>
    <?php endif; ?>
</div>

<?php if (empty($users)): ?>
    <div class="empty-state">لا يوجد مستخدمون.</div>
<?php else: ?>
<table class="table">
    <thead>
        <tr>
            <th>#</th>
            <th>الاسم</th>
            <th>اسم المستخدم</th>
            <th>الدور</th>
            <th>الخورس</th>
            <th>الحالة</th>
            <th>آخر دخول</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($users as $i => $u): ?>
        <tr>
            <td><?= $i + 1 ?></td>
            <td><?= e($u['name']) ?></td>
            <td><code><?= e($u['username']) ?></code></td>
            <td><span class="badge badge-sky"><?= e($u['role_label'] ?? $u['role_name'] ?? '—') ?></span></td>
            <td><?= e($u['choir_name'] ?? '—') ?></td>
            <td><span class="badge <?= $u['status'] === 'active' ? 'badge-green' : 'badge-muted' ?>">
                <?= $u['status'] === 'active' ? 'نشط' : 'غير نشط' ?>
            </span></td>
            <td><?= $u['last_login_at'] ? e(formatDateAr($u['last_login_at'], false)) : '—' ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>