<?php $me = currentUser(); ?>
<div class="page-header">
    <div class="page-header-text">
        <h1>ملفي الشخصي</h1>
        <p>إدارة بياناتك وكلمة المرور</p>
    </div>
</div>

<div class="card" style="max-width:640px">
    <div style="display:flex;align-items:center;gap:16px;margin-bottom:24px;padding-bottom:20px;border-bottom:1px solid var(--border-color)">
        <div style="width:72px;height:72px;border-radius:var(--radius-lg);background:linear-gradient(135deg,var(--primary) 0%,var(--primary-light) 100%);color:#fff;display:grid;place-items:center;font-size:30px;font-weight:900;box-shadow:0 4px 12px color-mix(in srgb,var(--primary) 30%,transparent);flex-shrink:0">
            <?= e(mb_substr($me['name'], 0, 1)) ?>
        </div>
        <div style="flex:1;min-width:0">
            <div style="font-size:20px;font-weight:900;color:var(--text-primary)"><?= e($me['name']) ?></div>
            <div style="font-size:14px;color:var(--text-muted);margin-top:4px;direction:ltr">@<?= e($me['username']) ?></div>
            <div style="margin-top:8px">
                <span class="badge badge-info"><?= e($me['role_label'] ?? $me['role_name']) ?></span>
            </div>
        </div>
    </div>

    <div style="display:grid;gap:10px">
        <div class="info-row" style="display:flex;justify-content:space-between;padding:12px 16px;background:var(--bg-subtle);border-radius:var(--radius)">
            <span style="font-weight:600;color:var(--text-muted);font-size:13px">البريد الإلكتروني</span>
            <span style="font-weight:700;direction:ltr"><?= e($me['email'] ?? '—') ?></span>
        </div>
        <div style="display:flex;justify-content:space-between;padding:12px 16px;background:var(--bg-subtle);border-radius:var(--radius)">
            <span style="font-weight:600;color:var(--text-muted);font-size:13px">الدور</span>
            <span style="font-weight:700"><?= e($me['role_label'] ?? $me['role_name']) ?></span>
        </div>
        <?php if (!empty($me['choir_id'])): ?>
        <div style="display:flex;justify-content:space-between;padding:12px 16px;background:var(--bg-subtle);border-radius:var(--radius)">
            <span style="font-weight:600;color:var(--text-muted);font-size:13px">الخورس</span>
            <span style="font-weight:700">
                <?= e(Database::one("SELECT name FROM choirs WHERE id = ?", [(int)$me['choir_id']])['name'] ?? '—') ?>
            </span>
        </div>
        <?php endif; ?>
        <?php if (!empty($me['last_login_at'])): ?>
        <div style="display:flex;justify-content:space-between;padding:12px 16px;background:var(--bg-subtle);border-radius:var(--radius)">
            <span style="font-weight:600;color:var(--text-muted);font-size:13px">آخر دخول</span>
            <span style="font-weight:700"><?= e(formatDateAr($me['last_login_at'])) ?></span>
        </div>
        <?php endif; ?>
    </div>

    <div style="margin-top:24px;padding-top:20px;border-top:1px solid var(--border-color)">
        <button type="button" class="btn btn-primary" onclick="document.getElementById('passwordModal').hidden = false" style="width:100%">
            🔑 تغيير كلمة المرور
        </button>
    </div>
</div>

<!-- Modal -->
<div id="passwordModal" class="modal-backdrop" hidden>
    <div class="modal">
        <div class="modal-header">
            <h3>تغيير كلمة المرور</h3>
        </div>
        <div class="modal-body">
            <div class="form-group" style="margin-bottom:14px">
                <label class="form-label">كلمة المرور الحالية *</label>
                <input type="password" id="currentPw" dir="ltr">
            </div>
            <div class="form-group" style="margin-bottom:14px">
                <label class="form-label">كلمة المرور الجديدة *</label>
                <input type="password" id="newPw" dir="ltr" minlength="6">
                <div class="form-hint">6 أحرف على الأقل</div>
            </div>
            <div class="form-group">
                <label class="form-label">تأكيد كلمة المرور *</label>
                <input type="password" id="confirmPw" dir="ltr">
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn" onclick="document.getElementById('passwordModal').hidden = true">إلغاء</button>
            <button type="button" class="btn btn-primary" id="savePwBtn" onclick="savePw()">حفظ</button>
        </div>
    </div>
</div>

<script>
async function savePw() {
    var current = document.getElementById('currentPw').value;
    var newPw = document.getElementById('newPw').value;
    var confirmPw = document.getElementById('confirmPw').value;

    if (!current || !newPw || !confirmPw) { toast('يرجى إكمال الحقول', 'error'); return; }
    if (newPw.length < 6) { toast('كلمة المرور قصيرة', 'error'); return; }
    if (newPw !== confirmPw) { toast('غير متطابقتين', 'error'); return; }

    var btn = document.getElementById('savePwBtn');
    var orig = btn.textContent;
    btn.disabled = true;
    btn.textContent = 'جاري الحفظ...';

    var csrf = document.querySelector('meta[name="csrf-token"]').content;

    try {
        var res = await fetch('/api/profile/change-password', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-Token': csrf
            },
            body: JSON.stringify({ current_password: current, new_password: newPw })
        });
        var j = await res.json();

        if (j.success) {
            toast('✅ ' + j.message, 'success');
            document.getElementById('passwordModal').hidden = true;
        } else {
            toast('❌ ' + (j.message || 'فشل'), 'error');
        }
    } catch (err) {
        toast('خطأ: ' + err.message, 'error');
    } finally {
        btn.disabled = false;
        btn.textContent = orig;
    }
}
</script>