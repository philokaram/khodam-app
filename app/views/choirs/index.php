<div class="page-head">
    <h1 class="page-title"><?= e(__('nav.choirs')) ?></h1>
    <?php if (hasPermission('choirs.create')): ?>
        <a href="<?= e(appBaseUrl()) ?>/choirs/create" class="btn btn-primary">
            + <?= e(__('choirs.add')) ?>
        </a>
    <?php endif; ?>
</div>

<?php if (empty($choirs)): ?>
    <div class="empty-state"><?= e(__('messages.no_data')) ?></div>
<?php else: ?>
<table class="table">
    <thead>
        <tr>
            <th>#</th>
            <th><?= e(__('choirs.name')) ?></th>
            <th>الوصف</th>
            <th>عدد الخدام</th>
            <th>الحالة</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($choirs as $i => $c): ?>
        <tr>
            <td><?= $i + 1 ?></td>
            <td><?= e($c['name']) ?></td>
            <td><?= e($c['description'] ?? '—') ?></td>
            <td>
                <?php
                $cnt = Database::one("SELECT COUNT(*) AS c FROM servants WHERE choir_id = ?", [(int)$c['id']])['c'] ?? 0;
                echo (int)$cnt;
                ?>
            </td>
            <td>
                <span class="badge <?= $c['is_active'] ? 'badge-green' : 'badge-muted' ?>">
                    <?= $c['is_active'] ? 'نشط' : 'غير نشط' ?>
                </span>
            </td>
            <td>
                <?php if (hasPermission('choirs.edit')): ?>
                    <a href="<?= e(appBaseUrl()) ?>/choirs/edit?id=<?= (int)$c['id'] ?>" class="btn btn-sm">تعديل</a>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>