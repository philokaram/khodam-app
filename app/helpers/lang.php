<?php
function __(string $key, array $replace = []): string
{
    $lang = $GLOBALS['__lang'] ?? [];
    $parts = explode('.', $key);
    $val = $lang;
    foreach ($parts as $p) {
        if (!is_array($val) || !isset($val[$p])) return $key;
        $val = $val[$p];
    }
    foreach ($replace as $k => $v) {
        $val = str_replace(':' . $k, (string)$v, $val);
    }
    return $val;
}

function attendanceStatusLabel(string $status): string
{
    return match($status) {
        ATTENDANCE_PRESENT => __('status.present'),
        ATTENDANCE_ABSENT  => __('status.absent'),
        ATTENDANCE_EXCUSED => __('status.excused'),
        default            => $status,
    };
}

function servantStatusLabel(string $status): string
{
    return $status === SERVANT_ACTIVE
        ? __('status.active')
        : __('status.inactive');
}