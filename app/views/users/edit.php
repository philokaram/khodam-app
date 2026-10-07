<div class="page-header">
    <div class="page-header-text">
        <h1>تعديل المستخدم</h1>
        <p><?= e($user['name']) ?></p>
    </div>
    <div class="page-header-actions">
        <a href="<?= e(appBaseUrl()) ?>/users" class="btn">← رجوع</a>
    </div>
</div>

<form id="userEditForm" class="form-container" data-id="<?= (int)$user['id'] ?>">
    <input type="hidden" name="_csrf_token" value="<?= e(csrfToken()) ?>">

    <div class="form-group">
        <label class="form-label">الاسم الكامل *</label>
        <input type="text" name="name" id="uName" required value="<?= e($user['name']) ?>">
    </div>

    <div class="form-group">
        <label class="form-label">اسم المستخدم *</label>
        <input type="text" name="username" id="uUsername" required dir="ltr"
               value="<?= e($user['username']) ?>">
    </div>

    <div class="form-group">
        <label class="form-label">البريد الإلكتروني</label>
        <input type="email" name="email" id="uEmail" dir="ltr"
               value="<?= e($user['email'] ?? '') ?>">
    </div>

    <div class="form-group">
        <label class="form-label">الدور *</label>
        <select name="role_id" id="uRole" required>
            <?php foreach ($roles as $r): ?>
                <option value="<?= (int)$r['id'] ?>" <?= ($user['role_id'] == $r['id']) ? 'selected' : '' ?>>
                    <?= e($r['label_ar'] ?? $r['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-group">
        <label class="form-label">الخورس</label>
        <select name="choir_id" id="uChoir">
            <option value="">— بدون —</option>
            <?php foreach ($choirs as $c): ?>
                <option value="<?= (int)$c['id'] ?>" <?= ($user['choir_id'] == $c['id']) ? 'selected' : '' ?>>
                    <?= e($c['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-group">
        <label class="form-label">الحالة</label>
        <select name="status" id="uStatus">
            <option value="active" <?= $user['status'] === 'active' ? 'selected' : '' ?>>نشط</option>
            <option value="inactive" <?= $user['status'] === 'inactive' ? 'selected' : '' ?>>معطّل</option>
        </select>
    </div>

    <div style="padding:12px 16px;background:var(--bg-subtle);border-radius:var(--radius);font-size:13px;color:var(--text-muted);margin-top:8px">
        💡 لتغيير كلمة المرور، استخدم زر 🔑 في صفحة المستخدمين.
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary" id="submitBtn">حفظ التعديلات</button>
        <a href="<?= e(appBaseUrl()) ?>/users" class="btn">إلغاء</a>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var form = document.getElementById('userEditForm');
    var btn = document.getElementById('submitBtn');
    var id = parseInt(form.dataset.id, 10);

    form.addEventListener('submit', async function(e) {
        e.preventDefault();

        var data = {
            id: id,
            name: document.getElementById('uName').value.trim(),
            username: document.getElementById('uUsername').value.trim(),
            email: document.getElementById('uEmail').value.trim(),
            role_id: parseInt(document.getElementById('uRole').value, 10),
            choir_id: document.getElementById('uChoir').value || null,
            status: document.getElementById('uStatus').value,
        };

        if (!data.name || !data.username || !data.role_id) {
            toast('يرجى إكمال الحقول المطلوبة', 'error');
            return;
        }

        var orig = btn.textContent;
        btn.disabled = true;
        btn.textContent = 'جاري الحفظ...';

        var csrf = document.querySelector('meta[name="csrf-token"]').content;

        try {
            var res = await fetch('/api/users/update', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-Token': csrf
                },
                body: JSON.stringify(data)
            });
            var j = await res.json();

            if (j.success) {
                toast('✅ ' + j.message, 'success');
                setTimeout(() => window.location.href = window.APP_URL + '/users', 800);
            } else {
                var msg = j.message;
                if (j.errors && Object.keys(j.errors).length) {
                    msg += ' — ' + Object.values(j.errors).join('، ');
                }
                toast('❌ ' + msg, 'error');
                btn.disabled = false;
                btn.textContent = orig;
            }
        } catch (err) {
            toast('خطأ: ' + err.message, 'error');
            btn.disabled = false;
            btn.textContent = orig;
        }
    });
});
</script>