<?php
$base = appBaseUrl();
$user = currentUser();
$current = $_SERVER['REQUEST_URI'] ?? '';
$is = fn(string $p) => str_contains($current, $p);
$roleLabel = $user['role_label'] ?? ($user['role_name'] ?? '');
$roleName = $user['role_name'] ?? '';

// ============================================================
// صلاحيات العرض حسب الدور
// ============================================================
$isServantUser           = ($roleName === 'SERVANT');
$canSeeServants          = in_array($roleName, ['SUPER_ADMIN', 'ADMIN', 'CHOIR_ADMIN'], true);
$canSeeAdmin             = in_array($roleName, ['SUPER_ADMIN', 'ADMIN'], true);
$canSeeReports           = in_array($roleName, ['SUPER_ADMIN', 'ADMIN', 'CHOIR_ADMIN', 'ATTENDANCE_USER'], true);
$canRegisterAttendance   = in_array($roleName, ['SUPER_ADMIN', 'ADMIN', 'ATTENDANCE_USER'], true);
$canSeeAttendanceHistory = in_array($roleName, ['SUPER_ADMIN', 'ADMIN', 'CHOIR_ADMIN', 'ATTENDANCE_USER'], true);

// رابط الشعار - SERVANT يذهب لملفه
$brandLink = $isServantUser ? '/servant/reports' : '/dashboard';
?>

