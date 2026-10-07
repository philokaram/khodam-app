<?php
class StatisticsService
{
    /**
     * إحصائيات عامة (Dashboard / Overall)
     * تجمع كل السجلات من جدول attendance مباشرة
     */
    public function overall(array $filters = []): array
    {
        [$where, $params] = $this->buildWhere($filters, 'a', 'ses');

        $sql = "SELECT
                    COUNT(DISTINCT a.servant_id) AS total_servants_with_records,
                    COUNT(a.id)                  AS total_records,
                    COALESCE(SUM(a.status='present'), 0) AS present_count,
                    COALESCE(SUM(a.status='absent'),  0) AS absent_count,
                    COALESCE(SUM(a.status='excused'), 0) AS excused_count
                FROM attendance a
                INNER JOIN attendance_sessions ses ON ses.id = a.session_id
                $where";

        $row = Database::one($sql, $params) ?? [];

        // إجمالي الخدام (منفصل — بغض النظر عن الحضور)
        $servantsWhere = 'WHERE 1=1';
        $servantsParams = [];
        if (!empty($filters['choir_id'])) {
            $servantsWhere .= ' AND choir_id = ?';
            $servantsParams[] = (int)$filters['choir_id'];
        }
        $totalServants = (int)(Database::one(
            "SELECT COUNT(*) AS c FROM servants $servantsWhere",
            $servantsParams
        )['c'] ?? 0);

        $total   = (int)($row['total_records'] ?? 0);
        $present = (int)($row['present_count'] ?? 0);
        $absent  = (int)($row['absent_count'] ?? 0);
        $excused = (int)($row['excused_count'] ?? 0);

        return [
            'total_servants' => $totalServants,
            'total_records'  => $total,
            'present'        => $present,
            'absent'         => $absent,
            'excused'        => $excused,
            'rate'           => $total > 0 ? round($present / $total * 100, 1) : 0.0,
        ];
    }

    /**
     * إحصائيات خادم واحد
     */
    public function forServant(int $servantId, array $filters = []): array
    {
        $filters['servant_id'] = $servantId;
        [$where, $params] = $this->buildWhere($filters, 'a', 'ses');

        $sql = "SELECT
                    COUNT(a.id)                          AS total,
                    COALESCE(SUM(a.status='present'), 0) AS present,
                    COALESCE(SUM(a.status='absent'),  0) AS absent,
                    COALESCE(SUM(a.status='excused'), 0) AS excused
                FROM attendance a
                INNER JOIN attendance_sessions ses ON ses.id = a.session_id
                $where";

        $row = Database::one($sql, $params) ?? [];
        $total   = (int)($row['total'] ?? 0);
        $present = (int)($row['present'] ?? 0);

        return [
            'total'   => $total,
            'present' => $present,
            'absent'  => (int)($row['absent'] ?? 0),
            'excused' => (int)($row['excused'] ?? 0),
            'rate'    => $total > 0 ? round($present / $total * 100, 1) : 0.0,
        ];
    }

    /**
     * إحصائيات خادم حسب كل نشاط (ديناميكي)
     */
    public function forServantByActivity(int $servantId, array $filters = []): array
    {
        $filters['servant_id'] = $servantId;
        [$where, $params] = $this->buildWhere($filters, 'a', 'ses');

        $sql = "SELECT
                    act.id   AS activity_id,
                    act.name AS activity_name,
                    COUNT(a.id)                          AS total,
                    COALESCE(SUM(a.status='present'), 0) AS present,
                    COALESCE(SUM(a.status='absent'),  0) AS absent,
                    COALESCE(SUM(a.status='excused'), 0) AS excused
                FROM attendance a
                INNER JOIN attendance_sessions ses ON ses.id = a.session_id
                INNER JOIN activities act ON act.id = ses.activity_id
                $where
                GROUP BY act.id, act.name
                ORDER BY act.id";

        return Database::all($sql, $params);
    }

    /**
     * إحصائيات خورس
     */
    public function forChoir(int $choirId, array $filters = []): array
    {
        $filters['choir_id'] = $choirId;
        return $this->overall($filters);
    }

    /**
     * بناء WHERE موحّد
     * @param string $attendanceAlias  alias جدول attendance (عادة 'a')
     * @param string $sessionAlias     alias جدول attendance_sessions (عادة 'ses')
     */
    private function buildWhere(array $f, string $attendanceAlias = 'a', string $sessionAlias = 'ses'): array
    {
        $where  = ['1=1'];
        $params = [];

        if (!empty($f['from'])) {
            $where[]  = "$sessionAlias.attendance_date >= ?";
            $params[] = $f['from'];
        }
        if (!empty($f['to'])) {
            $where[]  = "$sessionAlias.attendance_date <= ?";
            $params[] = $f['to'];
        }
        if (!empty($f['choir_id'])) {
            $where[]  = "$sessionAlias.choir_id = ?";
            $params[] = (int)$f['choir_id'];
        }
        if (!empty($f['activity_id'])) {
            $where[]  = "$sessionAlias.activity_id = ?";
            $params[] = (int)$f['activity_id'];
        }
        if (!empty($f['servant_id'])) {
            $where[]  = "$attendanceAlias.servant_id = ?";
            $params[] = (int)$f['servant_id'];
        }
        if (!empty($f['status'])) {
            $where[]  = "$attendanceAlias.status = ?";
            $params[] = $f['status'];
        }

        return ['WHERE ' . implode(' AND ', $where), $params];
    }
}