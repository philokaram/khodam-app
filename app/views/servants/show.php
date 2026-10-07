<?php
$base = appBaseUrl();
$servant = $servant ?? [];
$stats = $stats ?? ['total' => 0, 'present' => 0, 'absent' => 0, 'excused' => 0, 'rate' => 0];
$byActivity = $byActivity ?? [];

// احسب النسب
$total = max((int)$stats['total'], 1);
$presentPct = ((int)$stats['present'] / $total) * 100;
$absentPct  = ((int)$stats['absent']  / $total) * 100;
$excusedPct = ((int)$stats['excused'] / $total) * 100;

// Donut SVG
$circumference = 2 * M_PI * 70;
$presentDash = ($presentPct / 100) * $circumference;
$absentDash  = ($absentPct  / 100) * $circumference;
$excusedDash = ($excusedPct / 100) * $circumference;

// أول حرف من الاسم للأفاتار
$initial = mb_substr($servant['name'] ?? '?', 0, 1);
?>

<!-- Header -->
<div class="page-header">
    <div class="page-header-text">
        <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px">
            <a href="<?= e($base) ?>/servants" 
               style="color:var(--text-muted);font-size:13px;font-weight:600;text-decoration:none">
                ← الخدام
            </a>
        </div>
        <h1 style="display:flex;align-items:center;gap:14px">
            <div style="width:52px;height:52px;border-radius:var(--radius-lg);background:linear-gradient(135deg,var(--primary) 0%,var(--primary-light) 100%);color:#fff;display:grid;place-items:center;font-size:22px;font-weight:900;box-shadow:0 4px 12px color-mix(in srgb,var(--primary) 30%,transparent);flex-shrink:0">
                <?= e($initial) ?>
            </div>
            <div>
                <div><?= e($servant['name'] ?? 'خادم') ?></div>
                <div style="font-size:13px;font-weight:600;color:var(--text-muted);margin-top:4px">
                    <?= e($servant['choir_name'] ?? '') ?>
                    <?php if (!empty($servant['code'])): ?>
                        · <?= e($servant['code']) ?>
                    <?php endif; ?>
                </div>
            </div>
        </h1>
    </div>
    <div class="page-header-actions">
        <?php if (hasPermission('servants.edit')): ?>
    <a href="<?= e(appBaseUrl()) ?>/servants/edit?id=<?= (int)$servant['id'] ?>" 
       class="btn btn-primary">
        تعديل البيانات
    </a>
<?php endif; ?>
        <a href="<?= e($base) ?>/reports/servant?id=<?= (int)$servant['id'] ?>" 
           class="btn">
            التقرير الكامل
        </a>
    </div>
</div>

<!-- KPIs -->
<div class="kpi-grid">
    <div class="kpi kpi-primary">
        <div class="kpi-icon">📊</div>
        <div class="kpi-label">إجمالي الجلسات</div>
        <div class="kpi-value"><?= (int)$stats['total'] ?></div>
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
        <div class="kpi-label">بعذر</div>
        <div class="kpi-value"><?= (int)$stats['excused'] ?></div>
    </div>
    <div class="kpi kpi-warning">
        <div class="kpi-icon">%</div>
        <div class="kpi-label">نسبة الحضور</div>
        <div class="kpi-value"><?= e(formatPercent((float)$stats['rate'])) ?></div>
    </div>
</div>

