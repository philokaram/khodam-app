<?php
class Activity
{
    public function all(bool $onlyActive = true): array
    {
        $sql = "SELECT * FROM activities";
        if ($onlyActive) $sql .= " WHERE is_active = 1";
        $sql .= " ORDER BY id";
        return Database::all($sql);
    }
    public function find(int $id): ?array
    {
        return Database::one("SELECT * FROM activities WHERE id = ?", [$id]);
    }
    public function create(array $d): int { return Database::insert('activities', $d); }
    public function update(int $id, array $d): int { return Database::update('activities', $d, 'id = ?', [$id]); }
}