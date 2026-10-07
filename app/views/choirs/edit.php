<div class="page-head">
    <h1 class="page-title"><?= e(__('choirs.edit')) ?></h1>
    <a href="<?= e(appBaseUrl()) ?>/choirs" class="btn">رجوع</a>
</div>

<form method="post" action="<?= e(appBaseUrl()) ?>/choirs/update" class="form-box">
    <?= csrfField() ?>
    <input type="hidden" name="id" value="<?= (int)$choir['id'] ?>">

    <label>
        <span><?= e(__('choirs.name')) ?> *</span>
        <input type="text" name="name" required value="<?= e($choir['name']) ?>">
    </label>

    <label>
        <span><?= e(__('choirs.description')) ?></span>
        <textarea name="description" rows="3"><?= e($choir['description'] ?? '') ?></textarea>
    </label>

    <label class="check">
        <input type="checkbox" name="is_active" value="1" <?= $choir['is_active'] ? 'checked' : '' ?>>
        <span><?= e(__('choirs.is_active')) ?></span>
    </label>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary">حفظ</button>
        <a href="<?= e(appBaseUrl()) ?>/choirs" class="btn">إلغاء</a>
    </div>
</form>