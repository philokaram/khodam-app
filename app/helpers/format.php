<?php
/**
 * app/helpers/format.php
 */

function formatDateAr(?string $date, bool $withDay = false): string
{
    if (!$date) return '—';
    $ts = strtotime($date);
    if ($ts === false) return '—';

    $months = [
        1=>'يناير',2=>'فبراير',3=>'مارس',4=>'أبريل',5=>'مايو',6=>'يونيو',
        7=>'يوليو',8=>'أغسطس',9=>'سبتمبر',10=>'أكتوبر',11=>'نوفمبر',12=>'ديسمبر'
    ];
    $days = [
        'Saturday'=>'السبت','Sunday'=>'الأحد','Monday'=>'الاثنين',
        'Tuesday'=>'الثلاثاء','Wednesday'=>'الأربعاء',
        'Thursday'=>'الخميس','Friday'=>'الجمعة'
    ];

    $d = (int)date('j', $ts);
    $m = (int)date('n', $ts);
    $y = date('Y', $ts);

    // استخدم سلسلة منفصلة لتجنب مشكلة الفاصلة العربية
    $monthName = $months[$m] ?? '';
    $baseDate  = $d . ' ' . $monthName . ' ' . $y;

    if (!$withDay) {
        return $baseDate;
    }

    $dayName = $days[date('l', $ts)] ?? '';

    // ⚠️ لا تضع '،' مباشرة بعد متغير!
    // استخدم sprintf أو دمج صريح
    return $dayName . '، ' . $baseDate;
}

function formatPercent(float $value): string
{
    return number_format($value, 1) . '%';
}

function formatTimeAr(?string $time): string
{
    if (!$time) return '—';
    $ts = strtotime($time);
    if ($ts === false) return '—';
    return date('g:i A', $ts);
}