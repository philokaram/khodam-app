<?php
$emojis = ['😀', '😎', '🥳', '🤓', '😇', '🙂', '😄', '😊', '😁', '🤗', '🙌', '👨‍🎓', '👨‍💼', '👨‍🏫', '🧑', '👤'];
?>

<form method="post" action="<?= e(appBaseUrl()) ?>/servants/store" class="form-box">
    <?= csrfField() ?>

    <label>
        <span><?= e(__('servants.name')) ?> *</span>
        <input type="text" name="name" required value="<?= e($_SESSION['_old']['name'] ?? '') ?>">
    </label>

    <label>
        <span>الإيموجي</span>
        <div style="display:grid;grid-template-columns:repeat(8,1fr);gap:6px;padding:10px;background:var(--bg-alt);border:var(--border-soft);border-radius:var(--radius-sm)">
            <?php foreach ($emojis as $e): ?>
                <button type="button" class="emoji-pick" data-emoji="<?= $e ?>"
                        style="font-size:24px;padding:8px;border:var(--border-soft);border-radius:10px;background:var(--card);cursor:pointer">
                    <?= $e ?>
                </button>
            <?php endforeach; ?>
        </div>
        <input type="hidden" name="emoji" id="emojiInput" value="<?= e($_SESSION['_old']['emoji'] ?? '👤') ?>">
    </label>

    <label>
        <span><?= e(__('servants.choir')) ?> *</span>
        <select name="choir_id" required>
            <option value="">اختر...</option>
            <?php foreach ($choirs as $c): ?>
                <option value="<?= (int)$c['id'] ?>">
                    <?= e($c['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </label>

    <label>
        <span><?= e(__('servants.code')) ?></span>
        <input type="text" name="code" value="<?= e($_SESSION['_old']['code'] ?? '') ?>" dir="ltr">
    </label>

    <label>
        <span><?= e(__('servants.phone')) ?></span>
        <input type="tel" name="phone" value="<?= e($_SESSION['_old']['phone'] ?? '') ?>" dir="ltr">
    </label>

    <label>
        <span><?= e(__('servants.join_date')) ?></span>
        <input type="date" name="join_date" value="<?= e($_SESSION['_old']['join_date'] ?? '') ?>">
    </label>

    <label>
        <span><?= e(__('servants.status')) ?></span>
        <select name="status">
            <option value="active">نشط</option>
            <option value="inactive">غير نشط</option>
        </select>
    </label>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary">حفظ</button>
        <a href="<?= e(appBaseUrl()) ?>/servants" class="btn">إلغاء</a>
    </div>
</form>

<script>
document.querySelectorAll('.emoji-pick').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.emoji-pick').forEach(b => {
            b.style.background = 'var(--card)';
            b.style.borderColor = 'var(--ink)';
        });
        btn.style.background = 'var(--yellow)';
        btn.style.borderColor = 'var(--yellow)';
        document.getElementById('emojiInput').value = btn.dataset.emoji;
    });
});
</script>
<?php unset($_SESSION['_old']); ?>