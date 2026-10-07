<div class="page-head">
    <h1 class="page-title"><?= e(__('choirs.add')) ?></h1>
    <a href="<?= e(appBaseUrl()) ?>/choirs" class="btn">رجوع</a>
</div>

<form method="post" action="<?= e(appBaseUrl()) ?>/choirs/store" class="form-box">
    <?= csrfField() ?>

    <label>
        <span><?= e(__('choirs.name')) ?> *</span>
        <input type="text" name="name" required value="<?= e($_SESSION['_old']['name'] ?? '') ?>">
    </label>

    <label>
        <span><?= e(__('choirs.description')) ?></span>
        <textarea name="description" rows="3"><?= e($_SESSION['_old']['description'] ?? '') ?></textarea>
    </label>

    <label class="check">
        <input type="checkbox" name="is_active" value="1" checked>
        <span><?= e(__('choirs.is_active')) ?></span>
    </label>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary">حفظ</button>
        <a href="<?= e(appBaseUrl()) ?>/choirs" class="btn">إلغاء</a>
    </div>
</form>
<?php unset($_SESSION['_old']); ?>