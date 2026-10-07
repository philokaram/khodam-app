<?php
class AttendanceController
{
    public function index(): void
    {
        requireLogin();
        requirePermission('attendance.view');
        redirect('/attendance/create');
    }

    public function create(): void
    {
        requireLogin();
        requirePermission('attendance.create');

        $activityId = (int)($_GET['activity_id'] ?? 0);
        $choirId    = (int)($_GET['choir_id'] ?? 0);
        $date       = $_GET['date'] ?? date('Y-m-d');

        $servants = [];
        $existing = [];
        if ($activityId && $choirId && vDate($date)) {
            if (!canAccessChoir($choirId)) {
                http_response_code(403); exit(__('messages.no_permission'));
            }
            $servants = (new Servant())->activeByChoir($choirId);
            $session = Database::one(
                "SELECT id FROM attendance_sessions WHERE activity_id=? AND choir_id=? AND attendance_date=?",
                [$activityId, $choirId, $date]
            );
            if ($session) {
                foreach ((new Attendance())->bySession((int)$session['id']) as $r) {
                    $existing[(int)$r['servant_id']] = $r;
                }
            }
        }

        view('attendance/create', [
            'title'      => __('attendance.register'),
            'activities' => (new Activity())->all(),
            'choirs'     => (new Choir())->all(),
            'activityId' => $activityId,
            'choirId'    => $choirId,
            'date'       => $date,
            'servants'   => $servants,
            'existing'   => $existing,
        ]);
    }

    public function save(): void
    {
        requireLogin();
        requirePermission('attendance.create');

        $isJson = str_contains($_SERVER['CONTENT_TYPE'] ?? '', 'application/json');
        $input  = $isJson ? json_decode(file_get_contents('php://input'), true) : $_POST;
        if (!is_array($input)) jsonResponse(['ok' => false, 'message' => __('messages.invalid_request')], 400);

        try {
            verifyCsrf();
            $activityId = (int)($input['activity_id'] ?? 0);
            $choirId    = (int)($input['choir_id'] ?? 0);
            $date       = $input['date'] ?? '';
            $records    = $input['records'] ?? [];
            $notes      = $input['notes'] ?? null;

            if (!canAccessChoir($choirId)) {
                jsonResponse(['ok' => false, 'message' => __('messages.no_permission')], 403);
            }

            $result = (new AttendanceService())->saveBulk($activityId, $choirId, $date, $records, $notes);
            jsonResponse(['ok' => true, 'message' => __('messages.attendance_saved'), 'data' => $result]);
        } catch (InvalidArgumentException $e) {
            jsonResponse(['ok' => false, 'message' => $e->getMessage()], 422);
        } catch (Throwable $e) {
            jsonResponse(['ok' => false, 'message' => __('messages.save_error')], 500);
        }
    }

    public function history(): void
    {
        requireLogin();
        requirePermission('attendance.view');

        $filters = [
            'choir_id'    => $_GET['choir_id'] ?? null,
            'activity_id' => $_GET['activity_id'] ?? null,
            'from'        => $_GET['from'] ?? null,
            'to'          => $_GET['to'] ?? null,
        ];
        $where = "WHERE 1=1"; $params = [];
        if ($filters['choir_id'])    { $where .= " AND ses.choir_id = ?";    $params[] = $filters['choir_id']; }
        if ($filters['activity_id']) { $where .= " AND ses.activity_id = ?"; $params[] = $filters['activity_id']; }
        if ($filters['from'])        { $where .= " AND ses.attendance_date >= ?"; $params[] = $filters['from']; }
        if ($filters['to'])          { $where .= " AND ses.attendance_date <= ?"; $params[] = $filters['to']; }

        $sessions = Database::all(
            "SELECT ses.*, a.name AS activity_name, c.name AS choir_name,
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

        view('attendance/history', [
            'title'      => __('attendance.history'),
            'sessions'   => $sessions,
            'choirs'     => (new Choir())->all(),
            'activities' => (new Activity())->all(),
            'filters'    => $filters,
        ]);
    }
}