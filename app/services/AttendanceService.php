<?php
class AttendanceService
{
    /**
     * حفظ الحضور لدفعة كاملة
     * $records = [ ['servant_id'=>1,'status'=>'present','excuse_reason'=>null], ... ]
     */
    public function saveBulk(int $activityId, int $choirId, string $date, array $records, ?string $notes = null): array
    {
        $u = currentUser();
        if (!$u) throw new RuntimeException('غير مصرح');

        $this->validateBulk($records);

        $pdo = Database::pdo();
        $pdo->beginTransaction();
        try {
            $sessionId = (new AttendanceSession())->findOrCreate(
                $activityId, $choirId, $date, (int)$u['id'], $notes
            );

            $before = (new Attendance())->bySession($sessionId);
            $beforeMap = [];
            foreach ($before as $b) $beforeMap[(int)$b['servant_id']] = $b;

            foreach ($records as $r) {
                $sid = (int)$r['servant_id'];
                $status = $r['status'];
                $reason = $status === ATTENDANCE_EXCUSED ? trim((string)($r['excuse_reason'] ?? '')) : null;
                (new Attendance())->upsert($sessionId, $sid, $status, $reason);
            }

            $after = (new Attendance())->bySession($sessionId);
            (new AuditLog())->write((int)$u['id'], 'SAVE_ATTENDANCE', 'attendance_session', $sessionId, $beforeMap, $after);

            $pdo->commit();
            return ['session_id' => $sessionId];
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    private function validateBulk(array $records): void
    {
        if (empty($records)) {
            throw new InvalidArgumentException(__('validation.empty_attendance'));
        }
        $allowed = [ATTENDANCE_PRESENT, ATTENDANCE_ABSENT, ATTENDANCE_EXCUSED];
        foreach ($records as $r) {
            if (empty($r['servant_id'])) {
                throw new InvalidArgumentException(__('validation.servant_required'));
            }
            if (!in_array($r['status'] ?? '', $allowed, true)) {
                throw new InvalidArgumentException(__('validation.status_required'));
            }
            if ($r['status'] === ATTENDANCE_EXCUSED && trim((string)($r['excuse_reason'] ?? '')) === '') {
                throw new InvalidArgumentException(__('validation.excuse_required'));
            }
        }
    }
}