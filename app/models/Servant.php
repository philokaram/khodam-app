<?php
class Servant
{
    public function allWithChoir(array $filters = []): array
    {
        $sql = "SELECT s.*, c.name AS choir_name
                FROM servants s
                JOIN choirs c ON c.id = s.choir_id
                WHERE 1=1";
        $params = [];
        if (!empty($filters['choir_id'])) {
            $sql .= " AND s.choir_id = ?"; $params[] = $filters['choir_id'];
        }
        if (!empty($filters['status'])) {
            $sql .= " AND s.status = ?"; $params[] = $filters['status'];
        }
        if (!empty($filters['search'])) {
            $sql .= " AND (s.name LIKE ? OR s.code LIKE ?)";
            $params[] = '%' . $filters['search'] . '%';
            $params[] = '%' . $filters['search'] . '%';
        }
        $sql .= " ORDER BY s.name ASC";
        return Database::all($sql, $params);
    }

    public function activeByChoir(int $choirId): array
    {
        return Database::all(
            "SELECT * FROM servants WHERE choir_id = ? AND status = 'active' ORDER BY name",
            [$choirId]
        );
    }

   public function find(int $id): ?array
{
    return Database::one(
        "SELECT s.*, c.name AS choir_name
         FROM servants s 
         JOIN choirs c ON c.id = s.choir_id
         WHERE s.id = ?",
        [$id]
    );
}

    public function create(array $d): int
    {
        return Database::insert('servants', $d);
    }

    public function update(int $id, array $d): int
    {
        return Database::update('servants', $d, 'id = ?', [$id]);
    }

    public function codeExists(string $code, ?int $exceptId = null): bool
    {
        $sql = "SELECT COUNT(*) AS c FROM servants WHERE code = ?";
        $p = [$code];
        if ($exceptId) { $sql .= " AND id <> ?"; $p[] = $exceptId; }
        return (int)(Database::one($sql, $p)['c'] ?? 0) > 0;
    }
}