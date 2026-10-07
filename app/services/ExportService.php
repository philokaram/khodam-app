<?php
class ExportService
{
    /**
     * تصدير عام كـCSV
     * 
     * @param string $filename اسم الملف
     * @param array $headers رؤوس الأعمدة (عربية)
     * @param array $rows صفوف البيانات
     */
    public function toCsv(string $filename, array $headers, array $rows): void
    {
        // اسم ملف آمن
        $safeFilename = preg_replace('/[^a-zA-Z0-9_\-\x{0600}-\x{06FF}]/u', '_', $filename);
        $safeFilename .= '-' . date('Y-m-d') . '.csv';

        // رؤوس HTTP
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $safeFilename . '"');
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');

        // فتح output
        $output = fopen('php://output', 'w');

        // BOM لدعم العربية في Excel
        fwrite($output, "\xEF\xBB\xBF");

        // الرؤوس
        fputcsv($output, $headers, ',', '"', '\\');

        // الصفوف
        foreach ($rows as $row) {
            fputcsv($output, $row, ',', '"', '\\');
        }

        fclose($output);
        exit;
    }

    /**
     * تصدير تقرير الحضور التفصيلي
     */
    public function attendanceReport(array $filters): void
    {
        [$where, $params] = $this->buildAttendanceWhere($filters);

        $records = Database::all(
            "SELECT
                ses.attendance_date,
                act.name AS activity_name,
                c.name AS choir_name,
                s.name AS servant_name,
                s.code AS servant_code,
                a.status,
                a.excuse_reason
             FROM attendance a
             JOIN servants s ON s.id = a.servant_id
             JOIN attendance_sessions ses ON ses.id = a.session_id
             JOIN activities act ON act.id = ses.activity_id
             JOIN choirs c ON c.id = ses.choir_id
             $where
             ORDER BY ses.attendance_date DESC, s.name ASC
             LIMIT 10000",
            $params
        );

        $statusLabels = [
            'present' => 'حاضر',
            'absent'  => 'غائب',
            'excused' => 'بعذر',
        ];

        $rows = [];
        foreach ($records as $r) {
            $rows[] = [
                formatDateAr($r['attendance_date']),
                $r['servant_name'],
                $r['servant_code'] ?? '',
                $r['choir_name'],
                $r['activity_name'],
                $statusLabels[$r['status']] ?? $r['status'],
                $r['excuse_reason'] ?? '',
            ];
        }

        $this->toCsv(
            'attendance-report',
            ['التاريخ', 'اسم الخادم', 'الكود', 'الخورس', 'النشاط', 'الحالة', 'سبب العذر'],
            $rows
        );
    }

    /**
     * تصدير قائمة الخدام
     */
    public function servantsList(array $filters = []): void
    {
        $servants = (new Servant())->allWithChoir($filters);

        $rows = [];
        foreach ($servants as $s) {
            $rows[] = [
                $s['name'],
                $s['choir_name'],
                $s['code'] ?? '',
                $s['phone'] ?? '',
                $s['join_date'] ? formatDateAr($s['join_date']) : '',
                $s['status'] === 'active' ? 'نشط' : 'غير نشط',
            ];
        }

        $this->toCsv(
            'servants-list',
            ['الاسم', 'الخورس', 'الكود', 'الهاتف', 'تاريخ الانضمام', 'الحالة'],
            $rows
        );
    }

    /**
     * تصدير إحصائيات الخدام
     * - لكل خادم: عدد الجلسات، حاضر، غائب، بعذر، النسبة
     */
    public function servantsStatistics(array $filters = []): void
    {
        [$where, $params] = $this->buildAttendanceWhere($filters);

        // كل خادم وإحصائياته
        $stats = Database::all(
            "SELECT
                s.id,
                s.name,
                s.code,
                c.name AS choir_name,
                COUNT(a.id) AS total,
                COALESCE(SUM(a.status='present'), 0) AS present_count,
                COALESCE(SUM(a.status='absent'),  0) AS absent_count,
                COALESCE(SUM(a.status='excused'), 0) AS excused_count
             FROM servants s
             JOIN choirs c ON c.id = s.choir_id
             LEFT JOIN attendance a ON a.servant_id = s.id
             LEFT JOIN attendance_sessions ses ON ses.id = a.session_id
             " . ($where !== 'WHERE 1=1' ? str_replace('WHERE 1=1 AND', 'WHERE', 'WHERE ' . substr($where, 6)) : '') . "
             GROUP BY s.id, s.name, s.code, c.name
             ORDER BY s.name",
            $params
        );

        $rows = [];
        foreach ($stats as $s) {
            $total = (int)$s['total'];
            $present = (int)$s['present_count'];
            $rate = $total > 0 ? round(($present / $total) * 100, 1) : 0;

            $rows[] = [
                $s['name'],
                $s['code'] ?? '',
                $s['choir_name'],
                $total,
                $present,
                (int)$s['absent_count'],
                (int)$s['excused_count'],
                $rate . '%',
            ];
        }

        $this->toCsv(
            'servants-statistics',
            ['الاسم', 'الكود', 'الخورس', 'إجمالي الجلسات', 'حاضر', 'غائب', 'بعذر', 'نسبة الحضور'],
            $rows
        );
    }

    /**
     * تصدير إحصائيات حسب النشاط
     */
    public function statisticsByActivity(array $filters = []): void
    {
        [$where, $params] = $this->buildAttendanceWhere($filters);

        $data = Database::all(
            "SELECT
                act.name AS activity_name,
                COUNT(a.id) AS total,
                COALESCE(SUM(a.status='present'), 0) AS present_count,
                COALESCE(SUM(a.status='absent'),  0) AS absent_count,
                COALESCE(SUM(a.status='excused'), 0) AS excused_count
             FROM attendance a
             JOIN attendance_sessions ses ON ses.id = a.session_id
             JOIN activities act ON act.id = ses.activity_id
             $where
             GROUP BY act.id, act.name
             ORDER BY act.name",
            $params
        );

        $rows = [];
        foreach ($data as $r) {
            $total = (int)$r['total'];
            $present = (int)$r['present_count'];
            $rate = $total > 0 ? round(($present / $total) * 100, 1) : 0;

            $rows[] = [
                $r['activity_name'],
                $total,
                $present,
                (int)$r['absent_count'],
                (int)$r['excused_count'],
                $rate . '%',
            ];
        }

        $this->toCsv(
            'statistics-by-activity',
            ['النشاط', 'إجمالي السجلات', 'حاضر', 'غائب', 'بعذر', 'نسبة الحضور'],
            $rows
        );
    }

    /**
     * بناء WHERE موحّد للفلاتر
     */
    private function buildAttendanceWhere(array $f): array
    {
        $where  = ['1=1'];
        $params = [];

        if (!empty($f['from']))        { $where[] = 'ses.attendance_date >= ?'; $params[] = $f['from']; }
        if (!empty($f['to']))          { $where[] = 'ses.attendance_date <= ?'; $params[] = $f['to']; }
        if (!empty($f['choir_id']))    { $where[] = 'ses.choir_id = ?';         $params[] = (int)$f['choir_id']; }
        if (!empty($f['activity_id'])) { $where[] = 'ses.activity_id = ?';      $params[] = (int)$f['activity_id']; }
        if (!empty($f['servant_id']))  { $where[] = 'a.servant_id = ?';         $params[] = (int)$f['servant_id']; }
        if (!empty($f['status']))      { $where[] = 'a.status = ?';             $params[] = $f['status']; }

        return ['WHERE ' . implode(' AND ', $where), $params];
    }
}