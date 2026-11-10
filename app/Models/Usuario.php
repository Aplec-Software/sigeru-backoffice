<?php

namespace App\Models;

use App\Core\Clock;
use App\Core\Model;

class Usuario extends Model
{
    protected static string $tabla = 'usuarios';
    protected static array $campos = ['rol_id', 'nombre', 'apellido', 'documento', 'email', 'telefono', 'password_hash', 'estado', 'idioma', 'centro_id', 'creado_en'];
    protected static array $filtros = ['rol_id', 'estado'];
    protected static array $busqueda = ['t.nombre', 't.apellido', 't.email', 't.documento'];
    protected static string $orden = "CASE t.estado WHEN 'pendiente' THEN 0 ELSE 1 END, t.apellido, t.nombre";

    protected static function consultaBase(): string
    {
        return 'SELECT t.*, r.nombre AS rol, ce.nombre AS centro FROM usuarios t
                JOIN roles r ON r.id = t.rol_id LEFT JOIN centros ce ON ce.id = t.centro_id';
    }

    public static function porEmail(string $email): ?array
    {
        return static::uno(static::consultaBase() . ' WHERE t.email = ?', [mb_strtolower($email)]);
    }

    public static function registrarIntento(array $u, bool $correcto, int $maximo = 5, int $minutos = 15): void
    {
        if ($correcto) {
            static::ejecutar('UPDATE usuarios SET intentos_fallidos = 0, bloqueado_hasta = NULL, ultimo_acceso = ? WHERE id = ?', [Clock::ahora(), $u['id']]);
            return;
        }
        $intentos = (int) $u['intentos_fallidos'] + 1;
        $bloqueo = $intentos >= $maximo ? Clock::mas($minutos) : null;
        static::ejecutar('UPDATE usuarios SET intentos_fallidos = ?, bloqueado_hasta = ? WHERE id = ?', [$bloqueo ? 0 : $intentos, $bloqueo, $u['id']]);
    }

    public static function revocarSesiones(int $id): void
    {
        static::ejecutar('UPDATE sesiones SET revocado_en = ? WHERE usuario_id = ? AND revocado_en IS NULL', [Clock::ahora(), $id]);
    }

    public static function pendientes(): int
    {
        return (int) static::valor("SELECT COUNT(*) FROM usuarios WHERE estado = 'pendiente'");
    }

    public static function roles(): array
    {
        return static::todos('SELECT id, nombre FROM roles ORDER BY id');
    }

    public static function centros(): array
    {
        return static::todos('SELECT id, nombre FROM centros WHERE activo = 1 ORDER BY nombre');
    }
}
