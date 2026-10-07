<?php
function loadEnv(string $path): void
{
    if (!file_exists($path)) return;
    foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#') continue;
        [$key, $val] = array_pad(explode('=', $line, 2), 2, '');
        $key = trim($key); $val = trim($val, " \t\n\r\0\x0B\"'");
        $_ENV[$key] = $val;
        putenv("$key=$val");
    }
}