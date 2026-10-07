<div class="page-header">
    <div class="page-header-text">
        <h1><?= e(__('nav.servants')) ?></h1>
        <p>
            <?php if (isChoirAdmin()): ?>
                خدام خورس <?= e(currentUser()['choir_name'] ?? '') ?>
            <?php else: ?>
                إدارة الخدام المسجلين في النظام
            <?php endif; ?>
        </p>
    </div>
<div class="page-header-actions">
    <?php if (hasPermission('servants.create')): ?>
        <a href="<?= e(appBaseUrl()) ?>/servants/import" class="btn">
            📥 استيراد من Excel
        </a>
    <?php endif; ?>
    <?php if (hasPermission('reports.export')): ?>
        <button type="button" class="btn" onclick="exportServants()">
            📤 تصدير Excel
        </button>
    <?php endif; ?>
    <?php if (hasPermission('servants.create')): ?>
        <a href="<?= e(appBaseUrl()) ?>/servants/create" class="btn btn-primary">
            + <?= e(__('servants.add')) ?>
        </a>
    <?php endif; ?>
</div>
</div>

<form method="get" class="filters-bar">
    <label>
        <span>بحث</span>
        <input type="search" name="search" value="<?= e($filters['search'] ?? '') ?>"
               placeholder="<?= e(__('servants.search')) ?>">
    </label>

    <?php if (!isChoirAdmin()): ?>
    <label>
        <span><?= e(__('servants.choir')) ?></span>
        <select name="choir_id">
            <option value="">الكل</option>
            <?php foreach ($choirs as $c): ?>
                <option value="<?= (int)$c['id'] ?>" <?= ($filters['choir_id'] == $c['id']) ? 'selected' : '' ?>>
                    <?= e($c['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </label>
    <?php endif; ?>

    <label>
        <span><?= e(__('servants.status')) ?></span>
        <select name="status">
            <option value="">الكل</option>
            <option value="active" <?= ($filters['status'] === 'active') ? 'selected' : '' ?>>نشط</option>
            <option value="inactive" <?= ($filters['status'] === 'inactive') ? 'selected' : '' ?>>غير نشط</option>
        </select>
    </label>
    <button type="submit" class="btn btn-primary">تطبيق</button>
</form>

<?php if (empty($servants)): ?>
    <div class="empty-state">
        <h3 style="margin:0 0 8px;color:var(--text-primary)">
            <?= isChoirAdmin() ? 'لا يوجد خدام في خورسك' : 'لا يوجد خدام' ?>
        </h3>
        <p style="margin:0 0 16px">
            <?= isChoirAdmin() ? 'تواصل مع المدير لإضافة خدام' : 'ابدأ بإضافة خادم جديد' ?>
        </p>
        <?php if (hasPermission('servants.create')): ?>
            <a href="<?= e(appBaseUrl()) ?>/servants/create" class="btn btn-primary">
                + إضافة خادم
            </a>
        <?php endif; ?>
    </div>
<?php else: ?>

    <div class="servant-card-grid stagger">
    <?php foreach ($servants as $s): ?>
        <a href="<?= e(appBaseUrl()) ?>/servants/show?id=<?= (int)$s['id'] ?>"
           class="servant-card"
           style="position:relative">

            <?php if (hasPermission('servants.delete')): ?>
                <button type="button"
                        onclick="event.preventDefault(); event.stopPropagation(); deleteServant(<?= (int)$s['id'] ?>, '<?= e(addslashes($s['name'])) ?>')"
                        title="حذف الخادم"
                        style="position:absolute;top:10px;inset-inline-end:10px;width:32px;height:32px;border-radius:50%;background:var(--danger-soft);color:var(--danger);border:1px solid var(--danger);display:grid;place-items:center;cursor:pointer;font-size:14px;transition:all .15s;z-index:2;padding:0">
                    🗑
                </button>
            <?php endif; ?>

            <div class="avatar-lg">
                <?= e($s['emoji'] ?? mb_substr($s['name'], 0, 1)) ?>
            </div>

            <div class="info">
                <div class="name"><?= e($s['name']) ?></div>
                <div class="meta">
                    <?php if (!isChoirAdmin()): ?>
                        <?= e($s['choir_name']) ?>
                        <?php if ($s['code']): ?> · <?php endif; ?>
                    <?php endif; ?>
                    <?php if ($s['code']): ?>
                        <code><?= e($s['code']) ?></code>
                    <?php endif; ?>
                </div>
                <div class="meta" style="margin-top:6px">
                    <span class="badge <?= $s['status'] === 'active' ? 'badge-success' : 'badge-neutral' ?>">
                        <?= e(servantStatusLabel($s['status'])) ?>
                    </span>
                </div>
            </div>
        </a>
    <?php endforeach; ?>
    </div>

<?php endif; ?>

<script>
function deleteServant(id, name) {
    deleteItem({
        url: window.APP_URL + '/api/servants/delete',
        id: id,
        title: 'حذف الخادم',
        message: 'هل أنت متأكد من حذف "' + name + '"؟',
        extra: '<div style="padding:10px;background:var(--warning-soft);border-radius:8px;font-size:13px;color:var(--warning-text);line-height:1.5">' +
               '⚠️ إذا كان للخادم سجل حضور سابق، <strong>سيتم تعطيله</strong> بدلاً من حذفه للحفاظ على البيانات التاريخية.' +
               '</div>'
    });
}

function exportServants() {
    var params = new URLSearchParams(window.location.search);
    window.location.href = window.APP_URL + '/servants/export' + (params.toString() ? '?' + params.toString() : '');
}
</script>