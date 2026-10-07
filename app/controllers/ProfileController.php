<?php
class ProfileController
{
    public function index(): void
    {
        requireLogin();
        $me = currentUser();

        // إذا كان SERVANT — اعرض بياناته كخادم
        if (isServant() && !empty($me['servant_id'])) {
            $servantId = (int)$me['servant_id'];
            $servant = (new Servant())->find($servantId);
            $stats = $servant ? (new StatisticsService())->forServant($servantId) : null;
            $byActivity = $servant ? (new StatisticsService())->forServantByActivity($servantId) : [];

            view('profile/servant', [
                'title'      => 'ملفي الشخصي',
                'servant'    => $servant,
                'stats'      => $stats,
                'byActivity' => $byActivity,
            ]);
            return;
        }

        // مستخدم عادي
        view('profile/index', [
            'title' => 'ملفي الشخصي',
            'user'  => $me,
        ]);
    }
}