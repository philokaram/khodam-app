<?php
function view(string $path, array $data = []): void
{
    extract($data, EXTR_SKIP);

    $viewFile = APP_PATH . '/views/' . $path . '.php';
    if (!file_exists($viewFile)) {
        http_response_code(500);
        exit("View not found: $path");
    }

    // كشف صفحات المصادقة تلقائياً
    $isAuth = $isAuth ?? str_starts_with($path, 'auth/');

    ob_start();
    require $viewFile;
    $content = ob_get_clean();

    require APP_PATH . '/views/layouts/main.php';
}

function component(string $name, array $props = []): void
{
    extract($props, EXTR_SKIP);
    $file = APP_PATH . '/views/components/' . $name . '.php';
    if (file_exists($file)) require $file;
}