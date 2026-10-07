<?php
// بيانات إضافية للرسم البياني
$byActivity = [];
if ($stats['total_records'] > 0) {
    $where = "WHERE 1=1";
    $params = [];
    if (!empty($filters['from']))        { $where .= " AND ses.attendance_date >= ?"; $params[] = $filters['from']; }
    if (!empty($filters['to']))          { $where .= " AND ses.attendance_date <= ?"; $params[] = $filters['to']; }
    if (!empty($filters['choir_id']))    { $where .= " AND ses.choir_id = ?";         $params[] = $filters['choir_id']; }
    if (!empty($filters['activity_id'])) { $where .= " AND ses.activity_id = ?";      $params[] = $filters['activity_id']; }

    $byActivity = Database::all(
        "SELECT act.name AS activity_name,
                COUNT(a.id) AS total,
                SUM(a.status='present') AS present
         FROM attendance a
         JOIN attendance_sessions ses ON ses.id = a.session_id
         JOIN activities act ON act.id = ses.activity_id
         $where
         GROUP BY act.id, act.name
         ORDER BY total DESC",
        $params
    );
}

// احسب نسب لرسم الدائرة
$total = max((int)$stats['total_records'], 1);
$presentPct = ((int)$stats['present'] / $total) * 100;
$absentPct = ((int)$stats['absent'] / $total) * 100;
$excusedPct = ((int)$stats['excused'] / $total) * 100;

$circumference = 2 * M_PI * 70; // نصف القطر 70
$presentDash = ($presentPct / 100) * $circumference;
$absentDash = ($absentPct / 100) * $circumference;
$excusedDash = ($excusedPct / 100) * $circumference;
?>

<div class="page-head">
    <div>
        <h1 class="page-title">لوحة التحكم</h1>
        <?php if (isChoirAdmin()): ?>
<p style="color:var(--text-muted);margin:4px 0 0;font-size:14px">
    إحصائيات خورس <?= e(currentUser()['choir_name'] ?? '') ?>
</p>
<?php endif; ?>
        <p style="color:var(--muted);margin:4px 0 0;font-size:15px">
            نظرة سريعة على حضور الخدام
        </p>
    </div>
    <a href="<?= e(appBaseUrl()) ?>/attendance/create" class="btn btn-primary">
        + تسجيل حضور
    </a>
</div>

<form method="get" class="filters-bar">
    <label>
        <span>من تاريخ</span>
        <input type="date" name="from" value="<?= e($filters['from'] ?? '') ?>">
    </label>
    <label>
        <span>إلى تاريخ</span>
        <input type="date" name="to" value="<?= e($filters['to'] ?? '') ?>">
    </label>
<?php if (!isChoirAdmin()): ?>
<label>
    <span>الخورس</span>
    <select name="choir_id">
        <option value="">الكل</option>
        <?php foreach ($choirs as $c): ?>
            <option value="<?= (int)$c['id'] ?>" <?= ($filters['choir_id'] == $c['id']) ? 'selected' : '' ?>>
                <?= e($c['name']) ?>
            </option>
        <?php endforeach; ?>
    </select>
</label>
<?php else: ?>
<div style="display:flex;align-items:center;gap:8px;padding:8px 14px;background:var(--primary-soft);border:1px solid var(--primary);border-radius:var(--radius);font-weight:700;font-size:13px;color:var(--primary)">
    🎵 <?= e(currentUser()['choir_name'] ?? '') ?>
