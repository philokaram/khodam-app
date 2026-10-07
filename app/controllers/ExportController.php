<?php
class ExportController
{
    /**
     * تصدير تقرير الحضور التفصيلي
     * GET /reports/export/attendance?from=...&to=...&choir_id=...&activity_id=...
     */
    public function attendance(): void
    {
        requireLogin();
        requirePermission('reports.export');

        (new ExportService())->attendanceReport([
            'from'        => $_GET['from']        ?? null,
            'to'          => $_GET['to']          ?? null,
            'choir_id'    => $_GET['choir_id']    ?? null,
            'activity_id' => $_GET['activity_id'] ?? null,
            'status'      => $_GET['status']      ?? null,
        ]);
    }

    /**
     * تصدير قائمة الخدام
     * GET /servants/export?choir_id=...&status=...
     */
    public function servants(): void
    {
        requireLogin();
        requirePermission('servants.view');

        (new ExportService())->servantsList([
            'choir_id' => $_GET['choir_id'] ?? null,
            'status'   => $_GET['status']   ?? null,
            'search'   => $_GET['search']   ?? null,
        ]);
    }

    /**
     * تصدير إحصائيات الخدام
     * GET /reports/export/servants-stats?...
     */
    public function servantsStats(): void
    {
        requireLogin();
        requirePermission('reports.export');

        (new ExportService())->servantsStatistics([
            'from'        => $_GET['from']        ?? null,
            'to'          => $_GET['to']          ?? null,
            'choir_id'    => $_GET['choir_id']    ?? null,
            'activity_id' => $_GET['activity_id'] ?? null,
        ]);
    }

    /**
     * تصدير إحصائيات الأنشطة
     * GET /reports/export/activities-stats?...
     */
    public function activitiesStats(): void
    {
        requireLogin();
        requirePermission('reports.export');

        (new ExportService())->statisticsByActivity([
            'from'     => $_GET['from']     ?? null,
            'to'       => $_GET['to']       ?? null,
            'choir_id' => $_GET['choir_id'] ?? null,
        ]);
    }
}