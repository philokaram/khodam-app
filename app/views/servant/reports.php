<?php
$me = currentUser();
$servant = $servant ?? [];
$overall = $overall ?? ['total' => 0, 'present' => 0, 'absent' => 0, 'excused' => 0, 'rate' => 0];
$byActivity = $byActivity ?? [];

$total = max((int)$overall['total'], 1);
$circumference = 2 * M_PI * 70;
$presentPct = ((int)$overall['present'] / $total) * 100;
$absentPct  = ((int)$overall['absent']  / $total) * 100;
$excusedPct = ((int)$overall['excused'] / $total) * 100;
$presentDash = ($presentPct / 100) * $circumference;
$absentDash  = ($absentPct  / 100) * $circumference;
$excusedDash = ($excusedPct / 100) * $circumference;

$initial = mb_substr($servant['name'] ?? $me['name'], 0, 1);
?>

<div class="page-header">
    <div class="page-header-text">
        <h1 style="display:flex;align-items:center;gap:14px">
            <div style="width:52px;height:52px;border-radius:var(--radius-lg);background:linear-gradient(135deg,var(--primary) 0%,var(--primary-light) 100%);color:#fff;display:grid;place-items:center;font-size:22px;font-weight:900;flex-shrink:0;box-shadow:0 4px 12px color-mix(in srgb,var(--primary) 30%,transparent)">
                <?= e($initial) ?>
            </div>
            <div>
                <div>تقريري الشخصي</div>
                <div style="font-size:13px;font-weight:600;color:var(--text-muted);margin-top:4px">
                    <?= e($servant['name'] ?? $me['name']) ?>
                    <?php if (!empty($servant['code'])): ?>
                        · <?= e($servant['code']) ?>
                    <?php endif; ?>
                </div>
            </div>
        </h1>
    </div>
    <div class="page-header-actions">
        <button type="button" class="btn" onclick="window.print()">🖨️ طباعة</button>
    </div>
</div>

<!-- KPI -->
<div class="kpi-grid">
    <div class="kpi kpi-primary">
        <div class="kpi-icon">📊</div>
        <div class="kpi-label">إجمالي الجلسات</div>
        <div class="kpi-value"><?= (int)$overall['total'] ?></div>
    </div>
    <div class="kpi kpi-success">
        <div class="kpi-icon">✓</div>
        <div class="kpi-label">الحضور</div>
        <div class="kpi-value"><?= (int)$overall['present'] ?></div>
    </div>
    <div class="kpi kpi-danger">
        <div class="kpi-icon">✕</div>
        <div class="kpi-label">الغياب</div>
        <div class="kpi-value"><?= (int)$overall['absent'] ?></div>
    </div>
    <div class="kpi kpi-info">
        <div class="kpi-icon">⏱</div>
        <div class="kpi-label">بعذر</div>
        <div class="kpi-value"><?= (int)$overall['excused'] ?></div>
    </div>
    <div class="kpi kpi-warning">
        <div class="kpi-icon">%</div>
        <div class="kpi-label">نسبة الحضور</div>
        <div class="kpi-value"><?= e(formatPercent((float)$overall['rate'])) ?></div>
    </div>
</div>

<!-- Donut Chart -->
<?php if ($overall['total'] > 0): ?>
<div class="chart-card">
    <div class="chart-card-header">
        <div>
            <div class="chart-card-title">توزيع الحضور</div>
            <div class="chart-card-subtitle">ملخص أدائك</div>
        </div>
    </div>
    <div class="donut-chart">
        <div class="donut">
            <svg viewBox="0 0 200 200">
                <circle class="donut-bg" cx="100" cy="100" r="70"></circle>
                <circle class="donut-present" cx="100" cy="100" r="70"
                    stroke-dasharray="<?= $presentDash ?> <?= $circumference - $presentDash ?>"
                    stroke-dashoffset="0"></circle>
                <circle class="donut-absent" cx="100" cy="100" r="70"
                    stroke-dasharray="<?= $absentDash ?> <?= $circumference - $absentDash ?>"
                    stroke-dashoffset="-<?= $presentDash ?>"></circle>
                <circle class="donut-excused" cx="100" cy="100" r="70"
                    stroke-dasharray="<?= $excusedDash ?> <?= $circumference - $excusedDash ?>"
                    stroke-dashoffset="-<?= $presentDash + $absentDash ?>"></circle>
            </svg>
            <div class="center">
                <b><?= e(formatPercent((float)$overall['rate'])) ?></b>
                <small>نسبة الحضور</small>
            </div>
        </div>
        <div class="donut-legend">
            <div class="legend-item">
                <span class="legend-dot present"></span>
                حاضر <b><?= (int)$overall['present'] ?></b>
            </div>
            <div class="legend-item">
                <span class="legend-dot absent"></span>
                غائب <b><?= (int)$overall['absent'] ?></b>
            </div>
            <div class="legend-item">
                <span class="legend-dot excused"></span>
                بعذر <b><?= (int)$overall['excused'] ?></b>
            </div>
        </div>
    </div>
</div>
<?php else: ?>
<div class="empty-state">
    <h3 style="margin:0 0 8px;color:var(--text-primary)">لا توجد سجلات حضور بعد</h3>
    <p>ستظهر إحصائياتك هنا بعد أن يسجل المدير حضورك</p>
</div>
<?php endif; ?>

<!-- By Activity -->
<?php if (!empty($byActivity)): ?>
<div class="chart-card">
    <div class="chart-card-header">
        <div class="chart-card-title">الحضور حسب النشاط</div>
    </div>
    <div class="activity-list">
        <?php foreach ($byActivity as $act):
            $actTotal = (int)$act['total'];
            $actPresent = (int)$act['present'];
            $actRate = $actTotal > 0 ? round($actPresent / $actTotal * 100, 1) : 0;
            $barColor = $actRate >= 75 ? 'var(--success)' : ($actRate >= 50 ? 'var(--warning)' : 'var(--danger)');
        ?>
            <div class="activity-item">
                <div>
                    <div class="activity-name"><?= e($act['activity_name']) ?></div>
                    <div class="activity-meta">
                        حاضر: <?= $actPresent ?> · غائب: <?= (int)$act['absent'] ?> · بعذر: <?= (int)$act['excused'] ?>
                    </div>
                </div>
                <div class="activity-percent" style="color:<?= $barColor ?>">
                    <?= $actRate ?>%
                </div>
                <div class="activity-bar">
                    <div class="progress">
                        <div class="progress-bar" style="width:<?= $actRate ?>%;background:<?= $barColor ?>"></div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>