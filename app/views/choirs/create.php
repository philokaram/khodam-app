<div class="page-header">
    <div class="page-header-text">
        <h1>إضافة خورس</h1>
        <p>أضف خورساً جديداً لمتابعة خدمة الخدام فيه</p>
    </div>
    <div class="page-header-actions">
        <a href="<?= e(appBaseUrl()) ?>/choirs" class="btn">← رجوع</a>
    </div>
</div>

<form id="choirCreateForm" class="form-container">
    <input type="hidden" name="_csrf_token" value="<?= e(csrfToken()) ?>">

    <div class="form-group">
        <label class="form-label" for="choirName">اسم الخورس *</label>
        <input type="text" id="choirName" name="name" required
               placeholder="مثال: خورس مارجرجس، خورس مارمرقس..."
               value="<?= e($_SESSION['_old']['name'] ?? '') ?>">
        <div class="form-hint">اسم واضح ومميز</div>
    </div>

    <div class="form-group">
        <label class="form-label" for="choirDesc">الوصف</label>
        <textarea id="choirDesc" name="description" rows="3"
                  placeholder="وصف اختياري للخورس"><?= e($_SESSION['_old']['description'] ?? '') ?></textarea>
    </div>

    <label class="check">
        <input type="checkbox" name="is_active" value="1" checked>
        <span>نشط (يظهر في القوائم)</span>
    </label>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary" id="submitBtn">
            حفظ الخورس
        </button>
        <a href="<?= e(appBaseUrl()) ?>/choirs" class="btn">إلغاء</a>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var form = document.getElementById('choirCreateForm');
    var submitBtn = document.getElementById('submitBtn');

    form.addEventListener('submit', async function(e) {
        e.preventDefault();

        var name = document.getElementById('choirName').value.trim();
        var description = document.getElementById('choirDesc').value.trim();
        var isActive = form.querySelector('input[name="is_active"]').checked;

        if (!name) {
            if (typeof toast === 'function') toast('يرجى إدخال اسم الخورس', 'error');
            return;
        }

        var origText = submitBtn.textContent;
        submitBtn.disabled = true;
        submitBtn.textContent = 'جاري الحفظ...';

        var csrf = document.querySelector('meta[name="csrf-token"]');
        csrf = csrf ? csrf.content : '';

        try {
            var res = await fetch('/api/choirs/create', {
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
            console.log('[create choir]', j);

            if (j.success) {
                if (typeof toast === 'function') toast('✅ ' + (j.message || 'تم الحفظ'), 'success');
                setTimeout(function() {
                    window.location.href = window.APP_URL + '/choirs';
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
            console.error('[create choir] error:', err);
            if (typeof toast === 'function') toast('خطأ: ' + err.message, 'error');
            else alert('خطأ: ' + err.message);
            submitBtn.disabled = false;
            submitBtn.textContent = origText;
        }
    });
});
</script>