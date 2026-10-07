<div class="attendance-page-head">
    <h1>تسجيل الحضور</h1>
    <p>اختر النشاط والخورس ثم سجّل الحضور</p>
</div>

<!-- Filters -->
<div class="attendance-filters" id="attendanceFilters">
    <label>
        <span>النشاط</span>
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
        <span>الخورس</span>
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
        <span>التاريخ</span>
        <input type="date" id="filterDate" value="<?= e($date) ?>" required>
    </label>

    <button type="button" id="btnLoadServants" class="btn btn-primary">
        عرض الخدام
    </button>
</div>

<!-- Session Info Bar -->
<div id="sessionInfo" class="session-bar" style="display:none">
    <div class="session-bar-item">
        <div class="icon">📅</div>
        <div class="label">
            <small>النشاط</small>
            <strong id="infoActivity">—</strong>
        </div>
    </div>
    <div class="session-bar-divider"></div>
    <div class="session-bar-item">
        <div class="icon">🎵</div>
        <div class="label">
            <small>الخورس</small>
            <strong id="infoChoir">—</strong>
        </div>
    </div>
    <div class="session-bar-divider"></div>
    <div class="session-bar-item">
        <div class="icon">📆</div>
        <div class="label">
            <small>التاريخ</small>
            <strong id="infoDate">—</strong>
        </div>
    </div>
</div>

<!-- Container -->
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
</script>

<script src="<?= e(appBaseUrl()) ?>/assets/js/attendance.js?v=<?= time() ?>"></script>