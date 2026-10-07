<?php
class Choir
{
    public function all(bool $onlyActive = true): array
    {
        $sql = "SELECT * FROM choirs";
        if ($onlyActive) $sql .= " WHERE is_active = 1";
        $sql .= " ORDER BY name";
        return Database::all($sql);
    }
    public function find(int $id): ?array
    {
        return Database::one("SELECT * FROM choirs WHERE id = ?", [$id]);
    }
    public function create(array $d): int { return Database::insert('choirs', $d); }
    public function update(int $id, array $d): int { return Database::update('choirs', $d, 'id = ?', [$id]); }
}