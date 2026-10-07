<?php
class StatisticsApiController
{
    public function overall(): void
    {
        apiRequireLogin();
        apiRequirePermission('reports.view');
        $stats = (new StatisticsService())->overall($this->filters());
        apiSuccess($stats);
    }

    public function servant(): void
    {
        apiRequireLogin();
        apiRequirePermission('reports.view');
        $id = (int)($_GET['id'] ?? 0);
        if (!$id) apiError('معرّف الخادم مطلوب', [], 422);

        $svc = new StatisticsService();
        apiSuccess([
            'overall'    => $svc->forServant($id, $this->filters()),
            'by_activity'=> $svc->forServantByActivity($id, $this->filters()),
        ]);
    }

    public function choir(): void
    {
        apiRequireLogin();
        apiRequirePermission('reports.view');
        $id = (int)($_GET['id'] ?? 0);
        if (!$id) apiError('معرّف الخورس مطلوب', [], 422);
        if (!canAccessChoir($id)) apiError('غير مصرح', [], 403);

        apiSuccess((new StatisticsService())->forChoir($id, $this->filters()));
    }

    private function filters(): array
    {
        return [
            'from'        => $_GET['from']        ?? null,
            'to'          => $_GET['to']          ?? null,
            'choir_id'    => $_GET['choir_id']    ? (int)$_GET['choir_id']    : null,
            'activity_id' => $_GET['activity_id'] ? (int)$_GET['activity_id'] : null,
            'status'      => $_GET['status']      ?? null,
        ];
    }
}