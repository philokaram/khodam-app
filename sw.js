'use strict';

const CACHE_VERSION = 'v7';
const CACHE_NAME = `khodam-${CACHE_VERSION}`;
const OFFLINE_URL = '/offline.html';

// ملفات ثابتة للـcache
const STATIC_ASSETS = [
    '/',
    '/manifest.json',
    '/offline.html',
    '/favicon.ico',
    '/apple-touch-icon.png',
    '/icons/icon-192.png',
    '/icons/icon-512.png',
    '/assets/css/app.css',
    '/assets/css/variables.css',
    '/assets/css/reset.css',
    '/assets/css/layout.css',
    '/assets/css/components.css',
    '/assets/css/forms.css',
    '/assets/css/tables.css',
    '/assets/css/dashboard.css',
    '/assets/css/attendance.css',
    '/assets/css/responsive.css',
    '/assets/js/app.js',
    '/assets/js/attendance.js'
];

// ============================================================
// INSTALL — تخزين الملفات الثابتة
// ============================================================
self.addEventListener('install', (event) => {
    console.log('[SW] Installing v' + CACHE_VERSION);
    
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(cache => {
                // استخدم allSettled حتى لا يفشل SW كامل عند فشل ملف واحد
                return Promise.allSettled(
                    STATIC_ASSETS.map(url =>
                        cache.add(url).catch(err => {
                            console.warn('[SW] Failed to cache:', url, err.message);
                        })
                    )
                );
            })
            .then(() => {
                console.log('[SW] Installed successfully');
                return self.skipWaiting();
            })
    );
});

// ============================================================
// ACTIVATE — تنظيف الكاش القديم + السيطرة الفورية
// ============================================================
self.addEventListener('activate', (event) => {
    console.log('[SW] Activating...');
    
    event.waitUntil(
        Promise.all([
            // احذف الكاش القديم
            caches.keys().then(keys =>
                Promise.all(
                    keys.filter(k => k !== CACHE_NAME)
                        .map(k => {
                            console.log('[SW] Deleting old cache:', k);
                            return caches.delete(k);
                        })
                )
            ),
            // سيطر على الصفحات المفتوحة
            self.clients.claim()
        ]).then(() => {
            console.log('[SW] Activated successfully');
        })
    );
});

// ============================================================
// FETCH — استراتيجيات مختلفة حسب نوع الطلب
// ============================================================
self.addEventListener('fetch', (event) => {
    const req = event.request;

    // فقط GET
    if (req.method !== 'GET') return;

    const url = new URL(req.url);

    // نفس الأصل فقط
    if (url.origin !== self.location.origin) return;

    // تجاهل API وطلبات الحضور الديناميكية
    if (url.pathname.startsWith('/api/') ||
        url.pathname.startsWith('/attendance/create') ||
        url.pathname.startsWith('/logout')) {
        return; // دع المتصفح يتعامل معها بشكل طبيعي
    }

    // ====== Navigation requests (HTML pages) ======
    if (req.mode === 'navigate') {
        event.respondWith(
            fetch(req).catch(() => 
                caches.match(OFFLINE_URL).then(hit => 
                    hit || new Response('غير متصل', {
                        status: 503,
                        headers: { 'Content-Type': 'text/html; charset=utf-8' }
                    })
                )
            )
        );
        return;
    }

    // ====== Static assets ======
    if (url.pathname.startsWith('/assets/') ||
        url.pathname.startsWith('/icons/') ||
        url.pathname === '/manifest.json' ||
        url.pathname === '/favicon.ico' ||
        url.pathname === '/apple-touch-icon.png') {
        
        event.respondWith(
            caches.match(req).then(hit => {
                if (hit) return hit;
                return fetch(req).then(res => {
                    if (res && res.ok && res.type === 'basic') {
                        const copy = res.clone();
                        caches.open(CACHE_NAME).then(c => c.put(req, copy));
                    }
                    return res;
                });
            })
        );
        return;
    }

    // ====== البقية: network-first ======
    event.respondWith(
        fetch(req).catch(() => caches.match(req))
    );
});

// ============================================================
// MESSAGE — للتحكم من الصفحة
// ============================================================
self.addEventListener('message', (event) => {
    if (event.data && event.data.type === 'SKIP_WAITING') {
        self.skipWaiting();
    }
});