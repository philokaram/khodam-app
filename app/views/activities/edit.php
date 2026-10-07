<div class="page-header">
    <div class="page-header-text">
        <h1>تعديل النشاط</h1>
        <p><?= e($activity['name']) ?></p>
    </div>
    <div class="page-header-actions">
        <a href="<?= e(appBaseUrl()) ?>/activities" class="btn">← رجوع</a>
    </div>
</div>

<form id="activityEditForm" class="form-container"
      data-id="<?= (int)$activity['id'] ?>">
    <input type="hidden" name="_csrf_token" value="<?= e(csrfToken()) ?>">

    <div class="form-group">
        <label class="form-label" for="actName">اسم النشاط *</label>
        <input type="text" id="actName" name="name" required
               value="<?= e($activity['name']) ?>">
    </div>

    <div class="form-group">
        <label class="form-label" for="actDesc">الوصف</label>
        <textarea id="actDesc" name="description" rows="3"><?= e($activity['description'] ?? '') ?></textarea>
    </div>

    <label class="check">
        <input type="checkbox" name="is_active" value="1" <?= $activity['is_active'] ? 'checked' : '' ?>>
        <span>نشط</span>
    </label>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary" id="submitBtn">
            حفظ التعديلات
        </button>
        <a href="<?= e(appBaseUrl()) ?>/activities" class="btn">إلغاء</a>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var form = document.getElementById('activityEditForm');
    var submitBtn = document.getElementById('submitBtn');
    var id = parseInt(form.dataset.id, 10);

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
            var res = await fetch('/api/activities/update', {
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
            console.error('[edit activity] error:', err);
            if (typeof toast === 'function') toast('خطأ: ' + err.message, 'error');
            else alert('خطأ: ' + err.message);
            submitBtn.disabled = false;
            submitBtn.textContent = origText;
        }
    });
});
</script>