<div class="page-header">
    <div class="page-header-text">
        <h1>المستخدمون</h1>
        <p>إدارة الحسابات والصلاحيات</p>
    </div>
    <div class="page-header-actions">
        <?php if (hasPermission('users.create')): ?>
            <a href="<?= e(appBaseUrl()) ?>/users/create" class="btn btn-primary">
                + إضافة مستخدم
            </a>
        <?php endif; ?>
    </div>
</div>

<?php
// إحصائيات سريعة
$totalUsers = count($users);
$activeUsers = 0;
$inactiveUsers = 0;
$adminUsers = 0;
$superAdminUsers = 0;

foreach ($users as $u) {
    if ($u['status'] === 'active') $activeUsers++;
    else $inactiveUsers++;
    if ((int)$u['role_id'] === 1) $superAdminUsers++;
    if ((int)$u['role_id'] === 2) $adminUsers++;
}
?>

<!-- KPI Cards -->
<div class="kpi-grid">
    <div class="kpi kpi-primary">
        <div class="kpi-icon">👥</div>
        <div class="kpi-label">إجمالي المستخدمين</div>
        <div class="kpi-value"><?= $totalUsers ?></div>
    </div>
    <div class="kpi kpi-success">
        <div class="kpi-icon">✓</div>
        <div class="kpi-label">نشط</div>
        <div class="kpi-value"><?= $activeUsers ?></div>
    </div>
    <div class="kpi kpi-warning">
        <div class="kpi-icon">⚠</div>
        <div class="kpi-label">معطّل</div>
        <div class="kpi-value"><?= $inactiveUsers ?></div>
    </div>
    <div class="kpi kpi-purple">
        <div class="kpi-icon">🛡</div>
        <div class="kpi-label">مدير عام</div>
        <div class="kpi-value"><?= $superAdminUsers ?></div>
    </div>
</div>

<?php if (empty($users)): ?>
    <div class="empty-state">
        <h3 style="margin:0 0 8px;color:var(--text-primary)">لا يوجد مستخدمون</h3>
        <p>ابدأ بإضافة مستخدم جديد</p>
    </div>
<?php else: ?>

    <!-- جدول المستخدمين -->
    <div class="table-wrapper">
        <table class="table">
            <thead>
                <tr>
                    <th>المستخدم</th>
                    <th>اسم الدخول</th>
                    <th>الدور</th>
                    <th>الخورس</th>
                    <th>الحالة</th>
                    <th>آخر دخول</th>
                    <th style="width:180px">الإجراءات</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($users as $u):
                $roleClass = match ((int)$u['role_id']) {
                    1 => 'badge-danger',
                    2 => 'badge-warning',
                    3 => 'badge-info',
                    4 => 'badge-success',
                    default => 'badge-neutral',
                };
                $roleIcon = match ((int)$u['role_id']) {
                    1 => '👑',
                    2 => '🛡',
                    3 => '🎵',
                    4 => '📝',
                    default => '👤',
                };
                $initial = mb_substr($u['name'], 0, 1);
            ?>
                <tr>
                    <td>
                        <div style="display:flex;align-items:center;gap:12px">
                            <div style="width:40px;height:40px;border-radius:var(--radius);background:linear-gradient(135deg,var(--primary) 0%,var(--primary-light) 100%);color:#fff;display:grid;place-items:center;font-weight:800;font-size:15px;flex-shrink:0;box-shadow:0 2px 6px color-mix(in srgb,var(--primary) 30%,transparent)">
                                <?= e($initial) ?>
                            </div>
                            <div style="min-width:0">
                                <div style="font-weight:800;color:var(--text-primary);font-size:14px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:200px">
                                    <?= e($u['name']) ?>
                                </div>
                                <?php if ($u['email']): ?>
                                    <div style="font-size:12px;color:var(--text-muted);direction:ltr;margin-top:2px">
                                        <?= e($u['email']) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </td>
                    <td>
                        <code style="direction:ltr;display:inline-block;background:var(--bg-subtle);padding:4px 10px;border-radius:6px;font-size:13px">
                            <?= e($u['username']) ?>
                        </code>
                    </td>
                    <td>
                        <span class="badge <?= $roleClass ?>">
                            <?= $roleIcon ?> <?= e($u['role_label'] ?? $u['role_name']) ?>
                        </span>
                    </td>
                    <td>
                        <?php if ($u['choir_name']): ?>
                            <span style="display:inline-flex;align-items:center;gap:4px;font-size:13px;font-weight:600">
                                🎵 <?= e($u['choir_name']) ?>
                            </span>
                        <?php else: ?>
                            <span style="color:var(--text-muted)">—</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($u['status'] === 'active'): ?>
                            <span class="badge badge-success">● نشط</span>
                        <?php else: ?>
                            <span class="badge badge-neutral">● معطّل</span>
                        <?php endif; ?>
                    </td>
                    <td style="font-size:12px;color:var(--text-muted)">
                        <?php if ($u['last_login_at']): ?>
                            <?= e(formatDateAr($u['last_login_at'])) ?>
                        <?php else: ?>
                            <span style="color:var(--text-disabled)">لم يدخل بعد</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div style="display:flex;gap:6px;flex-wrap:wrap">
                            <?php if (hasPermission('users.edit')): ?>
                                <a href="<?= e(appBaseUrl()) ?>/users/edit?id=<?= (int)$u['id'] ?>"
                                   class="btn btn-sm"
                                   title="تعديل">
                                    ✏️
                                </a>

                                <button type="button"
                                        class="btn btn-sm"
                                        onclick="changePassword(<?= (int)$u['id'] ?>, '<?= e(addslashes($u['name'])) ?>')"
                                        title="تغيير كلمة المرور">
                                    🔑
                                </button>
                            <?php endif; ?>

                            <?php if (hasPermission('users.delete')): ?>
                                <?php if ($u['status'] === 'active'): ?>
                                    <button type="button"
                                            class="btn btn-sm btn-danger"
                                            onclick="deactivateUser(<?= (int)$u['id'] ?>, '<?= e(addslashes($u['name'])) ?>')"
                                            title="تعطيل">
                                        🚫
                                    </button>
                                <?php else: ?>
                                    <button type="button"
                                            class="btn btn-sm"
                                            style="background:var(--success-soft);color:var(--success-text);border-color:var(--success)"
                                            onclick="activateUser(<?= (int)$u['id'] ?>, '<?= e(addslashes($u['name'])) ?>')"
                                            title="تنشيط">
                                        ✅
                                    </button>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

