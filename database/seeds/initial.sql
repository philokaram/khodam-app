-- الأدوار
INSERT INTO roles (name, label_ar) VALUES
('SUPER_ADMIN', 'مدير عام'),
('ADMIN', 'مدير'),
('CHOIR_ADMIN', 'مسؤول خورس'),
('ATTENDANCE_USER', 'مسجل حضور');

-- الصلاحيات
INSERT INTO permissions (name, label_ar) VALUES
('users.view', 'عرض المستخدمين'),
('users.create', 'إضافة مستخدم'),
('users.edit', 'تعديل مستخدم'),
('users.delete', 'حذف مستخدم'),
('servants.view', 'عرض الخدام'),
('servants.create', 'إضافة خادم'),
('servants.edit', 'تعديل خادم'),
('servants.delete', 'حذف خادم'),
('choirs.view', 'عرض الخُوَرَس'),
('choirs.create', 'إضافة خورس'),
('choirs.edit', 'تعديل خورس'),
('choirs.delete', 'حذف خورس'),
('activities.view', 'عرض الأنشطة'),
('activities.create', 'إضافة نشاط'),
('activities.edit', 'تعديل نشاط'),
('activities.delete', 'حذف نشاط'),
('attendance.view', 'عرض الحضور'),
('attendance.create', 'تسجيل حضور'),
('attendance.edit', 'تعديل حضور'),
('attendance.delete', 'حذف حضور'),
('reports.view', 'عرض التقارير'),
('reports.export', 'تصدير التقارير');

-- SUPER_ADMIN يحصل على كل الصلاحيات
INSERT INTO role_permissions (role_id, permission_id)
SELECT 1, id FROM permissions;

-- ADMIN كل الصلاحيات ما عدا إدارة المستخدمين
INSERT INTO role_permissions (role_id, permission_id)
SELECT 2, id FROM permissions WHERE name NOT LIKE 'users.%';

-- CHOIR_ADMIN
INSERT INTO role_permissions (role_id, permission_id)
SELECT 3, id FROM permissions WHERE name IN (
  'servants.view','servants.create','servants.edit',
  'choirs.view','activities.view',
  'attendance.view','attendance.create','attendance.edit',
  'reports.view','reports.export'
);

-- ATTENDANCE_USER
INSERT INTO role_permissions (role_id, permission_id)
SELECT 4, id FROM permissions WHERE name IN (
  'servants.view','choirs.view','activities.view',
  'attendance.view','attendance.create'
);

-- خورس تجريبي
INSERT INTO choirs (name, description) VALUES
('خورس مارجرجس', 'خورس مارجرجس - الكنيسة الكبرى');

-- أنشطة مبدئية
INSERT INTO activities (name, description) VALUES
('القداس', 'حضور القداس الإلهي'),
('التسبحة', 'حضور التسبحة'),
('الافتقاد', 'زيارات الافتقاد'),
('اجتماع الخدام', 'اجتماع الخدام الأسبوعي');

-- مستخدم مدير عام (كلمة المرور: admin123)
INSERT INTO users (name, username, email, password_hash, role_id, status)
VALUES ('المدير العام', 'admin', 'admin@example.com',
'$2y$10$e0NRzQ5hQ8QbZ5Qx4w8mLuJ6vE8yZ4xN9zB2wC7yD3fG1hI5kL6mO',
1, 'active');