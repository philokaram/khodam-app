<?php
class User
{
    public function findByUsername(string $username): ?array
    {
        return Database::one(
            "SELECT u.*, r.name AS role_name, r.label_ar AS role_label
             FROM users u JOIN roles r ON r.id = u.role_id
             WHERE u.username = ? AND u.status = 'active' LIMIT 1",
            [$username]
        );
    }

    public function find(int $id): ?array
    {
        return Database::one(
            "SELECT u.*, r.name AS role_name, c.name AS choir_name
             FROM users u
             JOIN roles r ON r.id = u.role_id
             LEFT JOIN choirs c ON c.id = u.choir_id
             WHERE u.id = ?", [$id]
        );
    }

    public function updateLastLogin(int $id): void
    {
        Database::update('users', ['last_login_at' => date('Y-m-d H:i:s')], 'id = ?', [$id]);
    }
}