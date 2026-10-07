<div class="page-head">
    <h1 class="page-title">إضافة مستخدم</h1>
    <a href="<?= e(appBaseUrl()) ?>/users" class="btn">رجوع</a>
</div>

<form method="post" action="<?= e(appBaseUrl()) ?>/users/store" class="form-box">
    <?= csrfField() ?>

    <label>
        <span>الاسم الكامل *</span>
        <input type="text" name="name" required>
    </label>

    <label>
        <span>اسم المستخدم *</span>
        <input type="text" name="username" required dir="ltr">
    </label>

    <label>
        <span>البريد الإلكتروني</span>
        <input type="email" name="email" dir="ltr">
    </label>

    <label>
        <span>كلمة المرور *</span>
        <input type="password" name="password" required minlength="6">
    </label>

    <label>
        <span>الدور *</span>
        <select name="role_id" required>
            <option value="">اختر...</option>
            <?php foreach ($roles as $r): ?>
                <option value="<?= (int)$r['id'] ?>"><?= e($r['label_ar']) ?></option>
            <?php endforeach; ?>
        </select>
    </label>

    <label>
        <span>الخورس (اختياري)</span>
        <select name="choir_id">
            <option value="">—</option>
            <?php foreach ($choirs as $c): ?>
                <option value="<?= (int)$c['id'] ?>"><?= e($c['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </label>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary">حفظ</button>
        <a href="<?= e(appBaseUrl()) ?>/users" class="btn">إلغاء</a>
    </div>
</form>