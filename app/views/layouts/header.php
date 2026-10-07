<?php
$base = appBaseUrl();
$user = currentUser();
?>
<header class="app-header">
    <div class="app-header-inner">
        <a href="<?= e($base) ?>/dashboard" class="app-logo">
            <?= e(__('app.name')) ?>
        </a>

        <button type="button" class="menu-toggle" aria-label="القائمة" onclick="document.body.classList.toggle('sidebar-open')">
            ☰
        </button>

        <?php if ($user): ?>
            <div class="app-user">
                <span><?= e($user['name'] ?? '') ?></span>
                <a href="<?= e($base) ?>/logout" class="btn btn-sm"><?= e(__('nav.logout')) ?></a>
            </div>
        <?php endif; ?>
    </div>
</header>