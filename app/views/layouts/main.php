<?php
$base = appBaseUrl();
$isAuth = $isAuth ?? false;
$me = currentUser();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl" data-theme="light">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title><?= e($title ?? __('app.name')) ?> — <?= e(__('app.name')) ?></title>

<!-- ⚠️ مهم: تطبيق الثيم فوراً قبل رسم الصفحة — بدون وميض -->
<script>
(function() {
    try {
        var saved = localStorage.getItem('theme');
        var prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        var theme = saved || (prefersDark ? 'dark' : 'light');
        document.documentElement.setAttribute('data-theme', theme);
    } catch(e) {
        document.documentElement.setAttribute('data-theme', 'light');
    }
})();

// دالة التبديل — معرّفة قبل تحميل باقي السكربتات
function toggleTheme() {
    var current = document.documentElement.getAttribute('data-theme') || 'light';
    var next = current === 'dark' ? 'light' : 'dark';
    document.documentElement.setAttribute('data-theme', next);
    try { localStorage.setItem('theme', next); } catch(e) {}

    var btn = document.querySelector('.theme-icon');
    if (btn) btn.textContent = next === 'dark' ? '☀️' : '🌙';

    window.dispatchEvent(new CustomEvent('themeChanged', { detail: { theme: next } }));
}
window.toggleTheme = toggleTheme;
</script>

<link rel="icon" href="<?= e($base) ?>/favicon.ico">
<link rel="apple-touch-icon" sizes="180x180" href="<?= e($base) ?>/apple-touch-icon.png">
<link rel="manifest" href="<?= e($base) ?>/manifest.json">
<meta name="theme-color" content="#FFD23F">
<meta name="csrf-token" content="<?= function_exists('csrfToken') ? e(csrfToken()) : '' ?>">

<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= e($base) ?>/assets/css/app.css?v=<?= @filemtime(BASE_PATH . '/assets/css/app.css') ?: time() ?>">
</head>
<body class="<?= $isAuth ? 'auth-body' : 'app-body' ?>">

<?php if ($isAuth): ?>

    <?php require APP_PATH . '/views/components/alert.php'; ?>
    <?= $content ?>

<?php else: ?>

    <?php require APP_PATH . '/views/layouts/header.php'; ?>
    <div class="app-shell">
        <?php require APP_PATH . '/views/layouts/sidebar.php'; ?>
        <main class="app-main">
            <?php require APP_PATH . '/views/components/alert.php'; ?>
            <?= $content ?>
        </main>
    </div>
    <?php require APP_PATH . '/views/layouts/footer.php'; ?>
    <?php require APP_PATH . '/views/components/confirm-modal.php'; ?>

    <!-- Onboarding — يظهر مرة واحدة فقط لكل مستخدم -->
    <?php if ($me && !empty($me['onboarding_seen']) === false): ?>
        <?php require APP_PATH . '/views/components/onboarding.php'; ?>
    <?php endif; ?>

<?php endif; ?>

<script>
window.APP_URL = <?= json_encode($base, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;

// تأكد أن زر الثيم يعرض الأيقونة الصحيحة عند التحميل
document.addEventListener('DOMContentLoaded', function() {
    var theme = document.documentElement.getAttribute('data-theme') || 'light';
    var btn = document.querySelector('.theme-icon');
    if (btn) btn.textContent = theme === 'dark' ? '☀️' : '🌙';
});
</script>

<script src="<?= e($base) ?>/assets/js/app.js?v=<?= @filemtime(BASE_PATH . '/assets/js/app.js') ?: time() ?>"></script>
<script src="<?= e($base) ?>/assets/js/tooltips.js?v=<?= @filemtime(BASE_PATH . '/assets/js/tooltips.js') ?: time() ?>" defer></script>

<?php if (!empty($scripts)) foreach ($scripts as $s): ?>
    <script src="<?= e($base) ?>/assets/js/<?= e($s) ?>?v=<?= @filemtime(BASE_PATH . '/assets/js/' . $s) ?: time() ?>"></script>
<?php endforeach; ?>

<?php if (!$isAuth): ?>
<script>
if ('serviceWorker' in navigator && location.protocol === 'https:') {
    window.addEventListener('load', function() {
        navigator.serviceWorker
            .register(window.APP_URL + '/sw.js', { scope: window.APP_URL + '/' })
            .then(function(reg) { console.log('✅ SW registered:', reg.scope); })
            .catch(function(err) { console.warn('SW failed:', err); });
    });
}
</script>
<?php endif; ?>

</body>
</html>