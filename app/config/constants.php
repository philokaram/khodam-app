<?php
/**
 * app/config/constants.php
 * __DIR__ = .../htdocs/app/config
 * BASE_PATH = .../htdocs
 */

if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(dirname(__DIR__)));
}
if (!defined('APP_PATH')) {
    define('APP_PATH', BASE_PATH . '/app');
}
if (!defined('STORAGE_PATH')) {
    define('STORAGE_PATH', BASE_PATH . '/storage');
}
if (!defined('UPLOAD_PATH')) {
    define('UPLOAD_PATH', STORAGE_PATH . '/uploads');
}
if (!defined('EXPORT_PATH')) {
    define('EXPORT_PATH', STORAGE_PATH . '/exports');
}
if (!defined('LOG_PATH')) {
    define('LOG_PATH', STORAGE_PATH . '/logs');
}

// حالات الحضور
if (!defined('ATTENDANCE_PRESENT')) define('ATTENDANCE_PRESENT', 'present');
if (!defined('ATTENDANCE_ABSENT'))  define('ATTENDANCE_ABSENT',  'absent');
if (!defined('ATTENDANCE_EXCUSED')) define('ATTENDANCE_EXCUSED', 'excused');

// حالات الخادم
if (!defined('SERVANT_ACTIVE'))   define('SERVANT_ACTIVE',   'active');
if (!defined('SERVANT_INACTIVE')) define('SERVANT_INACTIVE', 'inactive');

// الأدوار
if (!defined('ROLE_SUPER_ADMIN'))     define('ROLE_SUPER_ADMIN',     'SUPER_ADMIN');
if (!defined('ROLE_ADMIN'))           define('ROLE_ADMIN',           'ADMIN');
if (!defined('ROLE_CHOIR_ADMIN'))     define('ROLE_CHOIR_ADMIN',     'CHOIR_ADMIN');
if (!defined('ROLE_ATTENDANCE_USER')) define('ROLE_ATTENDANCE_USER', 'ATTENDANCE_USER');