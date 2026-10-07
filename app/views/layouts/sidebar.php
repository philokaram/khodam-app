<?php
$base = appBaseUrl();
$current = $_SERVER['REQUEST_URI'] ?? '';
$is = fn(string $p) => str_contains($current, $p);
?>
<aside class="app-sidebar">
    <div class="sidebar-brand">
        <div class="sidebar-brand-icon">⛪</div>
        <span>حضور الخدام</span>
    </div>

    <nav class="sidebar-nav">
        <a href="<?= e($base) ?>/dashboard" class="<?= $is('/dashboard') ? 'active' : '' ?>">
            <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="3" width="7" height="9"></rect>
                <rect x="14" y="3" width="7" height="5"></rect>
                <rect x="14" y="12" width="7" height="9"></rect>
                <rect x="3" y="16" width="7" height="5"></rect>
            </svg>
            لوحة التحكم
        </a>
        <a href="<?= e($base) ?>/servants" class="<?= $is('/servants') ? 'active' : '' ?>">
            <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                <circle cx="9" cy="7" r="4"></circle>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
            </svg>
            الخدام
        </a>
        <a href="<?= e($base) ?>/choirs" class="<?= $is('/choirs') ? 'active' : '' ?>">
            <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M9 18V5l12-2v13"></path>
                <circle cx="6" cy="18" r="3"></circle>
                <circle cx="18" cy="16" r="3"></circle>
            </svg>
            الخُوَرَس
        </a>
        <a href="<?= e($base) ?>/activities" class="<?= $is('/activities') ? 'active' : '' ?>">
            <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                <line x1="16" y1="2" x2="16" y2="6"></line>
                <line x1="8" y1="2" x2="8" y2="6"></line>
                <line x1="3" y1="10" x2="21" y2="10"></line>
            </svg>
            الأنشطة
        </a>
    </nav>

    <div class="sidebar-divider"></div>
    <div class="sidebar-section">الحضور</div>

    <nav class="sidebar-nav">
        <a href="<?= e($base) ?>/attendance/create" class="<?= $is('/attendance/create') ? 'active' : '' ?>">
            <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                <polyline points="22 4 12 14.01 9 11.01"></polyline>
            </svg>
            تسجيل حضور
        </a>
        <a href="<?= e($base) ?>/attendance/history" class="<?= $is('/attendance/history') ? 'active' : '' ?>">
            <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 8v4l3 3"></path>
                <circle cx="12" cy="12" r="10"></circle>
            </svg>
            سجل الحضور
        </a>
    </nav>

    <div class="sidebar-divider"></div>

    <nav class="sidebar-nav">
        <a href="<?= e($base) ?>/reports" class="<?= $is('/reports') ? 'active' : '' ?>">
            <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="20" x2="18" y2="10"></line>
                <line x1="12" y1="20" x2="12" y2="4"></line>
                <line x1="6" y1="20" x2="6" y2="14"></line>
            </svg>
            التقارير
        </a>
        <a href="<?= e($base) ?>/users" class="<?= $is('/users') ? 'active' : '' ?>">
            <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
            </svg>
            المستخدمون
        </a>
    </nav>
</aside>