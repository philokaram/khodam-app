<?php
function config(string $key, $default = null)
{
    static $loaded = [];
    [$file, $sub] = array_pad(explode('.', $key, 2), 2, null);
    if (!isset($loaded[$file])) {
        $path = APP_PATH . '/config/' . $file . '.php';
        $loaded[$file] = file_exists($path) ? require $path : [];
    }
    if ($sub === null) return $loaded[$file];
    return $loaded[$file][$sub] ?? $default;
}