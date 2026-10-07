<div class="page-head">
    <h1 class="page-title">تقرير: <?= e($servant['name']) ?></h1>
    <a href="<?= e(appBaseUrl()) ?>/reports" class="btn">رجوع للتقارير</a>
</div>

<div class="box" style="margin-bottom:20px">
    <h3 style="margin-top:0">البيانات الأساسية</h3>
    <p><b>الخورس:</b> <?= e($servant['choir_name']) ?></p>
    <p><b>الكود:</b> <?= e($servant['code'] ?? '—') ?></p>
    <p><b>الهاتف:</b> <?= e($servant['phone'] ?? '—') ?></p>
    <p><b>الحالة:</b>
        <span class="badge <?= $servant['status'] === 'active' ? 'badge-green' : 'badge-muted' ?>">
            <?= e(servantStatusLabel($servant['status'])) ?>
        </span>
    </p>
</div>

<h2 style="font-size:22px;font-weight:900;margin:24px 0 12px">الإحصائيات الإجمالية</h2>

<div class="kpi-grid">
    <div class="kpi kpi-yellow"><b><?= (int)$overall['total'] ?></b><small>إجمالي الجلسات</small></div>
    <div class="kpi kpi-green"><b><?= (int)$overall['present'] ?></b><small>حاضر</small></div>
    <div class="kpi kpi-red"><b><?= (int)$overall['absent'] ?></b><small>غائب</small></div>
    <div class="kpi kpi-sky"><b><?= (int)$overall['excused'] ?></b><small>بعذر</small></div>
    <div class="kpi kpi-orange"><b><?= e(formatPercent((float)$overall['rate'])) ?></b><small>نسبة الحضور</small></div>
</div>

<h2 style="font-size:22px;font-weight:900;margin:24px 0 12px">حسب النشاط</h2>

<?php if (empty($byActivity)): ?>
    <div class="empty-state">لا توجد بيانات.</div>
<?php else: ?>
<table class="table">
    <thead>
        <tr>
            <th>النشاط</th>
            <th>إجمالي</th>
            <th>حاضر</th>
            <th>غائب</th>
            <th>بعذر</th>
            <th>النسبة</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($byActivity as $row):
        $total = (int)$row['total'];
        $present = (int)$row['present'];
        $rate = $total > 0 ? round($present / $total * 100, 1) : 0;
    ?>
        <tr>
            <td><?= e($row['activity_name']) ?></td>
            <td><?= $total ?></td>
            <td><?= $present ?></td>
            <td><?= (int)$row['absent'] ?></td>
            <td><?= (int)$row['excused'] ?></td>
            <td><b><?= $rate ?>%</b></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>

<h2 style="font-size:22px;font-weight:900;margin:24px 0 12px">سجل الحضور التفصيلي</h2>

<?php if (empty($records)): ?>
    <div class="empty-state">لا توجد سجلات.</div>
<?php else: ?>
    <div style="overflow-x:auto">
    <table class="table">
        <thead>
            <tr>
                <th>التاريخ</th>
                <th>النشاط</th>
                <th>الخورس</th>
                <th>الحالة</th>
                <th>سبب العذر</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($records as $r):
            $status = $r['status'];
            $badgeClass = $status === 'present' ? 'badge-green'
                        : ($status === 'absent'  ? 'badge-red' : 'badge-yellow');
        ?>
            <tr>
                <td><?= e(formatDateAr($r['attendance_date'], true)) ?></td>
                <td><?= e($r['activity_name']) ?></td>
                <td><?= e($r['choir_name']) ?></td>
                <td><span class="badge <?= $badgeClass ?>"><?= e(attendanceStatusLabel($status)) ?></span></td>
                <td><?= e($r['excuse_reason'] ?? '—') ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
<?php endif; ?>