<div class="page-header">
    <div class="page-header-text">
        <h1>تعديل الخورس</h1>
        <p><?= e($choir['name']) ?></p>
    </div>
    <div class="page-header-actions">
        <a href="<?= e(appBaseUrl()) ?>/choirs" class="btn">← رجوع</a>
    </div>
</div>

<form id="choirEditForm" class="form-container"
      data-id="<?= (int)$choir['id'] ?>">
    <input type="hidden" name="_csrf_token" value="<?= e(csrfToken()) ?>">

    <div class="form-group">
        <label class="form-label" for="choirName">اسم الخورس *</label>
        <input type="text" id="choirName" name="name" required
               value="<?= e($choir['name']) ?>">
    </div>

    <div class="form-group">
        <label class="form-label" for="choirDesc">الوصف</label>
        <textarea id="choirDesc" name="description" rows="3"><?= e($choir['description'] ?? '') ?></textarea>
    </div>

    <label class="check">
        <input type="checkbox" name="is_active" value="1" <?= $choir['is_active'] ? 'checked' : '' ?>>
        <span>نشط</span>
    </label>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary" id="submitBtn">
            حفظ التعديلات
        </button>
        <a href="<?= e(appBaseUrl()) ?>/choirs" class="btn">إلغاء</a>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var form = document.getElementById('choirEditForm');
    var submitBtn = document.getElementById('submitBtn');
    var id = parseInt(form.dataset.id, 10);

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
            var res = await fetch('/api/choirs/update', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-Token': csrf
                },
                body: JSON.stringify({
                    id: id,
                    name: name,
                    description: description,
                    is_active: isActive ? 1 : 0
                })
            });

            var j = await res.json();

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
            console.error('[edit choir] error:', err);
            if (typeof toast === 'function') toast('خطأ: ' + err.message, 'error');
            else alert('خطأ: ' + err.message);
            submitBtn.disabled = false;
            submitBtn.textContent = origText;
        }
    });
});
</script>