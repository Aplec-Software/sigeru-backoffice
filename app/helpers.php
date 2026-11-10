<?php

// Funciones auxiliares para las vistas (espacio de nombres global).

/** Escapa un valor para imprimirlo dentro de HTML. */
function e(mixed $valor): string
{
    return htmlspecialchars((string) ($valor ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Traducción abreviada para las vistas. */
function t(string $clave, array $reemplazos = []): string
{
    return \App\Core\Lang::t($clave, $reemplazos);
}

/** Dirección dentro del backoffice (respeta el prefijo /backoffice). */
function url(string $ruta = ''): string
{
    return rtrim(\App\Core\Config::get('BASE_PATH', ''), '/') . '/' . ltrim($ruta, '/');
}

function fecha(?string $valor): string
{
    return $valor ? date('d/m/Y H:i', strtotime($valor)) : '—';
}
