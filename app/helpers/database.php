<?php
class Database
{
    private static ?PDO $pdo = null;

    public static function pdo(): PDO
    {
        if (self::$pdo === null) {
            $c = config('database');
            $dsn = "mysql:host={$c['host']};dbname={$c['database']};charset={$c['charset']}";
            self::$pdo = new PDO($dsn, $c['username'], $c['password'], $c['options']);
        }
        return self::$pdo;
    }

    public static function query(string $sql, array $params = []): PDOStatement
    {
        $stmt = self::pdo()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public static function all(string $sql, array $params = []): array
    {
        return self::query($sql, $params)->fetchAll();
    }

    public static function one(string $sql, array $params = []): ?array
    {
        $row = self::query($sql, $params)->fetch();
        return $row === false ? null : $row;
    }

    public static function insert(string $table, array $data): int
    {
        $cols = array_keys($data);
        $sql = "INSERT INTO `$table` (" . implode(',', array_map(fn($c)=>"`$c`", $cols)) . ") VALUES ("
             . implode(',', array_fill(0, count($cols), '?')) . ")";
        self::query($sql, array_values($data));
        return (int)self::pdo()->lastInsertId();
    }

    public static function update(string $table, array $data, string $where, array $whereParams = []): int
    {
        $set = implode(',', array_map(fn($c)=>"`$c` = ?", array_keys($data)));
        $sql = "UPDATE `$table` SET $set WHERE $where";
        return self::query($sql, array_merge(array_values($data), $whereParams))->rowCount();
    }
}