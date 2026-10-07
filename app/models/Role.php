<?php
class Role
{
    public function isSuperAdmin(int $roleId): bool
    {
        $row = Database::one("SELECT name FROM roles WHERE id = ?", [$roleId]);
        return $row && $row['name'] === ROLE_SUPER_ADMIN;
    }

    public function all(): array
    {
        return Database::all("SELECT * FROM roles ORDER BY id");
    }
}