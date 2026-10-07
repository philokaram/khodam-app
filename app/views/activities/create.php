<div class="page-head">
    <h1 class="page-title"><?= e(__('activities.add')) ?></h1>
    <a href="<?= e(appBaseUrl()) ?>/activities" class="btn">رجوع</a>
</div>

<form method="post" action="<?= e(appBaseUrl()) ?>/activities/store" class="form-box">
    <?= csrfField() ?>

    <label>
        <span><?= e(__('activities.name')) ?> *</span>
        <input type="text" name="name" required value="<?= e($_SESSION['_old']['name'] ?? '') ?>">
    </label>

    <label>
        <span><?= e(__('activities.description')) ?></span>
        <textarea name="description" rows="3"><?= e($_SESSION['_old']['description'] ?? '') ?></textarea>
    </label>

    <label class="check">
        <input type="checkbox" name="is_active" value="1" checked>
        <span><?= e(__('activities.is_active')) ?></span>
    </label>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary">حفظ</button>
        <a href="<?= e(appBaseUrl()) ?>/activities" class="btn">إلغاء</a>
    </div>
</form>
<?php unset($_SESSION['_old']); ?>