<?php endif; ?>

<style>
/* تحسينات خاصة بصفحة المستخدمين */
.table-wrapper .table td {
    padding: 14px 16px;
}

.table-wrapper .table tbody tr {
    transition: background 0.15s;
}

.table-wrapper .table tbody tr:hover {
    background: var(--bg-hover);
}
</style>

<script>
function changePassword(id, name) {
    var pw = prompt('كلمة المرور الجديدة لـ"' + name + '" (6 أحرف على الأقل):');
    if (!pw || pw.length < 6) {
        if (pw !== null) toast('كلمة المرور قصيرة', 'error');
        return;
    }
    var confirmPw = prompt('تأكيد كلمة المرور:');
    if (pw !== confirmPw) {
        toast('كلمتا المرور غير متطابقتين', 'error');
        return;
    }

    var csrf = document.querySelector('meta[name="csrf-token"]').content;

    fetch('/api/users/change-password', {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-Token': csrf
        },
        body: JSON.stringify({ id: id, new_password: pw })
    })
    .then(r => r.json())
    .then(j => {
        if (j.success) toast('✅ ' + j.message, 'success');
        else toast('❌ ' + (j.message || 'فشل'), 'error');
    })
    .catch(e => toast('خطأ: ' + e.message, 'error'));
}

function deactivateUser(id, name) {
    deleteItem({
        url: window.APP_URL + '/api/users/delete',
        id: id,
        title: 'تعطيل المستخدم',
        message: 'هل تريد تعطيل "' + name + '"؟',
        extra: '<div style="padding:10px;background:var(--warning-soft);border-radius:8px;font-size:13px;color:var(--warning-text)">' +
               '⚠️ سيتم منع المستخدم من تسجيل الدخول، لكن بياناته تبقى محفوظة. يمكنك تنشيطه لاحقاً.' +
               '</div>',
    });
}

function activateUser(id, name) {
    var csrf = document.querySelector('meta[name="csrf-token"]').content;
    fetch('/api/users/activate', {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-Token': csrf
        },
        body: JSON.stringify({ id: id })
    })
    .then(r => r.json())
    .then(j => {
        if (j.success) {
            toast('✅ ' + j.message, 'success');
            setTimeout(() => location.reload(), 800);
        } else {
            toast('❌ ' + (j.message || 'فشل'), 'error');
        }
    });
}
</script>