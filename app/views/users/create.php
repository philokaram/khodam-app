<div class="page-header">
    <div class="page-header-text">
        <h1>إضافة مستخدم جديد</h1>
        <p>أضف مستخدماً جديداً وحدد دوره في النظام</p>
    </div>
    <div class="page-header-actions">
        <a href="<?= e(appBaseUrl()) ?>/users" class="btn">← رجوع</a>
    </div>
</div>

<form id="userCreateForm" class="form-container" style="max-width:720px">
    <input type="hidden" name="_csrf_token" value="<?= e(csrfToken()) ?>">

    <!-- البيانات الأساسية -->
    <div style="padding-bottom:20px;border-bottom:1px solid var(--border-color);margin-bottom:20px">
        <h3 style="margin:0 0 16px;font-size:15px;font-weight:800;color:var(--text-primary);display:flex;align-items:center;gap:8px">
            <span style="width:32px;height:32px;background:var(--primary-soft);color:var(--primary);border-radius:10px;display:grid;place-items:center;font-size:16px">👤</span>
            البيانات الأساسية
        </h3>

        <div style="display:grid;gap:16px">
            <div class="form-group">
                <label class="form-label">الاسم الكامل *</label>
                <input type="text" name="name" id="uName" required
                       placeholder="مثال: أحمد محمود"
                       autofocus>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
                <div class="form-group">
                    <label class="form-label">اسم المستخدم *</label>
                    <input type="text" name="username" id="uUsername" required dir="ltr"
                           placeholder="ahmed"
                           pattern="[a-zA-Z0-9_\.]{3,50}">
                    <div class="form-hint">3-50 حرفاً إنجليزياً/أرقام/_.</div>
                </div>

                <div class="form-group">
                    <label class="form-label">البريد الإلكتروني</label>
                    <input type="email" name="email" id="uEmail" dir="ltr"
                           placeholder="ahmed@example.com">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">كلمة المرور *</label>
                <input type="password" name="password" id="uPassword" required
                       minlength="6" dir="ltr"
                       placeholder="••••••••">
                <div class="form-hint">6 أحرف على الأقل — احفظها في مكان آمن</div>
            </div>
        </div>
    </div>

    <!-- الصلاحيات -->
    <div style="padding-bottom:20px;border-bottom:1px solid var(--border-color);margin-bottom:20px">
        <h3 style="margin:0 0 16px;font-size:15px;font-weight:800;color:var(--text-primary);display:flex;align-items:center;gap:8px">
            <span style="width:32px;height:32px;background:var(--warning-soft);color:var(--warning);border-radius:10px;display:grid;place-items:center;font-size:16px">🔐</span>
            الصلاحيات والدور
        </h3>

        <div class="form-group">
            <label class="form-label">الدور *</label>
            <div id="roleOptions" style="display:grid;gap:10px">
                <?php foreach ($roles as $r):
                    $roleIcon = match ((int)$r['id']) {
                        1 => '👑',
                        2 => '🛡',
                        3 => '🎵',
                        4 => '📝',
                        default => '👤',
                    };
                    $roleDesc = match ($r['name']) {
                        'SUPER_ADMIN' => 'صلاحيات كاملة على النظام',
                        'ADMIN' => 'إدارة كل شيء ما عدا المستخدمين',
                        'CHOIR_ADMIN' => 'إدارة خورس واحد فقط',
                        'ATTENDANCE_USER' => 'تسجيل الحضور فقط',
                        default => '',
                    };
                ?>
                    <label class="role-option" data-role="<?= (int)$r['id'] ?>">
                        <input type="radio" name="role_id" value="<?= (int)$r['id'] ?>" required
                               style="display:none">
                        <div style="display:flex;align-items:center;gap:14px;padding:14px 18px;border:2px solid var(--border-color);border-radius:var(--radius);cursor:pointer;transition:all .15s;background:var(--bg-surface)">
                            <div style="width:44px;height:44px;background:var(--bg-subtle);border-radius:12px;display:grid;place-items:center;font-size:22px;flex-shrink:0">
                                <?= $roleIcon ?>
                            </div>
                            <div style="flex:1">
                                <div style="font-weight:800;font-size:14px;color:var(--text-primary)">
                                    <?= e($r['label_ar'] ?? $r['name']) ?>
                                </div>
                                <div style="font-size:12px;color:var(--text-muted);margin-top:2px">
                                    <?= e($roleDesc) ?>
                                </div>
                            </div>
                        </div>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- الخورس -->
    <div style="padding-bottom:20px;border-bottom:1px solid var(--border-color);margin-bottom:20px">
        <h3 style="margin:0 0 16px;font-size:15px;font-weight:800;color:var(--text-primary);display:flex;align-items:center;gap:8px">
            <span style="width:32px;height:32px;background:var(--info-soft);color:var(--info);border-radius:10px;display:grid;place-items:center;font-size:16px">🎵</span>
            الخورس المسؤول عنه
        </h3>

        <div class="form-group">
            <label class="form-label">الخورس (اختياري)</label>
            <select name="choir_id" id="uChoir">
                <option value="">— بدون (لا يقتصر على خورس معين) —</option>
                <?php foreach ($choirs as $c): ?>
                    <option value="<?= (int)$c['id'] ?>"><?= e($c['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <div class="form-hint">لمسؤولي الخورس فقط — لا يؤثر على SUPER_ADMIN</div>
        </div>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary" id="submitBtn" style="min-width:160px">
            حفظ المستخدم
        </button>
        <a href="<?= e(appBaseUrl()) ?>/users" class="btn">إلغاء</a>
    </div>
</form>

<style>
.role-option input:checked + div {
    border-color: var(--primary);
    background: var(--primary-soft) !important;
    box-shadow: 0 0 0 3px color-mix(in srgb, var(--primary) 15%, transparent);
}
.role-option:hover > div {
    border-color: var(--border-strong);
    background: var(--bg-hover) !important;
}
@media (max-width: 640px) {
    form[style*="grid-template-columns:1fr 1fr"] {
        grid-template-columns: 1fr !important;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var form = document.getElementById('userCreateForm');
    var btn = document.getElementById('submitBtn');

    // Role selection highlight
    document.querySelectorAll('.role-option').forEach(function(opt) {
        opt.addEventListener('click', function() {
            document.querySelectorAll('.role-option').forEach(function(o) {
                o.querySelector('input').checked = false;
            });
            var input = opt.querySelector('input');
            input.checked = true;
        });
    });

    form.addEventListener('submit', async function(e) {
        e.preventDefault();

        var roleInput = form.querySelector('input[name="role_id"]:checked');
        if (!roleInput) {
            toast('يرجى اختيار دور', 'error');
            return;
        }

        var data = {
            name: document.getElementById('uName').value.trim(),
            username: document.getElementById('uUsername').value.trim(),
            email: document.getElementById('uEmail').value.trim(),
            password: document.getElementById('uPassword').value,
            role_id: parseInt(roleInput.value, 10),
            choir_id: document.getElementById('uChoir').value || null,
        };

        if (!data.name || !data.username || !data.password) {
            toast('يرجى إكمال الحقول المطلوبة', 'error');
            return;
        }
        if (data.password.length < 6) {
            toast('كلمة المرور يجب أن تكون 6 أحرف على الأقل', 'error');
            return;
        }

        var orig = btn.textContent;
        btn.disabled = true;
        btn.textContent = 'جاري الحفظ...';

        var csrf = document.querySelector('meta[name="csrf-token"]').content;

        try {
            var res = await fetch('/api/users/create', {
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