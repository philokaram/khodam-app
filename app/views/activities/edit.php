<div class="page-head">
    <h1 class="page-title"><?= e(__('activities.edit')) ?></h1>
    <a href="<?= e(appBaseUrl()) ?>/activities" class="btn">رجوع</a>
</div>

<form method="post" action="<?= e(appBaseUrl()) ?>/activities/update" class="form-box">
    <?= csrfField() ?>
    <input type="hidden" name="id" value="<?= (int)$activity['id'] ?>">

    <label>
        <span><?= e(__('activities.name')) ?> *</span>
        <input type="text" name="name" required value="<?= e($activity['name']) ?>">
    </label>

    <label>
        <span><?= e(__('activities.description')) ?></span>
        <textarea name="description" rows="3"><?= e($activity['description'] ?? '') ?></textarea>
    </label>

    <label class="check">
        <input type="checkbox" name="is_active" value="1" <?= $activity['is_active'] ? 'checked' : '' ?>>
        <span><?= e(__('activities.is_active')) ?></span>
    </label>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary">حفظ</button>
        <a href="<?= e(appBaseUrl()) ?>/activities" class="btn">إلغاء</a>
    </div>
</form>