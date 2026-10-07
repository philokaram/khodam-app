'use strict';

/* ============================================================
   Toast notifications
============================================================ */
function toast(message, type = 'success') {
    // احذف أي toast قديم
    document.querySelectorAll('.toast').forEach(t => t.remove());
    
    const el = document.createElement('div');
    el.className = `toast toast-${type}`;
    el.textContent = message;
    document.body.appendChild(el);
    setTimeout(() => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(-20px)';
        setTimeout(() => el.remove(), 300);
    }, 3500);
}

/* ============================================================
   API helper مركزي
============================================================ */
async function api(url, options = {}) {
    if (!navigator.onLine) {
        const err = new Error('لا يوجد اتصال بالإنترنت');
        err.offline = true;
        throw err;
    }

    const opts = {
        method: options.method || 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            ...(options.headers || {}),
        },
        credentials: 'same-origin',
    };

    if (options.body !== undefined) {
        if (options.body instanceof FormData) {
            opts.body = options.body;
        } else {
            opts.headers['Content-Type'] = 'application/json';
            opts.body = JSON.stringify(options.body);
        }
    }

    const meta = document.querySelector('meta[name="csrf-token"]');
    if (meta && opts.method !== 'GET') {
        opts.headers['X-CSRF-Token'] = meta.content;
    }

    let res;
    try {
        res = await fetch(url, opts);
    } catch (e) {
        throw new Error('فشل الاتصال بالخادم');
    }

    let payload = null;
    const ctype = res.headers.get('content-type') || '';
    if (ctype.includes('application/json')) {
        try { payload = await res.json(); } catch { payload = null; }
    }

    if (!res.ok || !payload || payload.success === false) {
        const err = new Error(
            (payload && payload.message) || `خطأ ${res.status}`
        );
        err.status = res.status;
        err.errors = (payload && payload.errors) || {};
        err.payload = payload;
        throw err;
    }
    return payload;
}

/* ============================================================
   Theme Toggle — محدث ليدعم الأيقونة الجديدة
============================================================ */
function updateThemeButton(theme) {
    const btn = document.querySelector('.theme-icon');
    if (btn) {
        btn.textContent = theme === 'dark' ? '☀️' : '🌙';
    }
}

function toggleTheme() {
    const current = document.documentElement.getAttribute('data-theme') || 'light';
    const next = current === 'dark' ? 'light' : 'dark';
    
    document.documentElement.setAttribute('data-theme', next);
    try { localStorage.setItem('theme', next); } catch(e) {}
    updateThemeButton(next);
    
    // أطلق حدث
    window.dispatchEvent(new CustomEvent('themeChanged', { detail: { theme: next } }));
}

window.toggleTheme = toggleTheme;

// حدّث الزر عند التحميل
document.addEventListener('DOMContentLoaded', function() {
    const currentTheme = document.documentElement.getAttribute('data-theme') || 'light';
    updateThemeButton(currentTheme);
});

/* ============================================================
   Auto-dismiss existing toasts
============================================================ */
document.addEventListener('DOMContentLoaded', () => {
    // حدّث زر الثيم
    const currentTheme = document.documentElement.getAttribute('data-theme') || 'light';
    updateThemeButton(currentTheme);
    
    // auto-dismiss
    document.querySelectorAll('[data-auto-dismiss]').forEach(el => {
        const t = parseInt(el.dataset.autoDismiss, 10) || 4000;
        setTimeout(() => el.remove(), t);
    });
});

/* ============================================================
   Online / Offline
============================================================ */
window.addEventListener('online',  () => toast('✅ تم استعادة الاتصال', 'success'));
window.addEventListener('offline', () => toast('⚠️ انقطع الاتصال', 'error'));

/* ============================================================
   Service Worker
============================================================ */
if ('serviceWorker' in navigator && location.protocol === 'https:') {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js', { scope: '/' })
            .then(reg => console.log('✅ SW registered:', reg.scope))
            .catch(err => console.warn('SW failed:', err));
    });
}

