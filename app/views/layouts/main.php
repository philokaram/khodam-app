<?php
$base = appBaseUrl();
$isAuth = $isAuth ?? false;
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title><?= e($title ?? __('app.name')) ?> — <?= e(__('app.name')) ?></title>

<link rel="icon" href="<?= e($base) ?>/favicon.ico">
<link rel="apple-touch-icon" sizes="180x180" href="<?= e($base) ?>/apple-touch-icon.png">
<link rel="manifest" href="<?= e($base) ?>/manifest.json">
<meta name="theme-color" content="#FFD23F">
<meta name="csrf-token" content="<?= function_exists('csrfToken') ? e(csrfToken()) : '' ?>">

<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;800;900&display=swap" rel="stylesheet">
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

<?php endif; ?>

<script>
window.APP_URL = <?= json_encode($base, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
</script>
<script src="<?= e($base) ?>/assets/js/app.js" defer></script>
<?php if (!empty($scripts)) foreach ($scripts as $s): ?>
    <script src="<?= e($base) ?>/assets/js/<?= e($s) ?>" defer></script>
<?php endforeach; ?>

</body>
</html>