<h1 class="page-title"><?= e(__('nav.dashboard')) ?></h1>
<p style="color:var(--muted);margin:-16px 0 20px;font-size:15px">
    نظرة سريعة على حضور الخدام
</p>

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
    <button type="submit" class="btn btn-primary">تطبيق الفلاتر</button>
</form>

<div class="kpi-grid">
    <div class="kpi kpi-purple kpi-icon">
        <div class="icon">👥</div>
        <div class="content">
            <b><?= (int)$stats['total_servants'] ?></b>
            <small>إجمالي الخدام</small>
        </div>
    </div>
    <div class="kpi kpi-green kpi-icon">
        <div class="icon">✅</div>
        <div class="content">
            <b><?= (int)$stats['present'] ?></b>
            <small>الحضور</small>
        </div>
    </div>
    <div class="kpi kpi-red kpi-icon">
        <div class="icon">❌</div>
        <div class="content">
            <b><?= (int)$stats['absent'] ?></b>
            <small>الغياب</small>
        </div>
    </div>
    <div class="kpi kpi-sky kpi-icon">
        <div class="icon">📝</div>
        <div class="content">
            <b><?= (int)$stats['excused'] ?></b>
            <small>بعذر</small>
        </div>
    </div>
    <div class="kpi kpi-yellow kpi-icon">
        <div class="icon">📊</div>
        <div class="content">
            <b><?= e(formatPercent((float)$stats['rate'])) ?></b>
            <small>نسبة الحضور</small>
        </div>
    </div>
</div>

<?php if ($stats['total_records'] > 0): ?>
    <div class="box" style="margin-top:24px">
        <h3 style="margin-top:0">📈 ملخص سريع</h3>
        <p style="color:var(--muted)">
            خلال الفترة المحددة:
            <b><?= (int)$stats['total_records'] ?></b> سجل حضور،
            منهم <b style="color:var(--green)"><?= (int)$stats['present'] ?></b> حاضر،
            <b style="color:var(--red)"><?= (int)$stats['absent'] ?></b> غائب،
            و<b style="color:var(--sky)"><?= (int)$stats['excused'] ?></b> بعذر.
        </p>
    </div>
<?php else: ?>
    <div class="empty-state">
        لا توجد بيانات حضور بعد. ابدأ بتسجيل حضور من صفحة
        <a href="<?= e(appBaseUrl()) ?>/attendance/create" style="color:var(--orange);text-decoration:underline">
            تسجيل حضور
        </a>.
    </div>
<?php endif; ?>