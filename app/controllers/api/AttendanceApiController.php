<?php
class AttendanceApiController
{
    public function session(): void
    {
        apiRequireLogin();
        apiRequirePermission('attendance.view');

        $activityId = (int)($_GET['activity_id'] ?? 0);
        $choirId    = (int)($_GET['choir_id'] ?? 0);
        $date       = (string)($_GET['date'] ?? '');

        if (!$activityId || !$choirId || !vDate($date)) {
            apiError('فلاتر غير صحيحة', [], 422);
        }
        if (!canAccessChoir($choirId)) apiError('غير مصرح', [], 403);

        $servants = (new Servant())->activeByChoir($choirId);
        $session  = Database::one(
            "SELECT id FROM attendance_sessions WHERE activity_id=? AND choir_id=? AND attendance_date=?",
            [$activityId, $choirId, $date]
        );

        $existing = [];
        if ($session) {
            foreach ((new Attendance())->bySession((int)$session['id']) as $r) {
                $existing[(int)$r['servant_id']] = [
                    'status'        => $r['status'],
                    'excuse_reason' => $r['excuse_reason'],
                ];
            }
        }

        $rows = [];
        foreach ($servants as $s) {
            $sid = (int)$s['id'];
            $rows[] = [
                'servant_id' => $sid,
                'name'       => $s['name'],
                'code'       => $s['code'],
                'status'     => $existing[$sid]['status'] ?? null,
                'excuse_reason' => $existing[$sid]['excuse_reason'] ?? null,
            ];
        }

        apiSuccess([
            'session_id' => $session['id'] ?? null,
            'servants'   => $rows,
        ]);
    }

    public function save(): void
    {
        apiRequirePost();
        apiRequireLogin();
        apiRequirePermission('attendance.create');
        apiVerifyCsrf();

        $in = apiInput();
        $activityId = (int)($in['activity_id'] ?? 0);
        $choirId    = (int)($in['choir_id'] ?? 0);
        $date       = (string)($in['date'] ?? '');
        $records    = $in['records'] ?? [];
        $notes      = $in['notes'] ?? null;

        if (!$activityId || !$choirId || !vDate($date)) {
            apiError('بيانات ناقصة أو غير صحيحة', [], 422);
        }
        if (!canAccessChoir($choirId)) apiError('غير مصرح لك بهذا الخورس', [], 403);
        if (!is_array($records) || empty($records)) {
            apiError('لا توجد سجلات حضور', [], 422);
        }

        try {
            $result = (new AttendanceService())
                ->saveBulk($activityId, $choirId, $date, $records, $notes);
            apiSuccess($result, __('messages.attendance_saved'));
        } catch (InvalidArgumentException $e) {
            apiError($e->getMessage(), [], 422);
        }
    }

    public function history(): void
    {
        apiRequireLogin();
        apiRequirePermission('attendance.view');

        $filters = [
            'choir_id'    => (int)($_GET['choir_id'] ?? 0),
            'activity_id' => (int)($_GET['activity_id'] ?? 0),
            'from'        => (string)($_GET['from'] ?? ''),
            'to'          => (string)($_GET['to'] ?? ''),
        ];

        $where = 'WHERE 1=1'; $params = [];
        if ($filters['choir_id'])    { $where .= ' AND ses.choir_id = ?';    $params[] = $filters['choir_id']; }
        if ($filters['activity_id']) { $where .= ' AND ses.activity_id = ?'; $params[] = $filters['activity_id']; }
        if ($filters['from'])        { $where .= ' AND ses.attendance_date >= ?'; $params[] = $filters['from']; }
        if ($filters['to'])          { $where .= ' AND ses.attendance_date <= ?'; $params[] = $filters['to']; }

        $sessions = Database::all(
            "SELECT ses.id, ses.attendance_date, ses.notes,
                    a.name AS activity_name, c.name AS choir_name,
                    (SELECT COUNT(*) FROM attendance WHERE session_id=ses.id) AS records_count,
                    (SELECT COUNT(*) FROM attendance WHERE session_id=ses.id AND status='present') AS present_count
             FROM attendance_sessions ses
             JOIN activities a ON a.id = ses.activity_id
             JOIN choirs c ON c.id = ses.choir_id
             $where
             ORDER BY ses.attendance_date DESC, ses.id DESC
             LIMIT 200",
            $params
        );

        // إضافة تاريخ عربي جاهز للعرض
        foreach ($sessions as &$s) {
            $s['date_ar'] = formatDateAr($s['attendance_date'], true);
        }
        unset($s);

        apiSuccess(['sessions' => $sessions]);
    }
}