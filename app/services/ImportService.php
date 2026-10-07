<?php
class ImportService
{
    /**
     * تحليل ملف CSV
     * يتجاهل تلقائياً: التعليمات، الأمثلة، قائمة الخُوَرَس
     */
    public function parseServantsCsv(string $filePath): array
    {
        $rows = [];
        $generalErrors = [];

        $handle = fopen($filePath, 'r');
        if (!$handle) {
            return $this->emptyResult(['فشل فتح الملف']);
        }

        // تخطي BOM
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        // تخطي صف الرؤوس
        $headers = fgetcsv($handle, 0, ',', '"', '\\');
        if (!$headers) {
            fclose($handle);
            return $this->emptyResult(['الملف فارغ']);
        }

        // الخُوَرَس (map: name => id)
        $choirs = (new Choir())->all(false);
        $choirByName = [];
        foreach ($choirs as $c) {
            $choirByName[trim($c['name'])] = (int)$c['id'];
        }

        // الكشف عن التكرار
        $existingUsernames = array_column(
            Database::all("SELECT username FROM users"),
            'username'
        );
        $existingPhones = array_column(
            Database::all("SELECT phone FROM servants WHERE phone IS NOT NULL AND phone <> ''"),
            'phone'
        );

        $seenUsernames = [];
        $seenPhones = [];

        $validCount = 0;
        $errorCount = 0;
        $warningCount = 0;
        $totalRows = 0;
        $skippedRows = 0;

        while (($row = fgetcsv($handle, 0, ',', '"', '\\')) !== false) {
            $totalRows++;

            // تخطي الفارغة
            if (empty(array_filter($row, fn($v) => trim((string)$v) !== ''))) {
                $skippedRows++;
                continue;
            }

            $firstCell = trim((string)($row[0] ?? ''));

            // تخطي صفوف التعليمات
            if (mb_strpos($firstCell, '═') !== false ||
                mb_strpos($firstCell, 'تعليمات') !== false ||
                mb_strpos($firstCell, 'أمثلة') !== false ||
                mb_strpos($firstCell, 'الخُوَرَس المتاحة') !== false ||
                mb_strpos($firstCell, 'احذف') !== false ||
                preg_match('/^\d+\./', $firstCell)) {
                $skippedRows++;
                continue;
            }

            // تخطي أسماء الخُوَرَس (من قائمة القالب)
            if (isset($choirByName[$firstCell])) {
                $restEmpty = true;
                for ($i = 1; $i < 7; $i++) {
                    if (trim((string)($row[$i] ?? '')) !== '') {
                        $restEmpty = false;
                        break;
                    }
                }
                if ($restEmpty) {
                    $skippedRows++;
                    continue;
                }
            }

            $row = array_pad($row, 7, '');

            $item = [
                'row_num'    => $totalRows,
                'name'       => trim($this->clean($row[0] ?? '')),
                'choir_name' => trim($this->clean($row[1] ?? '')),
                'phone'      => trim($this->clean($row[2] ?? '')),
                'join_date'  => trim($this->clean($row[3] ?? '')),
                'username'   => trim($this->clean($row[4] ?? '')),
                'password'   => trim($this->clean($row[5] ?? '')),
                'emoji'      => trim($this->clean($row[6] ?? '👤')) ?: '👤',
                'status'     => 'valid',
                'messages'   => [],
                'choir_id'   => 0,
            ];

            // الاسم
            if ($item['name'] === '') {
                $item['status'] = 'error';
                $item['messages'][] = 'الاسم مطلوب';
            }

            // الخورس
            if ($item['choir_name'] === '') {
                $item['status'] = 'error';
                $item['messages'][] = 'الخورس مطلوب';
            } elseif (!isset($choirByName[$item['choir_name']])) {
                $item['status'] = 'error';
                $item['messages'][] = 'الخورس غير موجود: ' . $item['choir_name'];
            } else {
                $item['choir_id'] = $choirByName[$item['choir_name']];
            }

            // username
            if ($item['username'] === '') {
                $item['status'] = 'error';
                $item['messages'][] = 'اسم المستخدم مطلوب';
            } elseif (!preg_match('/^[a-zA-Z0-9_\.]{3,50}$/', $item['username'])) {
                $item['status'] = 'error';
                $item['messages'][] = 'اسم المستخدم غير صالح';
            } elseif (in_array($item['username'], $existingUsernames, true)) {
                $item['status'] = 'error';
                $item['messages'][] = 'اسم المستخدم مستخدم: ' . $item['username'];
            } elseif (in_array($item['username'], $seenUsernames, true)) {
                $item['status'] = 'error';
                $item['messages'][] = 'اسم المستخدم مكرر في الملف';
            } else {
                $seenUsernames[] = $item['username'];
            }

            // password
            if ($item['password'] === '' || strlen($item['password']) < 6) {
                $item['status'] = 'error';
                $item['messages'][] = 'كلمة المرور 6 أحرف على الأقل';
            }

            // phone
            if ($item['phone'] !== '') {
                if (in_array($item['phone'], $existingPhones, true)) {
                    if ($item['status'] !== 'error') $item['status'] = 'warning';
                    $item['messages'][] = 'الهاتف مستخدم مسبقاً';
                } elseif (in_array($item['phone'], $seenPhones, true)) {
                    if ($item['status'] !== 'error') $item['status'] = 'warning';
                    $item['messages'][] = 'الهاتف مكرر في الملف';
                } else {
                    $seenPhones[] = $item['phone'];
                }
            }

            // join_date
            if ($item['join_date'] !== '' && !vDate($item['join_date'])) {
                if ($item['status'] !== 'error') $item['status'] = 'warning';
                $item['messages'][] = 'تاريخ غير صالح — سيتم تجاهله';
                $item['join_date'] = '';
            }

            if ($item['status'] === 'error') $errorCount++;
            elseif ($item['status'] === 'warning') $warningCount++;
            else $validCount++;

            $rows[] = $item;
        }

        fclose($handle);

        return [
            'rows'           => $rows,
            'valid_count'    => $validCount,
            'error_count'    => $errorCount,
            'warning_count'  => $warningCount,
            'total_rows'     => $totalRows,
            'skipped_rows'   => $skippedRows,
            'general_errors' => $generalErrors,
        ];
    }

