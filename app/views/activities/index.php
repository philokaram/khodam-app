<div class="page-head">
    <h1 class="page-title"><?= e(__('nav.activities')) ?></h1>
    <?php if (hasPermission('activities.create')): ?>
        <a href="<?= e(appBaseUrl()) ?>/activities/create" class="btn btn-primary">
            + <?= e(__('activities.add')) ?>
        </a>
    <?php endif; ?>
</div>

<?php if (empty($activities)): ?>
    <div class="empty-state"><?= e(__('messages.no_data')) ?></div>
<?php else: ?>
<table class="table">
    <thead>
        <tr>
            <th>#</th>
            <th><?= e(__('activities.name')) ?></th>
            <th>الوصف</th>
            <th>الحالة</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($activities as $i => $a): ?>
        <tr>
            <td><?= $i + 1 ?></td>
            <td><?= e($a['name']) ?></td>
            <td><?= e($a['description'] ?? '—') ?></td>
            <td>
                <span class="badge <?= $a['is_active'] ? 'badge-green' : 'badge-muted' ?>">
                    <?= $a['is_active'] ? 'نشط' : 'غير نشط' ?>
                </span>
            </td>
            <td>
                <?php if (hasPermission('activities.edit')): ?>
                    <a href="<?= e(appBaseUrl()) ?>/activities/edit?id=<?= (int)$a['id'] ?>" class="btn btn-sm">تعديل</a>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>