<?php
class ReportController
{
    /**
     * التقرير العام
     */
    public function index(): void
    {
        requireLogin();
        requirePermission('reports.view');

        $filters = [
            'from'        => $_GET['from']        ?? null,
            'to'          => $_GET['to']          ?? null,
            'choir_id'    => !empty($_GET['choir_id'])    ? (int)$_GET['choir_id']    : null,
            'activity_id' => !empty($_GET['activity_id']) ? (int)$_GET['activity_id'] : null,
            'status'      => $_GET['status']      ?? null,
        ];

        // جلب السجلات المفلترة
        [$where, $params] = $this->buildRecordsWhere($filters);

        $records = Database::all(
            "SELECT
                a.id,
                a.servant_id,
                a.status,
                a.excuse_reason,
                s.name AS servant_name,
                c.name AS choir_name,
                act.name AS activity_name,
                ses.attendance_date
             FROM attendance a
             JOIN servants s ON s.id = a.servant_id
             JOIN attendance_sessions ses ON ses.id = a.session_id
             JOIN choirs c ON c.id = ses.choir_id
             JOIN activities act ON act.id = ses.activity_id
             $where
             ORDER BY ses.attendance_date DESC, s.name ASC
             LIMIT 500",
            $params
        );

        // إحصائيات عامة
        $stats = (new StatisticsService())->overall($filters);

        view('reports/index', [
            'title'      => __('nav.reports'),
            'records'    => $records,
            'stats'      => $stats,
            'choirs'     => (new Choir())->all(),
            'activities' => (new Activity())->all(),
            'filters'    => $filters,
        ]);
    }

    /**
     * تقرير خادم واحد
     */
    public function servant(): void
    {
        requireLogin();
        requirePermission('reports.view');

        $id = (int)($_GET['id'] ?? 0);
        if (!$id) {
            redirect('/servants');
        }

        $servant = (new Servant())->find($id);
        if (!$servant) {
            http_response_code(404);
            exit(__('messages.not_found'));
        }

        $filters = [
            'from'        => $_GET['from']        ?? null,
            'to'          => $_GET['to']          ?? null,
            'activity_id' => !empty($_GET['activity_id']) ? (int)$_GET['activity_id'] : null,
        ];

        $svc = new StatisticsService();
        $overall    = $svc->forServant($id, $filters);
        $byActivity = $svc->forServantByActivity($id, $filters);

        // سجل الحضور
        $filters['servant_id'] = $id;
        [$where, $params] = $this->buildRecordsWhere($filters);

        $records = Database::all(
            "SELECT
                a.status,
                a.excuse_reason,
                act.name AS activity_name,
                c.name AS choir_name,
                ses.attendance_date
             FROM attendance a
             JOIN attendance_sessions ses ON ses.id = a.session_id
             JOIN activities act ON act.id = ses.activity_id
             JOIN choirs c ON c.id = ses.choir_id
             $where
             ORDER BY ses.attendance_date DESC
             LIMIT 500",
            $params
        );

        view('reports/servant', [
            'title'      => 'تقرير ' . $servant['name'],
            'servant'    => $servant,
            'overall'    => $overall,
            'byActivity' => $byActivity,
            'records'    => $records,
            'activities' => (new Activity())->all(),
            'filters'    => $filters,
        ]);
    }

    /**
     * بناء WHERE موحّد للسجلات
     */
    private function buildRecordsWhere(array $f): array
    {
        $where  = ['1=1'];
        $params = [];

        if (!empty($f['from']))         { $where[] = 'ses.attendance_date >= ?'; $params[] = $f['from']; }
        if (!empty($f['to']))           { $where[] = 'ses.attendance_date <= ?'; $params[] = $f['to']; }
        if (!empty($f['choir_id']))     { $where[] = 'ses.choir_id = ?';         $params[] = (int)$f['choir_id']; }
        if (!empty($f['activity_id']))  { $where[] = 'ses.activity_id = ?';      $params[] = (int)$f['activity_id']; }
        if (!empty($f['servant_id']))   { $where[] = 'a.servant_id = ?';         $params[] = (int)$f['servant_id']; }
        if (!empty($f['status']))       { $where[] = 'a.status = ?';             $params[] = $f['status']; }

        return ['WHERE ' . implode(' AND ', $where), $params];
    }
}