<!-- Charts Row -->
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px">

    <!-- Donut -->
    <div class="chart-card">
        <div class="chart-card-header">
            <div>
                <div class="chart-card-title">توزيع الحضور</div>
                <div class="chart-card-subtitle">ملخص شامل</div>
            </div>
        </div>
        <?php if ($stats['total'] > 0): ?>
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
                        <b><?= e(formatPercent((float)$stats['rate'])) ?></b>
                        <small>نسبة الحضور</small>
                    </div>
                </div>
                <div class="donut-legend">
                    <div class="legend-item">
                        <span class="legend-dot present"></span>
                        حاضر
                        <b><?= (int)$stats['present'] ?></b>
                    </div>
                    <div class="legend-item">
                        <span class="legend-dot absent"></span>
                        غائب
                        <b><?= (int)$stats['absent'] ?></b>
                    </div>
                    <div class="legend-item">
                        <span class="legend-dot excused"></span>
                        بعذر
                        <b><?= (int)$stats['excused'] ?></b>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="empty-state" style="padding:40px 20px">
                لا توجد بيانات حضور بعد
            </div>
        <?php endif; ?>
    </div>

    <!-- Info Card -->
    <div class="chart-card">
        <div class="chart-card-header">
            <div>
                <div class="chart-card-title">البيانات الأساسية</div>
                <div class="chart-card-subtitle">معلومات الخادم</div>
            </div>
        </div>
        <div style="display:grid;gap:12px">
            <div style="display:flex;justify-content:space-between;padding:12px 16px;background:var(--bg-subtle);border-radius:var(--radius);border:1px solid var(--border-color)">
                <span style="font-weight:600;color:var(--text-muted);font-size:13px">الاسم الكامل</span>
                <span style="font-weight:700;color:var(--text-primary)"><?= e($servant['name'] ?? '—') ?></span>
            </div>
            <div style="display:flex;justify-content:space-between;padding:12px 16px;background:var(--bg-subtle);border-radius:var(--radius);border:1px solid var(--border-color)">
                <span style="font-weight:600;color:var(--text-muted);font-size:13px">الخورس</span>
                <span style="font-weight:700;color:var(--text-primary)"><?= e($servant['choir_name'] ?? '—') ?></span>
            </div>
            <div style="display:flex;justify-content:space-between;padding:12px 16px;background:var(--bg-subtle);border-radius:var(--radius);border:1px solid var(--border-color)">
                <span style="font-weight:600;color:var(--text-muted);font-size:13px">الكود</span>
                <span style="font-weight:700;color:var(--text-primary);font-family:var(--font-mono);direction:ltr">
                    <?= e($servant['code'] ?? '—') ?>
                </span>
            </div>
            <div style="display:flex;justify-content:space-between;padding:12px 16px;background:var(--bg-subtle);border-radius:var(--radius);border:1px solid var(--border-color)">
                <span style="font-weight:600;color:var(--text-muted);font-size:13px">الهاتف</span>
                <span style="font-weight:700;color:var(--text-primary);direction:ltr">
                    <?= e($servant['phone'] ?? '—') ?>
                </span>
            </div>
            <div style="display:flex;justify-content:space-between;padding:12px 16px;background:var(--bg-subtle);border-radius:var(--radius);border:1px solid var(--border-color)">
                <span style="font-weight:600;color:var(--text-muted);font-size:13px">تاريخ الانضمام</span>
                <span style="font-weight:700;color:var(--text-primary)">
                    <?= $servant['join_date'] ? e(formatDateAr($servant['join_date'], true)) : '—' ?>
                </span>
            </div>
            <div style="display:flex;justify-content:space-between;padding:12px 16px;background:var(--bg-subtle);border-radius:var(--radius);border:1px solid var(--border-color)">
                <span style="font-weight:600;color:var(--text-muted);font-size:13px">الحالة</span>
                <span class="badge <?= ($servant['status'] ?? '') === 'active' ? 'badge-success' : 'badge-neutral' ?>">
                    <?= e(servantStatusLabel($servant['status'] ?? 'active')) ?>
                </span>
            </div>
        </div>
    </div>
</div>

<!-- By Activity -->
<?php if (!empty($byActivity)): ?>
<div class="chart-card">
    <div class="chart-card-header">
        <div>
            <div class="chart-card-title">الحضور حسب النشاط</div>
            <div class="chart-card-subtitle">إحصائيات مفصّلة لكل نشاط</div>
        </div>
    </div>
    <div class="activity-list">
        <?php foreach ($byActivity as $act):
            $actTotal   = (int)$act['total'];
            $actPresent = (int)$act['present'];
            $actAbsent  = (int)$act['absent'];
            $actExcused = (int)$act['excused'];
            $actRate = $actTotal > 0 ? round($actPresent / $actTotal * 100, 1) : 0;
            $barColor = $actRate >= 75 ? 'var(--success)' : ($actRate >= 50 ? 'var(--warning)' : 'var(--danger)');
        ?>
            <div class="activity-item">
                <div>
                    <div class="activity-name"><?= e($act['activity_name']) ?></div>
                    <div class="activity-meta">
                        حاضر: <?= $actPresent ?> · غائب: <?= $actAbsent ?> · بعذر: <?= $actExcused ?>
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

<!-- Responsive -->
<style>
@media (max-width: 900px) {
    .chart-card + .chart-card {
        margin-top: 20px;
    }
    div[style*="grid-template-columns:1fr 1fr"] {
        grid-template-columns: 1fr !important;
    }
}
</style>