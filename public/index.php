<?php

/**
 * Backoffice de administración municipal (aplicación monolítica MVC).
 */
require __DIR__ . '/../app/bootstrap.php';

use App\Core\Config;
use App\Core\Lang;
use App\Core\Sesion;

header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header("Content-Security-Policy: default-src 'self'; img-src 'self' data: https://tile.openstreetmap.org https://*.tile.openstreetmap.org; script-src 'self' https://unpkg.com; style-src 'self' 'unsafe-inline' https://unpkg.com; frame-ancestors 'none'");

Sesion::iniciar();
Lang::fijar($_GET['lang'] ?? (Sesion::usuario()['idioma'] ?? null));

$ruta = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$base = Config::get('BASE_PATH', '');
if ($base !== '' && str_starts_with($ruta, $base)) {
    $ruta = substr($ruta, strlen($base));
}
$ruta = '/' . trim($ruta, '/');

$router = require __DIR__ . '/../app/rutas.php';
try {
    echo $router->despachar($_SERVER['REQUEST_METHOD'] ?? 'GET', $ruta);
} catch (\Throwable $e) {
    error_log('[backoffice] ' . $e->getMessage() . ' ' . $e->getFile() . ':' . $e->getLine());
    http_response_code(500);
    echo App\Core\Vista::render('error', ['mensaje' => Lang::t('error.interno')], 'Error');
}
