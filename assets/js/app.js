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
   Theme Toggle
============================================================ */
(function initTheme() {
    const saved = localStorage.getItem('theme');
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    const theme = saved || (prefersDark ? 'dark' : 'light');
    
    document.documentElement.setAttribute('data-theme', theme);
})();

function updateThemeButton(theme) {
    const btn = document.querySelector('.theme-toggle');
    if (btn) {
        btn.textContent = theme === 'dark' ? '☀️' : '🌙';
    }
}

function toggleTheme() {
    const current = document.documentElement.getAttribute('data-theme') || 'light';
    const next = current === 'dark' ? 'light' : 'dark';
    
    document.documentElement.setAttribute('data-theme', next);
    localStorage.setItem('theme', next);
    updateThemeButton(next);
    
    if (typeof toast === 'function') {
        toast(next === 'dark' ? '🌙 الوضع الليلي' : '☀️ الوضع النهاري', 'info');
    }
}

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
   Header Shadow on Scroll
============================================================ */
(function initHeaderScroll() {
    const header = document.querySelector('.app-header');
    if (!header) return;

    let lastScroll = 0;
    
    function onScroll() {
        const scrolled = window.scrollY > 8;
        header.classList.toggle('scrolled', scrolled);
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