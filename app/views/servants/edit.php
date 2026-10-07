<div class="page-header">
    <div class="page-header-text">
        <h1><?= e(__('servants.edit')) ?></h1>
        <p><?= e($servant['name']) ?></p>
    </div>
    <div class="page-header-actions">
        <a href="<?= e(appBaseUrl()) ?>/servants/show?id=<?= (int)$servant['id'] ?>" class="btn">← عرض</a>
        <a href="<?= e(appBaseUrl()) ?>/servants" class="btn">القائمة</a>
    </div>
</div>

<form id="servantEditForm" class="form-container"
      data-id="<?= (int)$servant['id'] ?>">
    <input type="hidden" name="_csrf_token" value="<?= e(csrfToken()) ?>">

    <div class="form-group">
        <label class="form-label" for="servantName"><?= e(__('servants.name')) ?> *</label>
        <input type="text" id="servantName" name="name" required
               value="<?= e($servant['name']) ?>">
    </div>

    <div class="form-group">
        <label class="form-label">الإيموجي</label>
        <div class="emoji-picker" style="display:grid;grid-template-columns:repeat(8,1fr);gap:6px;padding:10px;background:var(--bg-subtle);border:1px solid var(--border-color);border-radius:var(--radius)">
            <?php
            $emojis = ['😀','😎','🥳','🤓','😇','🙂','😄','😊','😁','🤗','🙌','👨‍🎓','👨‍💼','👨‍🏫','🧑','👤'];
            $selectedEmoji = $servant['emoji'] ?? '👤';
            foreach ($emojis as $e):
            ?>
                <button type="button" class="emoji-btn <?= $selectedEmoji === $e ? 'selected' : '' ?>"
                        data-emoji="<?= $e ?>"
                        style="aspect-ratio:1;display:grid;place-items:center;font-size:22px;background:var(--bg-surface);border:2px solid <?= $selectedEmoji === $e ? 'var(--primary)' : 'transparent' ?>;border-radius:10px;cursor:pointer;transition:all .15s;padding:0">
                    <?= $e ?>
                </button>
            <?php endforeach; ?>
        </div>
        <input type="hidden" name="emoji" id="emojiInput" value="<?= e($selectedEmoji) ?>">
    </div>

    <div class="form-group">
        <label class="form-label" for="servantChoir"><?= e(__('servants.choir')) ?> *</label>
        <select id="servantChoir" name="choir_id" required>
            <option value="">اختر الخورس...</option>
            <?php foreach ($choirs as $c): ?>
                <option value="<?= (int)$c['id'] ?>" <?= ($servant['choir_id'] == $c['id']) ? 'selected' : '' ?>>
                    <?= e($c['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-group">
        <label class="form-label" for="servantCode"><?= e(__('servants.code')) ?></label>
        <input type="text" id="servantCode" name="code" dir="ltr"
               value="<?= e($servant['code'] ?? '') ?>">
    </div>

    <div class="form-group">
        <label class="form-label" for="servantPhone"><?= e(__('servants.phone')) ?></label>
        <input type="tel" id="servantPhone" name="phone" dir="ltr"
               value="<?= e($servant['phone'] ?? '') ?>">
    </div>

    <div class="form-group">
        <label class="form-label" for="servantJoinDate"><?= e(__('servants.join_date')) ?></label>
        <input type="date" id="servantJoinDate" name="join_date"
               value="<?= e($servant['join_date'] ?? '') ?>">
    </div>

    <div class="form-group">
        <label class="form-label" for="servantStatus"><?= e(__('servants.status')) ?></label>
        <select id="servantStatus" name="status">
            <option value="active" <?= $servant['status'] === 'active' ? 'selected' : '' ?>>نشط</option>
            <option value="inactive" <?= $servant['status'] === 'inactive' ? 'selected' : '' ?>>غير نشط</option>
        </select>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary" id="submitBtn">
            حفظ التعديلات
        </button>
        <a href="<?= e(appBaseUrl()) ?>/servants" class="btn">إلغاء</a>
    </div>
</form>

<style>
.emoji-btn:hover { transform: scale(1.1); background: var(--bg-hover); }
.emoji-btn.selected { background: var(--primary-soft) !important; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var form = document.getElementById('servantEditForm');
    var submitBtn = document.getElementById('submitBtn');
    var emojiInput = document.getElementById('emojiInput');
    var id = parseInt(form.dataset.id, 10);

    document.querySelectorAll('.emoji-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.emoji-btn').forEach(function(b) {
                b.classList.remove('selected');
                b.style.borderColor = 'transparent';
            });
            btn.classList.add('selected');
            btn.style.borderColor = 'var(--primary)';
            emojiInput.value = btn.dataset.emoji;
        });
    });

    form.addEventListener('submit', async function(e) {
        e.preventDefault();

        var name = document.getElementById('servantName').value.trim();
        var choirId = parseInt(document.getElementById('servantChoir').value, 10);
        var code = document.getElementById('servantCode').value.trim();
        var phone = document.getElementById('servantPhone').value.trim();
        var joinDate = document.getElementById('servantJoinDate').value;
        var status = document.getElementById('servantStatus').value;
        var emoji = emojiInput.value;

        if (!name || !choirId) {
            if (typeof toast === 'function') toast('يرجى إكمال الحقول المطلوبة', 'error');
            return;
        }

        var origText = submitBtn.textContent;
        submitBtn.disabled = true;
        submitBtn.textContent = 'جاري الحفظ...';

        var csrf = document.querySelector('meta[name="csrf-token"]');
        csrf = csrf ? csrf.content : '';

        try {
            var res = await fetch('/api/servants/update', {
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
                    emoji: emoji,
                    choir_id: choirId,
                    code: code || null,
                    phone: phone || null,
                    join_date: joinDate || null,
                    status: status
                })
            });

            var j = await res.json();

            if (j.success) {
                if (typeof toast === 'function') toast('✅ ' + (j.message || 'تم الحفظ'), 'success');
                setTimeout(function() {
                    window.location.href = window.APP_URL + '/servants';
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
            console.error('[edit servant] error:', err);
            if (typeof toast === 'function') toast('خطأ: ' + err.message, 'error');
            else alert('خطأ: ' + err.message);
            submitBtn.disabled = false;
            submitBtn.textContent = origText;
        }
    });
});
</script>