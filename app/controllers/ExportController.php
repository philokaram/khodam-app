<?php
class ExportController
{
    /**
     * تصدير تقرير الحضور التفصيلي
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

    /**
     * ⭐ قالب استيراد الخدام — مع قائمة الخُوَرَس والتعليمات
     */
    public function servantImportTemplate(): void
    {
        requireLogin();
        requirePermission('servants.create');

        // احصل على كل الخُوَرَس
        $choirs = (new Choir())->all(false);
        $choirNames = array_column($choirs, 'name');

        // ============ الرؤوس ============
        $headers = [
            'الاسم',
            'الخورس',
            'الهاتف',
            'تاريخ الانضمام',
            'اسم المستخدم',
            'كلمة المرور',
            'الإيموجي',
        ];

        // ============ التعليمات ============
        $notes = [
            ['═══════════════ تعليمات ═══════════════', '', '', '', '', '', ''],
            ['1. الاسم إلزامي', '', '', '', '', '', ''],
            ['2. الخورس إلزامي — اختر من قائمة الخُوَرَس المتاحة بالأسفل', '', '', '', '', '', ''],
            ['3. الهاتف اختياري', '', '', '', '', '', ''],
            ['4. تاريخ الانضمام اختياري (صيغة: 2024-01-15)', '', '', '', '', '', ''],
            ['5. اسم المستخدم إلزامي (3-50 حرفاً إنجليزياً أو أرقام أو _ أو .)', '', '', '', '', '', ''],
            ['6. كلمة المرور إلزامية (6 أحرف على الأقل)', '', '', '', '', '', ''],
            ['7. الإيموجي اختياري (افتراضي: 👤)', '', '', '', '', '', ''],
            ['8. الكود (S001, S002, ...) يُولَّد تلقائياً — لا تكتبه', '', '', '', '', '', ''],
            ['', '', '', '', '', '', ''],
            ['⚠️ احذف كل صفوف التعليمات والأمثلة والخُوَرَس هذه قبل الرفع', '', '', '', '', '', ''],
            ['', '', '', '', '', '', ''],
        ];

        // ============ الخُوَرَس المتاحة ============
        $choirList = [
            ['═══════════════ الخُوَرَس المتاحة ═══════════════', '', '', '', '', '', ''],
        ];
        foreach ($choirNames as $name) {
            $choirList[] = [$name, '', '', '', '', '', ''];
        }
        $choirList[] = ['', '', '', '', '', '', ''];

        // ============ الأمثلة ============
        $examples = [
            ['═══════════════ أمثلة (احذفها) ═══════════════', '', '', '', '', '', ''],
        ];

        foreach (array_slice($choirNames, 0, 3) as $i => $choirName) {
            $examples[] = [
                'خادم مثال ' . ($i + 1),
                $choirName,
                '0100000000' . ($i + 1),
                '2024-01-15',
                'servant' . ($i + 1),
                'pass' . ($i + 1) . '123',
                ['😀', '😎', '🥳'][$i] ?? '👤',
            ];
        }

        $rows = array_merge([$headers], $notes, $choirList, $examples);
        (new ExportService())->directCsv('servants-import-template', $rows);
    }
}