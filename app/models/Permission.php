<?php
class Permission
{
    public function forRole(int $roleId): array
    {
        $rows = Database::all(
            "SELECT p.name FROM role_permissions rp
             JOIN permissions p ON p.id = rp.permission_id
             WHERE rp.role_id = ?",
            [$roleId]
        );
        $names = array_column($rows, 'name');
        // SUPER_ADMIN -> كل الصلاحيات
        if ((new Role())->isSuperAdmin($roleId)) {
            $names[] = '*';
        }
        return $names;
    }
}