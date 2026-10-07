<?php
class DashboardController
{
    public function index(): void
    {
        requireLogin();
        // ❌ لا تستدعِ verifyCsrf هنا — هذا GET وليس POST!

        if (!hasPermission('attendance.view') && !hasPermission('reports.view')) {
            http_response_code(403);
            exit(__('messages.no_permission'));
        }

        $filters = [
            'from'        => $_GET['from']        ?? null,
            'to'          => $_GET['to']          ?? null,
            'choir_id'    => !empty($_GET['choir_id'])    ? (int)$_GET['choir_id']    : null,
            'activity_id' => !empty($_GET['activity_id']) ? (int)$_GET['activity_id'] : null,
        ];

        $stats = (new StatisticsService())->overall($filters);

        view('dashboard/index', [
            'title'      => __('nav.dashboard'),
            'stats'      => $stats,
            'choirs'     => (new Choir())->all(),
            'activities' => (new Activity())->all(),
            'filters'    => $filters,
        ]);
    }
}