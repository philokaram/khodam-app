<h1 class="page-title"><?= e(__('attendance.register')) ?></h1>

<form method="get" class="filters-bar">
    <label>
        <span><?= e(__('attendance.activity')) ?></span>
        <select name="activity_id" required>
            <option value="">اختر...</option>
            <?php foreach ($activities as $a): ?>
                <option value="<?= (int)$a['id'] ?>" <?= ($activityId == $a['id']) ? 'selected' : '' ?>>
                    <?= e($a['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </label>
    <label>
        <span><?= e(__('attendance.choir')) ?></span>
        <select name="choir_id" required>
            <option value="">اختر...</option>
            <?php foreach ($choirs as $c): ?>
                <option value="<?= (int)$c['id'] ?>" <?= ($choirId == $c['id']) ? 'selected' : '' ?>>
                    <?= e($c['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </label>
    <label>
        <span><?= e(__('attendance.date')) ?></span>
        <input type="date" name="date" value="<?= e($date) ?>" required>
    </label>
    <button type="submit" class="btn btn-primary">عرض الخدام</button>
</form>

<?php if ($activityId && $choirId && $servants): ?>
    <div class="attendance-toolbar">
        <button type="button" id="btnAllPresent" class="btn btn-yellow">✓ <?= e(__('attendance.mark_all_present')) ?></button>
        <input type="search" id="servantSearch" placeholder="<?= e(__('servants.search')) ?>" class="search-input">
    </div>

    <form id="attendanceForm"
          data-activity="<?= (int)$activityId ?>"
          data-choir="<?= (int)$choirId ?>"
          data-date="<?= e($date) ?>">
        <?= csrfField() ?>
        <div class="attendance-list" id="attendanceList">
        <?php foreach ($servants as $s):
            $sid = (int)$s['id'];
            $cur = $existing[$sid] ?? null;
            $status = $cur['status'] ?? null;
            $reason = $cur['excuse_reason'] ?? '';
        ?>
            <div class="attendance-row" data-servant-id="<?= $sid ?>" data-name="<?= e($s['name']) ?>">
                <div class="servant-name"><?= e($s['name']) ?></div>
                <div class="status-group">
                    <button type="button" class="status-btn <?= $status === 'present' ? 'on present' : '' ?>" data-status="present"><?= e(__('status.present')) ?></button>
                    <button type="button" class="status-btn <?= $status === 'absent'  ? 'on absent'  : '' ?>" data-status="absent"><?= e(__('status.absent')) ?></button>
                    <button type="button" class="status-btn <?= $status === 'excused' ? 'on excused' : '' ?>" data-status="excused"><?= e(__('status.excused')) ?></button>
                </div>
                <div class="excuse-box" <?= $status === 'excused' ? '' : 'hidden' ?>>
                    <input type="text" class="excuse-input" placeholder="<?= e(__('attendance.excuse_placeholder')) ?>" value="<?= e($reason) ?>">
                </div>
            </div>
        <?php endforeach; ?>
        </div>

        <div class="attendance-actions">
            <button type="submit" class="btn btn-primary btn-lg"><?= e(__('attendance.save')) ?></button>
        </div>
    </form>
<?php elseif ($activityId && $choirId): ?>
    <div class="empty-state"><?= e(__('attendance.no_servants')) ?></div>
<?php else: ?>
    <div class="empty-state"><?= e(__('attendance.select_filters')) ?></div>
<?php endif; ?>