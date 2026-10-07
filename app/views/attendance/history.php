<div class="page-header">
    <div class="page-header-text">
        <h1><?= e(__('attendance.history')) ?></h1>
        <p>
            <?php if (isChoirAdmin()): ?>
                سجلات خورس <?= e(currentUser()['choir_name'] ?? '') ?>
            <?php else: ?>
                كل سجلات الحضور
            <?php endif; ?>
        </p>
    </div>
    <?php if (hasPermission('attendance.create')): ?>
    <div class="page-header-actions">
        <a href="<?= e(appBaseUrl()) ?>/attendance/create" class="btn btn-primary">
            + <?= e(__('attendance.register')) ?>
        </a>
    </div>
    <?php endif; ?>
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

    <?php if (!isChoirAdmin()): ?>
    <label>
        <span><?= e(__('reports.choir')) ?></span>
        <select name="choir_id">
            <option value="">الكل</option>
            <?php foreach ($choirs as $c): ?>
                <option value="<?= (int)$c['id'] ?>" <?= (($filters['choir_id'] ?? 0) == $c['id']) ? 'selected' : '' ?>>
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
                <option value="<?= (int)$a['id'] ?>" <?= (($filters['activity_id'] ?? 0) == $a['id']) ? 'selected' : '' ?>>
                    <?= e($a['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </label>
    <button type="submit" class="btn btn-primary">تطبيق</button>
</form>

<?php if (empty($sessions)): ?>
    <div class="empty-state">لا توجد جلسات حضور مطابقة.</div>
<?php else: ?>
<table class="table">
    <thead>
        <tr>
            <th>التاريخ</th>
            <th>النشاط</th>
            <?php if (!isChoirAdmin()): ?><th>الخورس</th><?php endif; ?>
            <th>عدد السجلات</th>
            <th>الحضور</th>
            <th>النسبة</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($sessions as $s):
        $total = (int)$s['records_count'];
        $present = (int)$s['present_count'];
        $rate = $total > 0 ? round($present / $total * 100, 1) : 0;
    ?>
        <tr>
            <td><?= e(formatDateAr($s['attendance_date'], true)) ?></td>
            <td><?= e($s['activity_name']) ?></td>
            <?php if (!isChoirAdmin()): ?>
                <td><?= e($s['choir_name']) ?></td>
            <?php endif; ?>
            <td><?= $total ?></td>
            <td><?= $present ?></td>
            <td>
                <span class="badge <?= $rate >= 75 ? 'badge-success' : ($rate >= 50 ? 'badge-warning' : 'badge-danger') ?>">
                    <?= $rate ?>%
                </span>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>