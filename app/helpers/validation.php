<?php
function vRequired($value): bool
{
    return !(is_null($value) || (is_string($value) && trim($value) === ''));
}

function vMaxLen(string $v, int $max): bool
{
    return mb_strlen($v, 'UTF-8') <= $max;
}

function vMinLen(string $v, int $min): bool
{
    return mb_strlen($v, 'UTF-8') >= $min;
}

function vEmail(string $v): bool
{
    return filter_var($v, FILTER_VALIDATE_EMAIL) !== false;
}

function vDate(string $v): bool
{
    $d = DateTime::createFromFormat('Y-m-d', $v);
    return $d && $d->format('Y-m-d') === $v;
}

function e(?string $v): string
{
    return htmlspecialchars((string)$v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}