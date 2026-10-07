<div class="page-header">
    <div class="page-header-text">
        <h1><?= e(__('nav.choirs')) ?></h1>
        <p>إدارة الخُوَرَس والمجموعات</p>
    </div>
    <div class="page-header-actions">
        <?php if (hasPermission('choirs.create')): ?>
            <a href="<?= e(appBaseUrl()) ?>/choirs/create" class="btn btn-primary">
                + <?= e(__('choirs.add')) ?>
            </a>
        <?php endif; ?>
    </div>
</div>

<?php if (empty($choirs)): ?>
    <div class="empty-state">
        <h3 style="margin:0 0 8px;color:var(--text-primary)">لا يوجد خُوَرَس</h3>
        <p style="margin:0 0 16px">ابدأ بإضافة خورس جديد</p>
        <?php if (hasPermission('choirs.create')): ?>
            <a href="<?= e(appBaseUrl()) ?>/choirs/create" class="btn btn-primary">
                + إضافة خورس
            </a>
        <?php endif; ?>
    </div>
<?php else: ?>

    <div class="table-wrapper">
        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th><?= e(__('choirs.name')) ?></th>
                    <th>الوصف</th>
                    <th>عدد الخدام</th>
                    <th>الحالة</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($choirs as $i => $c):
                $cnt = Database::one("SELECT COUNT(*) AS c FROM servants WHERE choir_id = ? AND status = 'active'", [(int)$c['id']])['c'] ?? 0;
            ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><strong><?= e($c['name']) ?></strong></td>
                    <td><?= e($c['description'] ?? '—') ?></td>
                    <td>
                        <span class="badge badge-info"><?= (int)$cnt ?> خادم</span>
                    </td>
                    <td>
                        <span class="badge <?= $c['is_active'] ? 'badge-success' : 'badge-neutral' ?>">
                            <?= $c['is_active'] ? 'نشط' : 'غير نشط' ?>
                        </span>
                    </td>
                    <td>
                        <div style="display:flex;gap:6px;flex-wrap:wrap">
                            <?php if (hasPermission('choirs.edit')): ?>
                                <a href="<?= e(appBaseUrl()) ?>/choirs/edit?id=<?= (int)$c['id'] ?>"
                                   class="btn btn-sm">تعديل</a>
                            <?php endif; ?>

                            <?php if (hasPermission('choirs.delete')): ?>
                                <button type="button"
                                        class="btn btn-sm btn-danger"
                                        onclick="deleteChoir(<?= (int)$c['id'] ?>, '<?= e(addslashes($c['name'])) ?>')"
                                        title="حذف الخورس">
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
function deleteChoir(id, name) {
    deleteItem({
        url: window.APP_URL + '/api/choirs/delete',
        id: id,
        title: 'حذف الخورس',
        message: 'هل أنت متأكد من حذف "' + name + '"؟',
        extra: '<div style="padding:10px;background:var(--danger-soft);border-radius:8px;font-size:13px;color:var(--danger-text);line-height:1.5">' +
               '⚠️ لا يمكن حذف خورس يحتوي على خدام أو جلسات حضور مسجلة.' +
               '</div>'
    });
}
</script>