<?php
$base = appBaseUrl();
$isAuth = $isAuth ?? false;
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
    
    // حدّث الزر
    var btn = document.querySelector('.theme-toggle');
    if (btn) btn.textContent = next === 'dark' ? '☀️' : '🌙';
    
    // أطلق حدث مخصص
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

<?php endif; ?>
<script>
window.APP_URL = <?= json_encode($base, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;

// تأكد أن الزر يعرض الأيقونة الصحيحة عند التحميل
document.addEventListener('DOMContentLoaded', function() {
    var theme = document.documentElement.getAttribute('data-theme') || 'light';
    var btn = document.querySelector('.theme-toggle');
    if (btn) btn.textContent = theme === 'dark' ? '☀️' : '🌙';
});
</script>
<script src="<?= e($base) ?>/assets/js/app.js" defer></script>
<?php if (!empty($scripts)) foreach ($scripts as $s): ?>
    <script src="<?= e($base) ?>/assets/js/<?= e($s) ?>" defer></script>
<?php endforeach; ?>

</body>
</html>