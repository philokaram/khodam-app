'use strict';

/* ============================================================
   Toast notifications
============================================================ */
function toast(message, type = 'success') {
    const el = document.createElement('div');
    el.className = `toast toast-${type}`;
    el.textContent = message;
    document.body.appendChild(el);
    setTimeout(() => el.remove(), 4000);
}

/* ============================================================
   API helper مركزية
============================================================ */
async function api(url, options = {}) {
    const opts = {
        method: options.method || 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            ...(options.headers || {}),
        },
        credentials: 'same-origin',
    };
    if (options.body) {
        if (options.body instanceof FormData) {
            opts.body = options.body;
        } else {
            opts.headers['Content-Type'] = 'application/json';
            opts.body = JSON.stringify(options.body);
        }
    }
    const csrf = document.querySelector('input[name="_csrf_token"]');
    if (csrf && opts.method !== 'GET') {
        opts.headers['X-CSRF-Token'] = csrf.value;
    }
    const res = await fetch(url, opts);
    let data;
    try { data = await res.json(); } catch { data = { ok: false, message: 'خطأ غير متوقع' }; }
    if (!res.ok) {
        const err = new Error(data.message || 'حدث خطأ');
        err.data = data;
        err.status = res.status;
        throw err;
    }
    return data;
}

window.api = api;
window.toast = toast;

/* ============================================================
   Auto-dismiss existing toasts
============================================================ */
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-auto-dismiss]').forEach(el => {
        const t = parseInt(el.dataset.autoDismiss, 10) || 4000;
        setTimeout(() => el.remove(), t);
    });
});

if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register((window.APP_URL || '') + '/sw.js')
            .catch(err => console.warn('SW registration failed', err));
    });
}

/* ============================================================
   كشف الاتصال بالإنترنت
============================================================ */
window.isOnline = () => navigator.onLine;

window.addEventListener('online',  () => {
    toast('تم استعادة الاتصال بالإنترنت', 'success');
});
window.addEventListener('offline', () => {
    toast('انقطع الاتصال بالإنترنت. لا يمكن حفظ الحضور الآن.', 'error');
});

/* ============================================================
   API helper - يرفض الطلب عند عدم الاتصال
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

    // CSRF من meta tag
    const meta = document.querySelector('meta[name="csrf-token"]');
    if (meta && opts.method !== 'GET') {
        opts.headers['X-CSRF-Token'] = meta.content;
    }

    let res;
    try {
        res = await fetch(url, opts);
    } catch (networkErr) {
        const err = new Error('فشل الاتصال بالخادم. تحقق من الإنترنت.');
        err.network = true;
        throw err;
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

window.api = api;
window.toast = toast;