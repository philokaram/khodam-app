<?php
class ServantPortalController
{
    private function guard(): array
    {
        requireLogin();
        $me = currentUser();

        if (!isServant()) {
            http_response_code(403);
            exit('هذه الصفحة مخصصة للخدام فقط');
        }

        if (empty($me['servant_id'])) {
            http_response_code(400);
            exit('حسابك غير مرتبط بخادم. تواصل مع المدير.');
        }

        return $me;
    }

    public function history(): void
    {
        $me = $this->guard();
        $servantId = (int)$me['servant_id'];

        $filters = [
            'from'        => $_GET['from']        ?? null,
            'to'          => $_GET['to']          ?? null,
            'activity_id' => !empty($_GET['activity_id']) ? (int)$_GET['activity_id'] : null,
        ];

        $where = "WHERE a.servant_id = ?";
        $params = [$servantId];

        if (!empty($filters['from'])) {
            $where .= " AND ses.attendance_date >= ?";
            $params[] = $filters['from'];
        }
        if (!empty($filters['to'])) {
            $where .= " AND ses.attendance_date <= ?";
            $params[] = $filters['to'];
        }
        if (!empty($filters['activity_id'])) {
            $where .= " AND ses.activity_id = ?";
            $params[] = $filters['activity_id'];
        }

        $records = Database::all(
            "SELECT
                a.status,
                a.excuse_reason,
                ses.attendance_date,
                act.name AS activity_name,
                c.name AS choir_name
             FROM attendance a
             JOIN attendance_sessions ses ON ses.id = a.session_id
             JOIN activities act ON act.id = ses.activity_id
             JOIN choirs c ON c.id = ses.choir_id
             $where
             ORDER BY ses.attendance_date DESC
             LIMIT 500",
            $params
        );

        view('servant/history', [
            'title'      => 'سجل حضوري',
            'records'    => $records,
            'activities' => (new Activity())->all(),
            'filters'    => $filters,
        ]);
    }

    public function reports(): void
    {
        $me = $this->guard();
        $servantId = (int)$me['servant_id'];

        $servant = (new Servant())->find($servantId);
        if (!$servant) {
            http_response_code(404);
            exit('بيانات الخادم غير موجودة');
        }

        $svc = new StatisticsService();
        $overall = $svc->forServant($servantId);
        $byActivity = $svc->forServantByActivity($servantId);

        view('servant/reports', [
            'title'      => 'تقريري الشخصي',
            'servant'    => $servant,
            'overall'    => $overall,
            'byActivity' => $byActivity,
        ]);
    }
}