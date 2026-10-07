<?php
$isChoirAdminUser = isChoirAdmin();
$myChoir = $isChoirAdminUser
    ? (new Choir())->find((int)currentUser()['choir_id'])
    : null;
?>

<div class="page-header">
    <div class="page-header-text">
        <h1><?= e(__('nav.reports')) ?></h1>
        <p>
            <?php if ($isChoirAdminUser): ?>
                تقارير خورس <?= e($myChoir['name'] ?? '') ?>
            <?php else: ?>
                تقارير الحضور المفصّلة
            <?php endif; ?>
        </p>
    </div>
    <div class="page-header-actions">
        <button type="button" class="btn" onclick="window.print()">
            🖨️ طباعة
        </button>
    </div>
</div>

<!-- Filters -->
<form method="get" class="filters-bar">
    <label>
        <span><?= e(__('reports.from')) ?></span>
        <input type="date" name="from" value="<?= e($filters['from'] ?? '') ?>">
    </label>
    <label>
        <span><?= e(__('reports.to')) ?></span>
        <input type="date" name="to" value="<?= e($filters['to'] ?? '') ?>">
    </label>

    <?php if (!$isChoirAdminUser): ?>
    <label>
        <span><?= e(__('reports.choir')) ?></span>
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
        <span><?= e(__('reports.activity')) ?></span>
        <select name="activity_id">
            <option value="">الكل</option>
            <?php foreach ($activities as $a): ?>
                <option value="<?= (int)$a['id'] ?>" <?= ($filters['activity_id'] == $a['id']) ? 'selected' : '' ?>>
                    <?= e($a['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </label>

    <label>
        <span><?= e(__('reports.status')) ?></span>
        <select name="status">
            <option value="">الكل</option>
            <option value="present" <?= ($filters['status'] === 'present') ? 'selected' : '' ?>>حاضر</option>
            <option value="absent"  <?= ($filters['status'] === 'absent')  ? 'selected' : '' ?>>غائب</option>
            <option value="excused" <?= ($filters['status'] === 'excused') ? 'selected' : '' ?>>بعذر</option>
        </select>
    </label>

    <button type="submit" class="btn btn-primary">تطبيق</button>
    <?php if (!empty(array_filter($filters))): ?>
        <a href="<?= e(appBaseUrl()) ?>/reports" class="btn">إعادة تعيين</a>
    <?php endif; ?>
</form>

<!-- Export Buttons -->
<?php if (hasPermission('reports.export')): ?>
<div style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:20px;padding:16px;background:var(--bg-surface);border:1px solid var(--border-color);border-radius:var(--radius-lg)">
    <div style="display:flex;align-items:center;gap:8px;flex:1;min-width:200px">
        <span style="font-size:13px;font-weight:700;color:var(--text-muted)">📥 تصدير Excel:</span>
    </div>
    <button type="button" class="btn" onclick="exportAttendance()">📊 تقرير الحضور</button>
    <button type="button" class="btn" onclick="exportServantsStats()">👥 إحصائيات الخدام</button>
    <button type="button" class="btn" onclick="exportActivitiesStats()">📅 إحصائيات الأنشطة</button>
</div>
<?php endif; ?>

<!-- KPI Stats -->
<div class="kpi-grid">
    <div class="kpi kpi-success">
        <div class="kpi-icon">✓</div>
        <div class="kpi-label">الحضور</div>
        <div class="kpi-value"><?= (int)$stats['present'] ?></div>
    </div>
    <div class="kpi kpi-danger">
        <div class="kpi-icon">✕</div>
        <div class="kpi-label">الغياب</div>
        <div class="kpi-value"><?= (int)$stats['absent'] ?></div>
    </div>
    <div class="kpi kpi-info">
        <div class="kpi-icon">⏱</div>
        <div class="kpi-label">بعذر</div>
        <div class="kpi-value"><?= (int)$stats['excused'] ?></div>
    </div>
    <div class="kpi kpi-warning">
        <div class="kpi-icon">%</div>
        <div class="kpi-label">نسبة الحضور</div>
        <div class="kpi-value"><?= e(formatPercent((float)$stats['rate'])) ?></div>
    </div>
    <div class="kpi kpi-primary">
        <div class="kpi-icon">📊</div>
        <div class="kpi-label">إجمالي السجلات</div>
        <div class="kpi-value"><?= (int)$stats['total_records'] ?></div>
    </div>
</div>

<!-- Records Table -->
<?php if (empty($records)): ?>
    <div class="empty-state">
        <h3 style="margin:0 0 8px;color:var(--text-primary)">لا توجد سجلات</h3>
        <p>لا توجد سجلات مطابقة للفلاتر المحددة.</p>
    </div>
<?php else: ?>

    <div class="card" style="padding:0;overflow:hidden">
        <div style="overflow-x:auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>التاريخ</th>
                        <th>الخادم</th>
                        <?php if (!$isChoirAdminUser): ?>
                            <th>الخورس</th>
                        <?php endif; ?>
                        <th>النشاط</th>
                        <th>الحالة</th>
                        <th>سبب العذر</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($records as $r):
                    $status = $r['status'];
                    $badgeClass = $status === 'present' ? 'badge-success'
                                : ($status === 'absent'  ? 'badge-danger' : 'badge-warning');
                    $statusLabel = match($status) {
                        'present' => 'حاضر',
                        'absent'  => 'غائب',
                        'excused' => 'بعذر',
                        default   => $status,
                    };
                ?>
                    <tr>
                        <td style="font-size:13px;color:var(--text-muted)">
                            <?= e(formatDateAr($r['attendance_date'])) ?>
                        </td>
                        <td>
                            <div style="display:flex;align-items:center;gap:8px">
                                <div style="width:28px;height:28px;border-radius:50%;background:var(--bg-subtle);display:grid;place-items:center;font-size:12px;font-weight:700;flex-shrink:0">
                                    <?= e(mb_substr($r['servant_name'], 0, 1)) ?>
                                </div>
                                <a href="<?= e(appBaseUrl()) ?>/servants/show?id=<?= (int)$r['servant_id'] ?>"
                                   style="font-weight:700;color:var(--text-primary);text-decoration:none">
                                    <?= e($r['servant_name']) ?>
                                </a>
                            </div>
                        </td>
                        <?php if (!$isChoirAdminUser): ?>
                            <td style="font-size:13px">
                                <?= e($r['choir_name']) ?>
                            </td>
                        <?php endif; ?>
                        <td style="font-size:13px">
                            <?= e($r['activity_name']) ?>
                        </td>
                        <td>
                            <span class="badge <?= $badgeClass ?>">
                                <?= e($statusLabel) ?>
                            </span>
                        </td>
                        <td style="font-size:13px;color:var(--text-muted)">
                            <?= e($r['excuse_reason'] ?? '—') ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <p style="color:var(--text-muted);font-size:13px;margin-top:12px;text-align:center">
        عرض <?= count($records) ?> سجل (الحد الأقصى 500)
    </p>

<?php endif; ?>

<script>
function buildQuery() {
    var params = new URLSearchParams(window.location.search);
    return params.toString();
}

function exportAttendance() {
    var qs = buildQuery();
    window.location.href = window.APP_URL + '/reports/export/attendance' + (qs ? '?' + qs : '');
}

function exportServantsStats() {
    var qs = buildQuery();
    window.location.href = window.APP_URL + '/reports/export/servants-stats' + (qs ? '?' + qs : '');
}

function exportActivitiesStats() {
    var qs = buildQuery();
    window.location.href = window.APP_URL + '/reports/export/activities-stats' + (qs ? '?' + qs : '');
}
</script>