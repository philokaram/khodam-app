<div class="page-header">
    <div class="page-header-text">
        <h1><?= e(__('servants.add')) ?></h1>
        <p>أضف خادماً جديداً — سيتم إنشاء حساب دخول له تلقائياً</p>
    </div>
    <div class="page-header-actions">
        <a href="<?= e(appBaseUrl()) ?>/servants" class="btn">← رجوع</a>
    </div>
</div>

<form id="servantCreateForm" class="form-container" style="max-width:720px">
    <input type="hidden" name="_csrf_token" value="<?= e(csrfToken()) ?>">

    <!-- البيانات الشخصية -->
    <div style="padding-bottom:20px;border-bottom:1px solid var(--border-color);margin-bottom:20px">
        <h3 style="margin:0 0 16px;font-size:15px;font-weight:800;display:flex;align-items:center;gap:8px">
            <span style="width:32px;height:32px;background:var(--primary-soft);color:var(--primary);border-radius:10px;display:grid;place-items:center">👤</span>
            بيانات الخادم
        </h3>

        <div style="display:grid;gap:16px">
            <div class="form-group">
                <label class="form-label"><?= e(__('servants.name')) ?> *</label>
                <input type="text" id="servantName" required
                       placeholder="الاسم الثلاثي"
                       value="<?= e($_SESSION['_old']['name'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label class="form-label">الإيموجي</label>
                <div id="emojiPicker" style="display:grid;grid-template-columns:repeat(8,1fr);gap:6px;padding:10px;background:var(--bg-subtle);border:1px solid var(--border-color);border-radius:var(--radius)">
                    <?php
                    $emojis = ['😀','😎','🥳','🤓','😇','🙂','😄','😊','😁','🤗','🙌','👨‍🎓','👨‍💼','👨‍🏫','🧑','👤'];
                    $selectedEmoji = $_SESSION['_old']['emoji'] ?? '👤';
                    foreach ($emojis as $e):
                    ?>
                        <button type="button" class="emoji-btn <?= $selectedEmoji === $e ? 'selected' : '' ?>"
                                data-emoji="<?= $e ?>"
                                style="aspect-ratio:1;display:grid;place-items:center;font-size:22px;background:var(--bg-surface);border:2px solid <?= $selectedEmoji === $e ? 'var(--primary)' : 'transparent' ?>;border-radius:10px;cursor:pointer;padding:0">
                            <?= $e ?>
                        </button>
                    <?php endforeach; ?>
                </div>
                <input type="hidden" name="emoji" id="emojiInput" value="<?= e($selectedEmoji) ?>">
            </div>

            <?php if (!isChoirAdmin()): ?>
            <div class="form-group">
                <label class="form-label"><?= e(__('servants.choir')) ?> *</label>
                <select id="servantChoir" required>
                    <option value="">اختر الخورس...</option>
                    <?php foreach ($choirs as $c): ?>
                        <option value="<?= (int)$c['id'] ?>">
                            <?= e($c['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php else: ?>
                <input type="hidden" id="servantChoir" value="<?= (int)($choirs[0]['id'] ?? 0) ?>">
                <div class="form-group">
                    <label class="form-label"><?= e(__('servants.choir')) ?></label>
                    <input type="text" value="<?= e($choirs[0]['name'] ?? '') ?>" disabled
                           style="background:var(--bg-subtle);cursor:not-allowed">
                </div>
            <?php endif; ?>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
                <div class="form-group">
                    <label class="form-label"><?= e(__('servants.code')) ?></label>
                    <input type="text" id="servantCode" dir="ltr" placeholder="S001">
                </div>
                <div class="form-group">
                    <label class="form-label"><?= e(__('servants.phone')) ?></label>
                    <input type="tel" id="servantPhone" dir="ltr" placeholder="01xxxxxxxxx">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label"><?= e(__('servants.join_date')) ?></label>
                <input type="date" id="servantJoinDate" value="<?= date('Y-m-d') ?>">
            </div>
        </div>
    </div>

    <!-- حساب الدخول -->
    <div style="padding-bottom:20px;border-bottom:1px solid var(--border-color);margin-bottom:20px">
        <h3 style="margin:0 0 16px;font-size:15px;font-weight:800;display:flex;align-items:center;gap:8px">
            <span style="width:32px;height:32px;background:var(--warning-soft);color:var(--warning);border-radius:10px;display:grid;place-items:center">🔐</span>
            حساب الدخول
        </h3>
        <p style="margin:0 0 16px;font-size:13px;color:var(--text-muted)">
            سيتمكن الخادم من تسجيل الدخول ورؤية ملفه الشخصي وسجل حضوره
        </p>

        <div style="display:grid;gap:16px">
            <div class="form-group">
                <label class="form-label">اسم المستخدم *</label>
                <input type="text" id="servantUsername" dir="ltr" required
                       placeholder="ahmed" pattern="[a-zA-Z0-9_\.]{3,50}">
                <div class="form-hint">3-50 حرفاً إنجليزياً/أرقام/_. — يجب أن يكون فريداً</div>
            </div>

            <div class="form-group">
                <label class="form-label">كلمة المرور *</label>
                <div style="display:flex;gap:8px">
                    <input type="text" id="servantPassword" dir="ltr" required minlength="6"
                           placeholder="••••••••" style="flex:1">
                    <button type="button" class="btn" onclick="generatePassword()" title="توليد كلمة مرور">🎲</button>
                </div>
                <div class="form-hint">6 أحرف على الأقل — احفظها وأعطها للخادم</div>
            </div>

            <label class="check">
                <input type="checkbox" id="showPassword">
                <span style="font-size:13px">إظهار كلمة المرور</span>
            </label>
        </div>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary" id="submitBtn" style="min-width:160px">
            حفظ الخادم
        </button>
        <a href="<?= e(appBaseUrl()) ?>/servants" class="btn">إلغاء</a>
    </div>
</form>

<style>
.emoji-btn:hover { transform: scale(1.1); background: var(--bg-hover); }
.emoji-btn.selected { background: var(--primary-soft) !important; }
@media (max-width: 640px) {
    form[style*="grid-template-columns:1fr 1fr"] {
        grid-template-columns: 1fr !important;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var form = document.getElementById('servantCreateForm');
    var btn = document.getElementById('submitBtn');
    var emojiInput = document.getElementById('emojiInput');

    // Emoji picker
    document.querySelectorAll('.emoji-btn').forEach(function(b) {
        b.addEventListener('click', function() {
            document.querySelectorAll('.emoji-btn').forEach(function(x) {
                x.classList.remove('selected');
                x.style.borderColor = 'transparent';
            });
            b.classList.add('selected');
            b.style.borderColor = 'var(--primary)';
            emojiInput.value = b.dataset.emoji;
        });
    });

    // Show/hide password
    document.getElementById('showPassword').addEventListener('change', function() {
        var inp = document.getElementById('servantPassword');
        inp.type = this.checked ? 'text' : 'password';
    });

    // Submit
    form.addEventListener('submit', async function(e) {
        e.preventDefault();

        var data = {
            name: document.getElementById('servantName').value.trim(),
            emoji: emojiInput.value,
            choir_id: parseInt(document.getElementById('servantChoir').value, 10),
            code: document.getElementById('servantCode').value.trim() || null,
            phone: document.getElementById('servantPhone').value.trim() || null,
            join_date: document.getElementById('servantJoinDate').value || null,
            username: document.getElementById('servantUsername').value.trim(),
            password: document.getElementById('servantPassword').value,
        };

        // Validation
        if (!data.name) { toast('يرجى إدخال اسم الخادم', 'error'); return; }
        if (!data.choir_id) { toast('يرجى اختيار الخورس', 'error'); return; }
        if (!data.username || data.username.length < 3) { toast('اسم المستخدم 3 أحرف على الأقل', 'error'); return; }
        if (!/^[a-zA-Z0-9_\.]{3,50}$/.test(data.username)) {
            toast('اسم المستخدم: حروف إنجليزية وأرقام و _ و . فقط', 'error'); return;
        }
        if (!data.password || data.password.length < 6) { toast('كلمة المرور 6 أحرف على الأقل', 'error'); return; }

        var orig = btn.textContent;
        btn.disabled = true;
        btn.textContent = 'جاري الحفظ...';

        var csrf = document.querySelector('meta[name="csrf-token"]').content;

        try {
            var res = await fetch('/api/servants/create', {
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
            console.log('[create servant]', j);

            if (j.success) {
                toast('✅ ' + j.message, 'success');
                setTimeout(() => window.location.href = window.APP_URL + '/servants', 800);
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

function generatePassword() {
    var chars = 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    var pw = '';
    for (var i = 0; i < 8; i++) {
        pw += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    var inp = document.getElementById('servantPassword');
    inp.value = pw;
    inp.type = 'text';
    document.getElementById('showPassword').checked = true;
    toast('تم توليد كلمة المرور — احفظها', 'info');
}
</script>