    /**
     * استيراد الصفوف (مع توليد الكود تلقائياً)
     */
    public function importServants(array $rows): array
    {
        $imported = 0;
        $skipped = 0;
        $errors = [];

        $pdo = Database::pdo();
        $pdo->beginTransaction();

        try {
            // آخر كود مستخدم
            $lastCode = Database::one(
                "SELECT code FROM servants 
                 WHERE code REGEXP '^S[0-9]+$' 
                 ORDER BY CAST(SUBSTRING(code, 2) AS UNSIGNED) DESC 
                 LIMIT 1"
            );
            $nextNumber = 1;
            if ($lastCode && preg_match('/^S(\d+)$/', $lastCode['code'], $m)) {
                $nextNumber = (int)$m[1] + 1;
            }

            foreach ($rows as $row) {
                if (($row['status'] ?? '') === 'error') {
                    $skipped++;
                    continue;
                }

                $name      = trim((string)($row['name'] ?? ''));
                $choirId   = (int)($row['choir_id'] ?? 0);
                $phone     = trim((string)($row['phone'] ?? '')) ?: null;
                $joinDate  = trim((string)($row['join_date'] ?? '')) ?: null;
                $username  = trim((string)($row['username'] ?? ''));
                $password  = (string)($row['password'] ?? '');
                $emoji     = trim((string)($row['emoji'] ?? '👤')) ?: '👤';

                if (!$name || !$choirId || !$username || strlen($password) < 6) {
                    $errors[] = "صف [{$name}]: بيانات ناقصة";
                    $skipped++;
                    continue;
                }

                // فحص مزدوج
                $exists = Database::one("SELECT id FROM users WHERE username = ?", [$username]);
                if ($exists) {
                    $skipped++;
                    continue;
                }

                // ⭐ توليد الكود
                $code = 'S' . str_pad((string)$nextNumber, 3, '0', STR_PAD_LEFT);
                while (Database::one("SELECT id FROM servants WHERE code = ?", [$code])) {
                    $nextNumber++;
                    $code = 'S' . str_pad((string)$nextNumber, 3, '0', STR_PAD_LEFT);
                }
                $nextNumber++;

                // 1. الخادم
                $servantId = Database::insert('servants', [
                    'name'      => $name,
                    'emoji'     => $emoji,
                    'choir_id'  => $choirId,
                    'code'      => $code,
                    'phone'     => $phone,
                    'join_date' => $joinDate,
                    'status'    => SERVANT_ACTIVE,
                ]);

                // 2. المستخدم
                Database::insert('users', [
                    'name'          => $name,
                    'username'      => $username,
                    'password_hash' => password_hash($password, PASSWORD_BCRYPT),
                    'role_id'       => 5,
                    'choir_id'      => $choirId,
                    'servant_id'    => $servantId,
                    'status'        => 'active',
                ]);

                // 3. Audit
                (new AuditLog())->write(
                    (int)currentUser()['id'],
                    'IMPORT_SERVANT',
                    'servant',
                    $servantId,
                    null,
                    ['name' => $name, 'username' => $username, 'code' => $code]
                );

                $imported++;
            }

            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }

        return ['imported' => $imported, 'skipped' => $skipped, 'errors' => $errors];
    }

    private function emptyResult(array $errors = []): array
    {
        return [
            'rows' => [], 'valid_count' => 0, 'error_count' => 0,
            'warning_count' => 0, 'total_rows' => 0,
            'skipped_rows' => 0, 'general_errors' => $errors,
        ];
    }

    private function clean(string $value): string
    {
        return trim(str_replace('*', '', $value));
    }
}