<?php

namespace App\Core;

/**
 * Vistas del backoffice: plantillas PHP en app/Views.
 * Todo dato que se imprime pasa por e() (escape de HTML contra XSS).
 */
class Vista
{
    public static function render(string $plantilla, array $datos = [], ?string $titulo = null): string
    {
        $contenido = self::parcial($plantilla, $datos);
        return self::parcial('layout', $datos + ['contenido' => $contenido, 'titulo' => $titulo ?? 'SiGeRU']);
    }

    public static function parcial(string $plantilla, array $datos = []): string
    {
        extract($datos, EXTR_SKIP);
        ob_start();
        require dirname(__DIR__) . '/Views/' . $plantilla . '.php';
        return (string) ob_get_clean();
    }
}
