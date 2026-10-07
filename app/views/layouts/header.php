<?php
$base = appBaseUrl();
$user = currentUser();
?>
<header class="app-header">
    <div class="app-header-inner">
        <a href="<?= e($base) ?>/dashboard" class="app-logo">
            <span class="logo-text"><?= e(__('app.name')) ?></span>
        </a>

        <button type="button" class="menu-toggle" aria-label="القائمة" 
                onclick="document.body.classList.toggle('sidebar-open')">
            ☰
        </button>

        <div style="margin-inline-start:auto;display:flex;align-items:center;gap:10px">
            <button type="button" class="theme-toggle" onclick="toggleTheme()" aria-label="تبديل الثيم">
                🌙
            </button>

            <?php if ($user): ?>
                <div class="app-user">
                    <span><?= e($user['name'] ?? '') ?></span>
                    <a href="<?= e($base) ?>/logout" class="btn btn-sm" title="تسجيل الخروج">
                        🚪 <span class="logout-text">خروج</span>
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</header>