/* ============================================================
   Confirm Modal
============================================================ */
function showConfirm({ title, message, extra, confirmText = 'حذف' }) {
    return new Promise((resolve) => {
        const modal = document.getElementById('confirmModal');
        
        if (!modal) {
            // Fallback: native confirm
            if (confirm(message || 'هل أنت متأكد؟')) resolve(true);
            else resolve(false);
            return;
        }

        // املأ المحتوى
        const titleEl = document.getElementById('confirmTitle');
        const msgEl = document.getElementById('confirmMessage');
        const extraEl = document.getElementById('confirmExtra');
        const yesBtn = document.getElementById('confirmYes');

        if (titleEl) titleEl.textContent = title || 'تأكيد';
        if (msgEl) msgEl.textContent = message || '';
        if (extraEl) extraEl.innerHTML = extra || '';
        if (yesBtn) yesBtn.textContent = confirmText;

        // أظهر
        modal.hidden = false;

        function close(result) {
            modal.hidden = true;
            yesBtn?.removeEventListener('click', onYes);
            modal.querySelector('[data-modal-close]')?.removeEventListener('click', onNo);
            modal.removeEventListener('click', onBackdrop);
            document.removeEventListener('keydown', onKey);
            resolve(result);
        }

        function onYes() { close(true); }
        function onNo()  { close(false); }
        function onKey(e) { if (e.key === 'Escape') close(false); }
        function onBackdrop(e) { 
            if (e.target === modal) close(false); 
        }

        yesBtn?.addEventListener('click', onYes);
        modal.querySelector('[data-modal-close]')?.addEventListener('click', onNo);
        modal.addEventListener('click', onBackdrop);
        document.addEventListener('keydown', onKey);
    });
}

/* ============================================================
   Generic Delete Handler
============================================================ */
async function deleteItem({ url, id, title, message, extra, onSuccess }) {
    const ok = await showConfirm({
        title: title || 'تأكيد الحذف',
        message: message || 'هل أنت متأكد من الحذف؟',
        extra: extra,
        confirmText: '🗑 حذف',
    });

    if (!ok) return;

    // CSRF
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';

    try {
        const res = await fetch(url, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-Token': csrf,
            },
            body: JSON.stringify({ id: id }),
        });

        const j = await res.json();

        if (j.success) {
            if (typeof toast === 'function') {
                toast('✅ ' + (j.message || 'تم الحذف'), 'success');
            }
            
            if (typeof onSuccess === 'function') {
                onSuccess(j);
            } else {
                // أعد تحميل الصفحة بعد نصف ثانية
                setTimeout(() => location.reload(), 800);
            }
        } else {
            // فشل → أظهر الخطأ
            let errMsg = j.message || 'فشل الحذف';
            
            if (j.errors && Object.keys(j.errors).length) {
                const details = Object.values(j.errors).join('، ');
                errMsg += ' — ' + details;
            }
            
            if (typeof toast === 'function') {
                toast('❌ ' + errMsg, 'error');
            } else {
                alert(errMsg);
            }
        }
    } catch (err) {
        console.error('[delete] error:', err);
        if (typeof toast === 'function') {
            toast('خطأ: ' + err.message, 'error');
        } else {
            alert('خطأ: ' + err.message);
        }
    }
}


/* ============================================================
   Header Shadow on Scroll
============================================================ */
(function() {
    const topbar = document.querySelector('.app-topbar');
    if (!topbar) return;
    
    function onScroll() {
        topbar.classList.toggle('scrolled', window.scrollY > 8);
    }
    
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
})();
/* ============================================================
   Exports
============================================================ */
window.api = api;
window.toast = toast;
window.toggleTheme = toggleTheme;
// Expose globally
window.showConfirm = showConfirm;
window.deleteItem = deleteItem;