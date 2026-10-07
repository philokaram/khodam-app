<h1 class="page-header-text" style="margin-bottom:24px">تسجيل الحضور</h1>

<!-- Filters -->
<div class="filters-bar" id="attendanceFilters">
    <label>
        <span>النشاط *</span>
        <select id="filterActivity" required>
            <option value="">اختر النشاط...</option>
            <?php foreach ($activities as $a): ?>
                <option value="<?= (int)$a['id'] ?>" <?= ($activityId == $a['id']) ? 'selected' : '' ?>>
                    <?= e($a['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </label>
    <label>
        <span>الخورس *</span>
        <select id="filterChoir" required>
            <option value="">اختر الخورس...</option>
            <?php foreach ($choirs as $c): ?>
                <option value="<?= (int)$c['id'] ?>" <?= ($choirId == $c['id']) ? 'selected' : '' ?>>
                    <?= e($c['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </label>
    <label>
        <span>التاريخ *</span>
        <input type="date" id="filterDate" value="<?= e($date) ?>" required>
    </label>
    <button type="button" id="btnLoadServants" class="btn btn-primary">
        عرض الخدام
    </button>
</div>

<!-- Container for the form -->
<div id="attendanceContainer">
    <div class="empty-state">
        اختر النشاط والخورس والتاريخ ثم اضغط "عرض الخدام"
    </div>
</div>

<script>
window.ATTENDANCE_CONFIG = {
    baseUrl: <?= json_encode(appBaseUrl()) ?>,
    csrf: <?= json_encode(csrfToken()) ?>,
    activities: <?= json_encode(array_map(fn($a) => ['id' => (int)$a['id'], 'name' => $a['name']], $activities)) ?>,
    choirs: <?= json_encode(array_map(fn($c) => ['id' => (int)$c['id'], 'name' => $c['name']], $choirs)) ?>,
};
console.log('[attendance] Config:', window.ATTENDANCE_CONFIG);
</script>

<!-- ⚠️ مهم: تحميل ملف JavaScript -->
<script src="<?= e(appBaseUrl()) ?>/assets/js/attendance.js?v=<?= time() ?>"></script>