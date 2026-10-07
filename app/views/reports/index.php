<div class="page-head">
    <h1 class="page-title"><?= e(__('nav.reports')) ?></h1>
    <button type="button" class="btn" onclick="window.print()">🖨️ طباعة</button>
</div>

<form method="get" class="filters-bar">
    <label>
        <span><?= e(__('reports.from')) ?></span>
        <input type="date" name="from" value="<?= e($filters['from'] ?? '') ?>">
    </label>
    <label>
        <span><?= e(__('reports.to')) ?></span>
        <input type="date" name="to" value="<?= e($filters['to'] ?? '') ?>">
    </label>
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
            <option value="excused" <?= ($filters['status'] === 'excused') ? 'selected' : '' ?>>لديه عذر</option>
        </select>
    </label>
    <button type="submit" class="btn btn-primary">تطبيق</button>
    <a href="<?= e(appBaseUrl()) ?>/reports" class="btn">إعادة تعيين</a>
</form>

<div class="kpi-grid" style="margin-bottom:20px">
    <div class="kpi kpi-green">
        <b><?= (int)$stats['present'] ?></b>
        <small>الحضور</small>
    </div>
    <div class="kpi kpi-red">
        <b><?= (int)$stats['absent'] ?></b>
        <small>الغياب</small>
    </div>
    <div class="kpi kpi-sky">
        <b><?= (int)$stats['excused'] ?></b>
        <small>الغياب بعذر</small>
    </div>
    <div class="kpi kpi-yellow">
        <b><?= e(formatPercent((float)$stats['rate'])) ?></b>
        <small>نسبة الحضور</small>
    </div>
    <div class="kpi kpi-orange">
        <b><?= (int)$stats['total_records'] ?></b>
        <small>إجمالي السجلات</small>
    </div>
</div>

<?php if (empty($records)): ?>
    <div class="empty-state">
        لا توجد سجلات مطابقة للفلاتر.
    </div>
<?php else: ?>
    <div style="overflow-x:auto">
    <table class="table">
        <thead>
            <tr>
                <th>التاريخ</th>
                <th>الخادم</th>
                <th>الخورس</th>
                <th>النشاط</th>
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
                <td><?= e(formatDateAr($r['attendance_date'])) ?></td>
                <td>
                    <a href="<?= e(appBaseUrl()) ?>/reports/servant?id=<?= (int)$r['servant_id'] ?>" style="text-decoration:underline">
                        <?= e($r['servant_name']) ?>
                    </a>
                </td>
                <td><?= e($r['choir_name']) ?></td>
                <td><?= e($r['activity_name']) ?></td>
                <td><span class="badge <?= $badgeClass ?>"><?= e(attendanceStatusLabel($status)) ?></span></td>
                <td><?= e($r['excuse_reason'] ?? '—') ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
    <p style="color:var(--muted);font-size:13px;margin-top:10px">
        عرض <?= count($records) ?> سجل (الحد الأقصى 500).
    </p>
<?php endif; ?>