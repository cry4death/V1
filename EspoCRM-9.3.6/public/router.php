<?php
/**
 * Маршрутизация для встроенного сервера PHP (`php -S ... router.php`).
 * Без этого /api/v1/* попадает в index.php клиента и возвращает HTML → в UI «Bad server response».
 */
$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/'
);

$local = __DIR__ . $uri;
if ($uri !== '/' && is_file($local)) {
    return false;
}

if (str_starts_with($uri, '/api/v1/portal-access')) {
    require __DIR__ . '/api/v1/portal-access/index.php';
    return true;
}

if (str_starts_with($uri, '/api/v1')) {
    require __DIR__ . '/api/v1/index.php';
    return true;
}

if (str_starts_with($uri, '/portal')) {
    require __DIR__ . '/portal/index.php';
    return true;
}

if (str_starts_with($uri, '/oauth/callback')) {
    require __DIR__ . '/oauth/callback/index.php';
    return true;
}

if ($uri === '/install' || str_starts_with($uri, '/install/')) {
    require __DIR__ . '/install/index.php';
    return true;
}

require __DIR__ . '/index.php';
return true;
