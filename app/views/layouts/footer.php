<?php $base = appBaseUrl(); ?>
<footer class="app-footer">
    <small>© <?= date('Y') ?> <?= e(__('app.name')) ?></small>
</footer>

<script>
window.APP_URL = <?= json_encode($base, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;

// تسجيل Service Worker - مرة واحدة فقط
if ('serviceWorker' in navigator && location.protocol === 'https:') {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js', { scope: '/' })
            .then(reg => console.log('✅ SW registered, scope:', reg.scope))
            .catch(err => console.error('❌ SW failed:', err));
    });
}
</script>