<?php
class DashboardApiController
{
    public function index(): void
    {
        apiRequireLogin();
        apiRequirePermission('attendance.view');

        $filters = [
            'from'        => $_GET['from']        ?? null,
            'to'          => $_GET['to']          ?? null,
            'choir_id'    => $_GET['choir_id']    ? (int)$_GET['choir_id']    : null,
            'activity_id' => $_GET['activity_id'] ? (int)$_GET['activity_id'] : null,
        ];

        apiSuccess((new StatisticsService())->overall($filters));
    }
}