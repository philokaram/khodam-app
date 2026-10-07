SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- =====================
-- الجداول الأساسية
-- =====================

CREATE TABLE roles (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    label_ar VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE permissions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    label_ar VARCHAR(150) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE role_permissions (
    role_id INT UNSIGNED NOT NULL,
    permission_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (role_id, permission_id),
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE choirs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    description TEXT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_choirs_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    username VARCHAR(80) NOT NULL UNIQUE,
    email VARCHAR(150) NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role_id INT UNSIGNED NOT NULL,
    choir_id INT UNSIGNED NULL,
    status ENUM('active','inactive') NOT NULL DEFAULT 'active',
    last_login_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(id),
    FOREIGN KEY (choir_id) REFERENCES choirs(id) ON DELETE SET NULL,
    INDEX idx_users_role (role_id),
    INDEX idx_users_choir (choir_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE servants (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    choir_id INT UNSIGNED NOT NULL,
    name VARCHAR(150) NOT NULL,
    code VARCHAR(50) NULL UNIQUE,
    phone VARCHAR(30) NULL,
    join_date DATE NULL,
    status ENUM('active','inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (choir_id) REFERENCES choirs(id),
    INDEX idx_servants_choir (choir_id),
    INDEX idx_servants_status (status),
    INDEX idx_servants_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE activities (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    description TEXT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_activities_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE attendance_sessions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    activity_id INT UNSIGNED NOT NULL,
    choir_id INT UNSIGNED NOT NULL,
    attendance_date DATE NOT NULL,
    notes TEXT NULL,
    created_by INT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (activity_id) REFERENCES activities(id),
    FOREIGN KEY (choir_id) REFERENCES choirs(id),
    FOREIGN KEY (created_by) REFERENCES users(id),
    UNIQUE KEY uniq_session (activity_id, choir_id, attendance_date),
    INDEX idx_sessions_date (attendance_date),
    INDEX idx_sessions_activity (activity_id),
    INDEX idx_sessions_choir (choir_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE attendance (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    session_id INT UNSIGNED NOT NULL,
    servant_id INT UNSIGNED NOT NULL,
    status ENUM('present','absent','excused') NOT NULL,
    excuse_reason VARCHAR(500) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (session_id) REFERENCES attendance_sessions(id) ON DELETE CASCADE,
    FOREIGN KEY (servant_id) REFERENCES servants(id) ON DELETE CASCADE,
    UNIQUE KEY uniq_attendance (session_id, servant_id),
    INDEX idx_attendance_status (status),
    INDEX idx_attendance_servant (servant_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE audit_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NULL,
    action VARCHAR(80) NOT NULL,
    entity_type VARCHAR(80) NOT NULL,
    entity_id INT UNSIGNED NULL,
    old_data JSON NULL,
    new_data JSON NULL,
    ip_address VARCHAR(45) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_audit_user (user_id),
    INDEX idx_audit_entity (entity_type, entity_id),
    INDEX idx_audit_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
ALTER TABLE servants ADD COLUMN emoji VARCHAR(8) DEFAULT '👤' AFTER name;
UPDATE servants SET emoji = CASE (id % 8)
    WHEN 0 THEN '😎'
    WHEN 1 THEN '😀'
    WHEN 2 THEN '🥳'
    WHEN 3 THEN '🤓'
    WHEN 4 THEN '😇'
    WHEN 5 THEN '🙂'
    WHEN 6 THEN '😄'
    ELSE '😊'
END WHERE emoji = '👤' OR emoji IS NULL;


-- 1. أضف دور SERVANT
INSERT INTO roles (name, label_ar) VALUES
('SERVANT', 'خادم')
ON DUPLICATE KEY UPDATE label_ar = 'خادم';

-- 2. اربط الخادم بحساب مستخدم (عمود servant_id)
ALTER TABLE users 
ADD COLUMN servant_id INT UNSIGNED NULL AFTER choir_id,
ADD CONSTRAINT fk_users_servant FOREIGN KEY (servant_id) REFERENCES servants(id) ON DELETE SET NULL;

-- 3. احصل على id دور SERVANT
SELECT id, name FROM roles WHERE name = 'SERVANT';
-- 4. أضف صلاحية عرض الملف الشخصي
INSERT INTO permissions (name, label_ar) VALUES
('profile.view', 'عرض الملف الشخصي')
ON DUPLICATE KEY UPDATE label_ar = 'عرض الملف الشخصي';

-- 5. أضف صلاحيات SERVANT
INSERT INTO role_permissions (role_id, permission_id)
SELECT 5, id FROM permissions WHERE name IN ('profile.view')
ON DUPLICATE KEY UPDATE role_id = role_id;

-- 6. تأكد من صلاحيات CHOIR_ADMIN
-- CHOIR_ADMIN: عرض خدامه + تسجيل حضور خدامه + تقارير خدامه
DELETE FROM role_permissions WHERE role_id = 3;
INSERT INTO role_permissions (role_id, permission_id)
SELECT 3, id FROM permissions WHERE name IN (
    'servants.view', 'servants.create', 'servants.edit',
    'choirs.view', 'activities.view',
    'attendance.view', 'attendance.create', 'attendance.edit',
    'reports.view'
);

-- 7. تأكد من صلاحيات ATTENDANCE_USER
-- ATTENDANCE_USER: تسجيل حضور لكل الخُوَرَس + تقارير
DELETE FROM role_permissions WHERE role_id = 4;
INSERT INTO role_permissions (role_id, permission_id)
SELECT 4, id FROM permissions WHERE name IN (
    'servants.view', 'choirs.view', 'activities.view',
    'attendance.view', 'attendance.create', 'attendance.edit',
    'reports.view'
);

-- 8. تأكد من صلاحيات ADMIN
-- ADMIN: كل شيء ما عدا إدارة SUPER_ADMIN
DELETE FROM role_permissions WHERE role_id = 2;
INSERT INTO role_permissions (role_id, permission_id)
SELECT 2, id FROM permissions WHERE name IN (
    'servants.view', 'servants.create', 'servants.edit', 'servants.delete',
    'choirs.view', 'choirs.create', 'choirs.edit', 'choirs.delete',
    'activities.view', 'activities.create', 'activities.edit', 'activities.delete',
    'attendance.view', 'attendance.create', 'attendance.edit', 'attendance.delete',
    'reports.view', 'reports.export',
    'users.view', 'users.create', 'users.edit', 'users.delete',
    'profile.view'
);

-- 9. تأكد من صلاحيات SUPER_ADMIN (كل شيء)
DELETE FROM role_permissions WHERE role_id = 1;
INSERT INTO role_permissions (role_id, permission_id)
SELECT 1, id FROM permissions;

-- 10. تحقق
SELECT r.name, r.label_ar, COUNT(rp.permission_id) AS perms
FROM roles r
LEFT JOIN role_permissions rp ON rp.role_id = r.id
GROUP BY r.id
ORDER BY r.id;

-- احذف كل صلاحيات CHOIR_ADMIN
DELETE FROM role_permissions WHERE role_id = 3;

-- أضف صلاحيات القراءة فقط
INSERT INTO role_permissions (role_id, permission_id)
SELECT 3, id FROM permissions WHERE name IN (
    'servants.view',
    'choirs.view',
    'activities.view',
    'attendance.view',
    'reports.view'
);

-- تحقق
SELECT p.name, p.label_ar
FROM role_permissions rp
JOIN permissions p ON p.id = rp.permission_id
WHERE rp.role_id = 3
ORDER BY p.name;

SET FOREIGN_KEY_CHECKS = 1;