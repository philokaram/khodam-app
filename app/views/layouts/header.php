<?php
$user = currentUser();
$current = $_SERVER['REQUEST_URI'] ?? '';
$roleName = $user['role_name'] ?? '';

// عنوان الصفحة الحالية
$pageTitle = 'لوحة التحكم';

if ($roleName === 'SERVANT') {
    if (str_contains($current, '/servant/history'))      $pageTitle = 'سجل حضوري';
    elseif (str_contains($current, '/servant/reports'))  $pageTitle = 'تقريري الشخصي';
    elseif (str_contains($current, '/profile'))          $pageTitle = 'ملفي الشخصي';
    else                                                  $pageTitle = 'ملفي';
} else {
    if (str_contains($current, '/servants'))             $pageTitle = 'الخدام';
    elseif (str_contains($current, '/choirs'))           $pageTitle = 'الخُوَرَس';
    elseif (str_contains($current, '/activities'))       $pageTitle = 'الأنشطة';
    elseif (str_contains($current, '/attendance/create'))  $pageTitle = 'تسجيل حضور';
    elseif (str_contains($current, '/attendance/history')) $pageTitle = 'سجل الحضور';
    elseif (str_contains($current, '/reports'))          $pageTitle = 'التقارير';
    elseif (str_contains($current, '/users'))            $pageTitle = 'المستخدمون';
    elseif (str_contains($current, '/profile'))          $pageTitle = 'ملفي الشخصي';
    elseif (str_contains($current, '/dashboard'))        $pageTitle = 'لوحة التحكم';
}

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