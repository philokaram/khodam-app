<?php
class Attendance
{
    public function bySession(int $sessionId): array
    {
        return Database::all(
            "SELECT a.*, s.name AS servant_name
             FROM attendance a
             JOIN servants s ON s.id = a.servant_id
             WHERE a.session_id = ?", [$sessionId]
        );
    }

    public function upsert(int $sessionId, int $servantId, string $status, ?string $reason): void
    {
        Database::query(
            "INSERT INTO attendance (session_id, servant_id, status, excuse_reason)
             VALUES (?,?,?,?)
             ON DUPLICATE KEY UPDATE status = VALUES(status), excuse_reason = VALUES(excuse_reason)",
            [$sessionId, $servantId, $status, $reason]
        );
    }

    public function deleteBySession(int $sessionId): void
    {
        Database::query("DELETE FROM attendance WHERE session_id = ?", [$sessionId]);
    }
}