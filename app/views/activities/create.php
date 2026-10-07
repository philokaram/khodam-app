<div class="page-header">
    <div class="page-header-text">
        <h1>إضافة نشاط</h1>
        <p>أضف نشاطاً جديداً يمكن تسجيل الحضور فيه</p>
    </div>
    <div class="page-header-actions">
        <a href="<?= e(appBaseUrl()) ?>/activities" class="btn">← رجوع</a>
    </div>
</div>

<form id="activityCreateForm" class="form-container">
    <input type="hidden" name="_csrf_token" value="<?= e(csrfToken()) ?>">

    <div class="form-group">
        <label class="form-label" for="actName">اسم النشاط *</label>
        <input type="text" id="actName" name="name" required
               placeholder="مثال: القداس، التسبحة، الافتقاد..."
               value="<?= e($_SESSION['_old']['name'] ?? '') ?>">
        <div class="form-hint">اسم مختصر وواضح</div>
    </div>

    <div class="form-group">
        <label class="form-label" for="actDesc">الوصف</label>
        <textarea id="actDesc" name="description" rows="3"
                  placeholder="وصف اختياري للنشاط"><?= e($_SESSION['_old']['description'] ?? '') ?></textarea>
    </div>

    <label class="check">
        <input type="checkbox" name="is_active" value="1" checked>
        <span>نشط (يظهر في قوائم تسجيل الحضور)</span>
    </label>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary" id="submitBtn">
            حفظ النشاط
        </button>
        <a href="<?= e(appBaseUrl()) ?>/activities" class="btn">إلغاء</a>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var form = document.getElementById('activityCreateForm');
    var submitBtn = document.getElementById('submitBtn');

    form.addEventListener('submit', async function(e) {
        e.preventDefault();

        var name = document.getElementById('actName').value.trim();
        var description = document.getElementById('actDesc').value.trim();
        var isActive = form.querySelector('input[name="is_active"]').checked;

        if (!name) {
            if (typeof toast === 'function') toast('يرجى إدخال اسم النشاط', 'error');
            return;
        }

        var origText = submitBtn.textContent;
        submitBtn.disabled = true;
        submitBtn.textContent = 'جاري الحفظ...';

        var csrf = document.querySelector('meta[name="csrf-token"]');
        csrf = csrf ? csrf.content : '';

        try {
            var res = await fetch('/api/activities/create', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-Token': csrf
                },
                body: JSON.stringify({
                    name: name,
                    description: description,
                    is_active: isActive ? 1 : 0
                })
            });

            var j = await res.json();
            console.log('[create activity]', j);

            if (j.success) {
                if (typeof toast === 'function') toast('✅ ' + (j.message || 'تم الحفظ'), 'success');
                setTimeout(function() {
                    window.location.href = window.APP_URL + '/activities';
                }, 800);
            } else {
                var errMsg = j.message || 'فشل الحفظ';
                if (j.errors && Object.keys(j.errors).length) {
                    errMsg += ' — ' + Object.values(j.errors).join('، ');
                }
                if (typeof toast === 'function') toast('❌ ' + errMsg, 'error');
                else alert(errMsg);
                submitBtn.disabled = false;
                submitBtn.textContent = origText;
            }
        } catch (err) {
            console.error('[create activity] error:', err);
            if (typeof toast === 'function') toast('خطأ: ' + err.message, 'error');
            else alert('خطأ: ' + err.message);
            submitBtn.disabled = false;
            submitBtn.textContent = origText;
        }
    });
});
</script>