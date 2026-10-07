<?php
$me = currentUser();
$servant = $servant ?? [];
$stats = $stats ?? ['total' => 0, 'present' => 0, 'absent' => 0, 'excused' => 0, 'rate' => 0];
$byActivity = $byActivity ?? [];
?>

<div class="page-header">
    <div class="page-header-text">
        <h1>ملفي الشخصي</h1>
        <p><?= e($servant['name'] ?? $me['name']) ?></p>
    </div>
</div>

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

<?php if (!empty($byActivity)): ?>
<div class="chart-card">
    <div class="chart-card-header">
        <div class="chart-card-title">الحضور حسب النشاط</div>
    </div>
    <div class="activity-list">
        <?php foreach ($byActivity as $act):
            $t = (int)$act['total'];
            $p = (int)$act['present'];
            $rate = $t > 0 ? round($p / $t * 100, 1) : 0;
            $color = $rate >= 75 ? 'var(--success)' : ($rate >= 50 ? 'var(--warning)' : 'var(--danger)');
        ?>
            <div class="activity-item">
                <div>
                    <div class="activity-name"><?= e($act['activity_name']) ?></div>
                    <div class="activity-meta">حاضر: <?= $p ?> · غائب: <?= (int)$act['absent'] ?> · بعذر: <?= (int)$act['excused'] ?></div>
                </div>
                <div class="activity-percent" style="color:<?= $color ?>"><?= $rate ?>%</div>
                <div class="activity-bar">
                    <div class="progress">
                        <div class="progress-bar" style="width:<?= $rate ?>%;background:<?= $color ?>"></div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<?php require APP_PATH . '/views/profile/_password_modal.php'; ?>