</div>
<?php endif; ?>
    <label>
        <span>النشاط</span>
        <select name="activity_id">
            <option value="">الكل</option>
            <?php foreach ($activities as $a): ?>
                <option value="<?= (int)$a['id'] ?>" <?= ($filters['activity_id'] == $a['id']) ? 'selected' : '' ?>>
                    <?= e($a['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </label>
    <button type="submit" class="btn btn-primary">تطبيق</button>
    <?php if (!empty(array_filter($filters))): ?>
        <a href="<?= e(appBaseUrl()) ?>/dashboard" class="btn">إعادة تعيين</a>
    <?php endif; ?>
</form>

<!-- KPI Cards -->
<div class="kpi-grid">
  <div class="kpi kpi-purple"
     data-tooltip="إجمالي الخدام المسجلين في النظام">
    <div class="kpi-icon">👥</div>
    <div class="kpi-label">إجمالي الخدام</div>
    <div class="kpi-value"><?= (int)$stats['total_servants'] ?></div>
</div>

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
        <div class="kpi-label">الغياب بعذر</div>
        <div class="kpi-value"><?= (int)$stats['excused'] ?></div>
    </div>

    <div class="kpi kpi-warning">
        <div class="kpi-icon">%</div>
        <div class="kpi-label">نسبة الحضور</div>
        <div class="kpi-value"><?= e(formatPercent((float)$stats['rate'])) ?></div>
    </div>
</div>

<?php if ($stats['total_records'] > 0): ?>

    <!-- Donut Chart + Legend -->
    <div class="chart-card">
        <h3>توزيع الحضور</h3>
        <div class="donut-chart">
            <div class="donut">
                <svg viewBox="0 0 200 200">
                    <circle class="donut-bg" cx="100" cy="100" r="70"></circle>
                    
                    <!-- Present -->
                    <circle class="donut-present" cx="100" cy="100" r="70"
                        stroke-dasharray="<?= $presentDash ?> <?= $circumference - $presentDash ?>"
                        stroke-dashoffset="0"></circle>
                    
                    <!-- Absent -->
                    <circle class="donut-absent" cx="100" cy="100" r="70"
                        stroke-dasharray="<?= $absentDash ?> <?= $circumference - $absentDash ?>"
                        stroke-dashoffset="-<?= $presentDash ?>"></circle>
                    
                    <!-- Excused -->
                    <circle class="donut-excused" cx="100" cy="100" r="70"
                        stroke-dasharray="<?= $excusedDash ?> <?= $circumference - $excusedDash ?>"
                        stroke-dashoffset="-<?= $presentDash + $absentDash ?>"></circle>
                </svg>
                <div class="center">
                    <b><?= e(formatPercent((float)$stats['rate'])) ?></b>
                    <small>نسبة الحضور</small>
                </div>
            </div>
            <div class="donut-legend">
                <div class="legend-item">
                    <span class="legend-dot present"></span>
                    حاضر: <b><?= (int)$stats['present'] ?></b>
                </div>
                <div class="legend-item">
                    <span class="legend-dot absent"></span>
                    غائب: <b><?= (int)$stats['absent'] ?></b>
                </div>
                <div class="legend-item">
                    <span class="legend-dot excused"></span>
                    بعذر: <b><?= (int)$stats['excused'] ?></b>
                </div>
                <div class="legend-item" style="padding-top:8px;border-top:2px dashed var(--line)">
                    إجمالي السجلات: <b><?= (int)$stats['total_records'] ?></b>
                </div>
            </div>
        </div>
    </div>

    <!-- Activity Bar Chart -->
    <?php if (!empty($byActivity)): ?>
    <div class="chart-card">
        <h3>الحضور حسب النشاط</h3>
        <div class="bar-chart" style="height:<?= max(220, count($byActivity) * 30) ?>px">
            <?php 
            $maxTotal = max(array_column($byActivity, 'total')) ?: 1;
            foreach ($byActivity as $act):
                $heightPercent = ($act['total'] / $maxTotal) * 100;
                $presentPctAct = $act['total'] > 0 ? round($act['present'] / $act['total'] * 100) : 0;
            ?>
                <div class="bar-item">
                    <div class="bar-fill" style="height:<?= $heightPercent ?>%">
                        <span class="bar-value"><?= $presentPctAct ?>% (<?= (int)$act['present'] ?>/<?= (int)$act['total'] ?>)</span>
                    </div>
                    <div class="bar-label"><?= e($act['activity_name']) ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Activity Progress List -->
    <div class="chart-card">
        <h3>نسبة الحضور لكل نشاط</h3>
        <div class="activity-list">
            <?php foreach ($byActivity as $act):
                $actTotal = (int)$act['total'];
                $actPresent = (int)$act['present'];
                $actRate = $actTotal > 0 ? round($actPresent / $actTotal * 100, 1) : 0;
                $barColor = $actRate >= 75 ? 'var(--green)' : ($actRate >= 50 ? 'var(--yellow)' : 'var(--red)');
            ?>
                <div class="activity-item">
                    <div class="header">
                        <span class="name">📅 <?= e($act['activity_name']) ?></span>
                        <span class="percent" style="color:<?= $barColor ?>"><?= $actRate ?>%</span>
                    </div>
                    <div class="progress">
                        <div class="progress-bar" style="width:<?= $actRate ?>%;background:<?= $barColor ?>"></div>
                    </div>
                    <div style="display:flex;justify-content:space-between;margin-top:6px;font-size:13px;color:var(--muted);font-weight:700">
                        <span>حاضر: <?= $actPresent ?></span>
                        <span>إجمالي: <?= $actTotal ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

<?php else: ?>

    <div class="empty-state">
        <h3 style="margin-top:0;color:var(--ink)">لا توجد بيانات حضور بعد</h3>
        <p>ابدأ بتسجيل حضور لأول اجتماع لتظهر الإحصائيات هنا.</p>
        <a href="<?= e(appBaseUrl()) ?>/attendance/create" class="btn btn-primary" style="margin-top:16px">
            + تسجيل أول حضور
        </a>
    </div>

<?php endif; ?>