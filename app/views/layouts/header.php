<?php
$user = currentUser();
$current = $_SERVER['REQUEST_URI'] ?? '';

// عنوان الصفحة الحالية (للجوال)
$pageTitle = 'لوحة التحكم';
if (str_contains($current, '/servants'))    $pageTitle = 'الخدام';
if (str_contains($current, '/choirs'))      $pageTitle = 'الخُوَرَس';
if (str_contains($current, '/activities'))  $pageTitle = 'الأنشطة';
if (str_contains($current, '/attendance/create'))  $pageTitle = 'تسجيل حضور';
if (str_contains($current, '/attendance/history')) $pageTitle = 'سجل الحضور';
if (str_contains($current, '/reports'))     $pageTitle = 'التقارير';
if (str_contains($current, '/users'))       $pageTitle = 'المستخدمون';

$roleLabel = $user['role_label'] ?? ($user['role_name'] ?? '');
?>
<header class="app-topbar">

    <!-- زر القائمة (جوال) -->
    <button type="button" class="topbar-action mobile-toggle" aria-label="القائمة"
            onclick="document.body.classList.toggle('sidebar-open')">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="3" y1="6" x2="21" y2="6"></line>
            <line x1="3" y1="12" x2="21" y2="12"></line>
            <line x1="3" y1="18" x2="21" y2="18"></line>
        </svg>
    </button>

    <!-- عنوان الصفحة (للجوال فقط) -->
    <span class="topbar-title"><?= e($pageTitle) ?></span>

    <div class="topbar-spacer"></div>

    <!-- زر الثيم -->
    <button type="button" class="topbar-action" onclick="toggleTheme()" aria-label="تبديل الثيم" title="تبديل الوضع الليلي">
        <span class="theme-icon" style="font-size:18px">🌙</span>
    </button>

    <!-- المستخدم -->
    <?php if ($user): ?>
        <div class="topbar-user" title="<?= e($user['name'] ?? '') ?>">
            <div class="topbar-avatar">
                <?= e(mb_substr($user['name'] ?? 'U', 0, 1)) ?>
            </div>
            <div class="topbar-user-meta">
                <span class="topbar-username"><?= e($user['name'] ?? '') ?></span>
                <?php if ($roleLabel): ?>
                    <span class="topbar-user-role"><?= e($roleLabel) ?></span>
                <?php endif; ?>
            </div>
        </div>

        <!-- زر الخروج -->
        <a href="<?= e(appBaseUrl()) ?>/logout" 
           class="topbar-action" 
           title="تسجيل الخروج"
           onclick="return confirm('هل تريد تسجيل الخروج؟')">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                <polyline points="16 17 21 12 16 7"></polyline>
                <line x1="21" y1="12" x2="9" y2="12"></line>
            </svg>
        </a>
    <?php endif; ?>

</header>