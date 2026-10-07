<?php
class AttendanceSession
{
    public function findOrCreate(int $activityId, int $choirId, string $date, int $userId, ?string $notes = null): int
    {
        $existing = Database::one(
            "SELECT id FROM attendance_sessions WHERE activity_id=? AND choir_id=? AND attendance_date=?",
            [$activityId, $choirId, $date]
        );
        if ($existing) {
            if ($notes !== null) {
                Database::update('attendance_sessions', ['notes' => $notes], 'id = ?', [$existing['id']]);
            }
            return (int)$existing['id'];
        }
        return Database::insert('attendance_sessions', [
            'activity_id'     => $activityId,
            'choir_id'        => $choirId,
            'attendance_date' => $date,
            'notes'           => $notes,
            'created_by'      => $userId,
        ]);
    }

    public function find(int $id): ?array
    {
        return Database::one(
            "SELECT s.*, a.name AS activity_name, c.name AS choir_name
             FROM attendance_sessions s
             JOIN activities a ON a.id = s.activity_id
             JOIN choirs c ON c.id = s.choir_id
             WHERE s.id = ?", [$id]
        );
    }
}