<div class="page-header">
    <div class="page-header-text">
        <h1>سجل حضوري</h1>
        <p>كل سجلات حضورك السابقة</p>
    </div>
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
    <label>
        <span>النشاط</span>
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
    <?php if (!empty(array_filter($filters))): ?>
        <a href="<?= e(appBaseUrl()) ?>/servant/history" class="btn">إعادة تعيين</a>
    <?php endif; ?>
</form>

<?php if (empty($records)): ?>
    <div class="empty-state">
        <h3 style="margin:0 0 8px;color:var(--text-primary)">لا توجد سجلات</h3>
        <p>لم يتم تسجيل حضورك بعد</p>
    </div>
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
            $badgeClass = match ($r['status']) {
                'present' => 'badge-success',
                'absent'  => 'badge-danger',
                'excused' => 'badge-warning',
                default   => 'badge-neutral',
            };
        ?>
            <tr>
                <td><?= e(formatDateAr($r['attendance_date'], true)) ?></td>
                <td><?= e($r['activity_name']) ?></td>
                <td><?= e($r['choir_name']) ?></td>
                <td>
                    <span class="badge <?= $badgeClass ?>">
                        <?= e(attendanceStatusLabel($r['status'])) ?>
                    </span>
                </td>
                <td><?= e($r['excuse_reason'] ?? '—') ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
    <p style="color:var(--text-muted);font-size:13px;margin-top:10px">
        عرض <?= count($records) ?> سجل
    </p>
<?php endif; ?>