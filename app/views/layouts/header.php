<?php
$user = currentUser();
?>
<header class="app-topbar">
    <button type="button" class="mobile-toggle" aria-label="القائمة"
            onclick="document.body.classList.toggle('sidebar-open')">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
            <line x1="3" y1="6" x2="21" y2="6"></line>
            <line x1="3" y1="12" x2="21" y2="12"></line>
            <line x1="3" y1="18" x2="21" y2="18"></line>
        </svg>
    </button>

    <div class="topbar-spacer"></div>

    <button type="button" class="topbar-action" onclick="toggleTheme()" aria-label="تبديل الثيم">
        <span class="theme-icon">🌙</span>
    </button>

    <?php if ($user): ?>
        <div class="topbar-user">
            <div class="topbar-avatar">
                <?= e(mb_substr($user['name'] ?? 'U', 0, 1)) ?>
            </div>
            <span class="topbar-username"><?= e($user['name'] ?? '') ?></span>
        </div>

        <a href="<?= e(appBaseUrl()) ?>/logout" class="btn btn-sm btn-ghost" title="تسجيل الخروج">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                <polyline points="16 17 21 12 16 7"></polyline>
                <line x1="21" y1="12" x2="9" y2="12"></line>
            </svg>
        </a>
    <?php endif; ?>
</header>