<aside class="app-sidebar">

    <!-- Brand -->
    <a href="<?= e($base . $brandLink) ?>" class="sidebar-brand">
        <div class="sidebar-brand-icon">⛪</div>
        <div class="sidebar-brand-text">
            <div class="sidebar-brand-title">حضور الخدام</div>
            <div class="sidebar-brand-sub">نظام المتابعة</div>
        </div>
    </a>

    <?php if ($isServantUser): ?>

        <!-- ============================================ -->
        <!-- SERVANT: ملفي الشخصي + سجل حضوري + تقريري   -->
        <!-- ============================================ -->
        <div class="sidebar-section">ملفي</div>
        <nav class="sidebar-nav">
            <a href="<?= e($base) ?>/profile" class="<?= $is('/profile') ? 'active' : '' ?>">
                <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                </svg>
                ملفي الشخصي
            </a>
            <a href="<?= e($base) ?>/servant/history" class="<?= $is('/servant/history') ? 'active' : '' ?>">
                <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 8v4l3 3"></path>
                    <circle cx="12" cy="12" r="10"></circle>
                </svg>
                سجل حضوري
            </a>
            <a href="<?= e($base) ?>/servant/reports" class="<?= $is('/servant/reports') ? 'active' : '' ?>">
                <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="20" x2="18" y2="10"></line>
                    <line x1="12" y1="20" x2="12" y2="4"></line>
                    <line x1="6" y1="20" x2="6" y2="14"></line>
                </svg>
                تقريري الشخصي
            </a>
        </nav>

    <?php else: ?>

        <!-- ============================================ -->
        <!-- الرئيسية -->
        <!-- ============================================ -->
        <div class="sidebar-section">الرئيسية</div>
        <nav class="sidebar-nav">
            <a href="<?= e($base) ?>/dashboard" class="<?= $is('/dashboard') ? 'active' : '' ?>">
                <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="3" width="7" height="9"></rect>
                    <rect x="14" y="3" width="7" height="5"></rect>
                    <rect x="14" y="12" width="7" height="9"></rect>
                    <rect x="3" y="16" width="7" height="5"></rect>
                </svg>
                لوحة التحكم
            </a>
        </nav>

        <!-- ============================================ -->
        <!-- الإدارة -->
        <!-- ============================================ -->
        <?php if ($canSeeServants): ?>
            <div class="sidebar-section">الإدارة</div>
            <nav class="sidebar-nav">
                <a href="<?= e($base) ?>/servants" class="<?= $is('/servants') ? 'active' : '' ?>">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                    </svg>
                    الخدام
                </a>

                <?php if ($canSeeAdmin): ?>
                    <a href="<?= e($base) ?>/choirs" class="<?= $is('/choirs') ? 'active' : '' ?>">
                        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M9 18V5l12-2v13"></path>
                            <circle cx="6" cy="18" r="3"></circle>
                            <circle cx="18" cy="16" r="3"></circle>
                        </svg>
                        الخُوَرَس
                    </a>
                    <a href="<?= e($base) ?>/activities" class="<?= $is('/activities') ? 'active' : '' ?>">
                        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="4" width="18" height="18" rx="2"></rect>
                            <line x1="16" y1="2" x2="16" y2="6"></line>
                            <line x1="8" y1="2" x2="8" y2="6"></line>
                            <line x1="3" y1="10" x2="21" y2="10"></line>
                        </svg>
                        الأنشطة
                    </a>
                <?php endif; ?>
            </nav>
        <?php endif; ?>

        <!-- ============================================ -->
        <!-- الحضور -->
        <!-- ============================================ -->
        <?php if ($canRegisterAttendance || $canSeeAttendanceHistory): ?>
            <div class="sidebar-section">الحضور</div>
            <nav class="sidebar-nav">
                <?php if ($canRegisterAttendance): ?>
                    <a href="<?= e($base) ?>/attendance/create" class="<?= $is('/attendance/create') ? 'active' : '' ?>">
                        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                            <polyline points="22 4 12 14.01 9 11.01"></polyline>
                        </svg>
                        تسجيل حضور
                    </a>
                <?php endif; ?>
                <?php if ($canSeeAttendanceHistory): ?>
                    <a href="<?= e($base) ?>/attendance/history" class="<?= $is('/attendance/history') ? 'active' : '' ?>">
                        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 8v4l3 3"></path>
                            <circle cx="12" cy="12" r="10"></circle>
                        </svg>
                        سجل الحضور
                    </a>
                <?php endif; ?>
            </nav>
        <?php endif; ?>

        <!-- ============================================ -->
        <!-- التقارير -->
        <!-- ============================================ -->
        <?php if ($canSeeReports): ?>
            <div class="sidebar-section">التقارير</div>
            <nav class="sidebar-nav">
                <a href="<?= e($base) ?>/reports" class="<?= $is('/reports') ? 'active' : '' ?>">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="20" x2="18" y2="10"></line>
                        <line x1="12" y1="20" x2="12" y2="4"></line>
                        <line x1="6" y1="20" x2="6" y2="14"></line>
                    </svg>
                    التقارير
                </a>
            </nav>
        <?php endif; ?>

        <!-- ============================================ -->
        <!-- النظام -->
        <!-- ============================================ -->
        <?php if ($canSeeAdmin): ?>
            <div class="sidebar-section">النظام</div>
            <nav class="sidebar-nav">
                <a href="<?= e($base) ?>/users" class="<?= $is('/users') ? 'active' : '' ?>">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                    </svg>
                    المستخدمون
                </a>
            </nav>
        <?php endif; ?>

        <!-- ============================================ -->
        <!-- حسابي -->
        <!-- ============================================ -->
        <div class="sidebar-section">حسابي</div>
        <nav class="sidebar-nav">
            <a href="<?= e($base) ?>/profile" class="<?= $is('/profile') ? 'active' : '' ?>">
                <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                </svg>
                ملفي الشخصي
            </a>
        </nav>

    <?php endif; ?>

    <!-- ============================================ -->
    <!-- User Footer -->
    <!-- ============================================ -->
    <?php if ($user): ?>
        <div class="sidebar-footer">
            <div class="sidebar-user">
                <div class="sidebar-user-avatar">
                    <?= e(mb_substr($user['name'] ?? 'U', 0, 1)) ?>
                </div>
                <div class="sidebar-user-info">
                    <div class="sidebar-user-name"><?= e($user['name'] ?? '') ?></div>
                    <div class="sidebar-user-role"><?= e($roleLabel) ?></div>
                </div>
            </div>
        </div>
    <?php endif; ?>

</aside>