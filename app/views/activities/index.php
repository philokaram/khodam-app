<div class="page-header">
    <div class="page-header-text">
        <h1><?= e(__('nav.activities')) ?></h1>
        <p>إدارة الأنشطة والاجتماعات</p>
    </div>
    <div class="page-header-actions">
        <?php if (hasPermission('activities.create')): ?>
            <a href="<?= e(appBaseUrl()) ?>/activities/create" class="btn btn-primary">
                + <?= e(__('activities.add')) ?>
            </a>
        <?php endif; ?>
    </div>
</div>

<?php if (empty($activities)): ?>
    <div class="empty-state">
        <h3 style="margin:0 0 8px;color:var(--text-primary)">لا يوجد أنشطة</h3>
        <p style="margin:0 0 16px">ابدأ بإضافة نشاط جديد</p>
        <?php if (hasPermission('activities.create')): ?>
            <a href="<?= e(appBaseUrl()) ?>/activities/create" class="btn btn-primary">
                + إضافة نشاط
            </a>
        <?php endif; ?>
    </div>
<?php else: ?>

    <div class="table-wrapper">
        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th><?= e(__('activities.name')) ?></th>
                    <th>الوصف</th>
                    <th>الحالة</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($activities as $i => $a): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><strong><?= e($a['name']) ?></strong></td>
                    <td><?= e($a['description'] ?? '—') ?></td>
                    <td>
                        <span class="badge <?= $a['is_active'] ? 'badge-success' : 'badge-neutral' ?>">
                            <?= $a['is_active'] ? 'نشط' : 'غير نشط' ?>
                        </span>
                    </td>
                    <td>
                        <div style="display:flex;gap:6px;flex-wrap:wrap">
                            <?php if (hasPermission('activities.edit')): ?>
                                <a href="<?= e(appBaseUrl()) ?>/activities/edit?id=<?= (int)$a['id'] ?>"
                                   class="btn btn-sm">تعديل</a>
                            <?php endif; ?>

                            <?php if (hasPermission('activities.delete')): ?>
                                <button type="button"
                                        class="btn btn-sm btn-danger"
                                        onclick="deleteActivity(<?= (int)$a['id'] ?>, '<?= e(addslashes($a['name'])) ?>')"
                                        title="حذف النشاط">
                                    🗑 حذف
                                </button>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

<?php endif; ?>

<script>
function deleteActivity(id, name) {
    deleteItem({
        url: window.APP_URL + '/api/activities/delete',
        id: id,
        title: 'حذف النشاط',
        message: 'هل أنت متأكد من حذف "' + name + '"؟',
        extra: '<div style="padding:10px;background:var(--danger-soft);border-radius:8px;font-size:13px;color:var(--danger-text);line-height:1.5">' +
               '⚠️ لا يمكن حذف نشاط له جلسات حضور مسجلة.' +
               '</div>'
    });
}
</script>