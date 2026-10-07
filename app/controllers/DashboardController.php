<?php
class DashboardController
{
    public function index(): void
    {
        requireLogin();

        // SERVANT: وجّهه إلى صفحة ملفه الشخصي
        if (isServant()) {
            redirect('/servant/reports');
        }

        // باقي الأدوار تحتاج صلاحية attendance.view
        requirePermission('attendance.view');

        $filters = [
            'from'        => $_GET['from']        ?? null,
            'to'          => $_GET['to']          ?? null,
            'choir_id'    => !empty($_GET['choir_id'])    ? (int)$_GET['choir_id']    : null,
            'activity_id' => !empty($_GET['activity_id']) ? (int)$_GET['activity_id'] : null,
        ];

        // CHOIR_ADMIN: خورسه فقط
        if (isChoirAdmin()) {
            $filters['choir_id'] = (int)currentUser()['choir_id'];
        }

        $stats = (new StatisticsService())->overall($filters);

        view('dashboard/index', [
            'title'      => __('nav.dashboard'),
            'stats'      => $stats,
            'choirs'     => isChoirAdmin()
                                ? [(new Choir())->find((int)currentUser()['choir_id'])]
                                : (new Choir())->all(),
            'activities' => (new Activity())->all(),
            'filters'    => $filters,
        ]);
    }
}