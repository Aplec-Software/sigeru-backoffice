<?php

namespace App\Core;

/**
 * Sesión del backoffice (sesión de PHP del lado del servidor).
 * La cookie es HttpOnly y SameSite=Strict; se regenera el identificador al
 * iniciar sesión y la sesión vence luego de un tiempo sin actividad.
 */
class Sesion
{
    private const MINUTOS_INACTIVIDAD = 30;

    public static function iniciar(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }
        session_name('SIGERU_BO');
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => Config::get('BASE_PATH', '') ?: '/',
            'httponly' => true,
            'secure' => Config::get('COOKIE_SEGURA', '1') === '1',
            'samesite' => 'Strict',
        ]);
        session_start();
        $ultimo = $_SESSION['ultimo_uso'] ?? time();
        if (time() - $ultimo > self::MINUTOS_INACTIVIDAD * 60) {
            self::cerrar();
            session_start();
        }
        $_SESSION['ultimo_uso'] = time();
    }

    public static function entrar(array $usuario): void
    {
        session_regenerate_id(true);
        $_SESSION['usuario'] = [
            'id' => (int) $usuario['id'],
            'nombre' => $usuario['nombre'] . ' ' . $usuario['apellido'],
            'idioma' => $usuario['idioma'],
        ];
    }

    public static function usuario(): ?array
    {
        return $_SESSION['usuario'] ?? null;
    }

    public static function cerrar(): void
    {
        $_SESSION = [];
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
    }

    /** Mensaje que se muestra una sola vez en la página siguiente. */
    public static function aviso(?string $texto = null, string $tipo = 'ok'): ?array
    {
        if ($texto !== null) {
            $_SESSION['aviso'] = ['texto' => $texto, 'tipo' => $tipo];
            return null;
        }
        $aviso = $_SESSION['aviso'] ?? null;
        unset($_SESSION['aviso']);
        return $aviso;
    }
}
