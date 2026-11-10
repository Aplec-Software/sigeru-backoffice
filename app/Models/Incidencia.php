<?php

namespace App\Models;

use App\Core\Clock;
use App\Core\Model;

class Incidencia extends Model
{
    protected static string $tabla = 'incidencias';
    protected static array $filtros = ['estado', 'tipo', 'prioridad', 'cuadrilla_id'];
    protected static array $busqueda = ['c.codigo', 'c.direccion'];
    protected static string $orden = 't.creado_en DESC';

    // mismas transiciones que la API de Gestión (diagrama de estados)
    public const TRANSICIONES = [
        'abierta' => ['en_curso', 'descartada'],
        'en_curso' => ['resuelta', 'abierta'],
        'resuelta' => ['abierta'],
        'descartada' => ['abierta'],
    ];

    protected static function consultaBase(): string
    {
        return 'SELECT t.*, c.codigo AS contenedor_codigo, c.direccion, c.latitud, c.longitud, z.nombre AS zona, cu.nombre AS cuadrilla
                FROM incidencias t JOIN contenedores c ON c.id = t.contenedor_id JOIN zonas z ON z.id = c.zona_id
                LEFT JOIN cuadrillas cu ON cu.id = t.cuadrilla_id';
    }

    public static function historial(int $id): array
    {
        return static::todos('SELECT h.*, u.nombre AS usuario_nombre, u.apellido AS usuario_apellido FROM incidencia_historial h
                              LEFT JOIN usuarios u ON u.id = h.usuario_id WHERE h.incidencia_id = ? ORDER BY h.creado_en, h.id', [$id]);
    }

    public static function cuadrillas(): array
    {
        return static::todos('SELECT id, nombre FROM cuadrillas WHERE activa = 1 ORDER BY nombre');
    }

    public static function cambiar(int $id, array $cambios, string $anterior, string $nuevo, int $usuarioId, ?string $comentario): void
    {
        $sets = implode(', ', array_map(fn ($c) => "$c = ?", array_keys($cambios)));
        static::ejecutar("UPDATE incidencias SET $sets WHERE id = ?", [...array_values($cambios), $id]);
        static::ejecutar('INSERT INTO incidencia_historial (incidencia_id, estado_anterior, estado_nuevo, usuario_id, comentario, creado_en) VALUES (?, ?, ?, ?, ?, ?)',
            [$id, $anterior, $nuevo, $usuarioId, $comentario, Clock::ahora()]);
    }

    /** Últimas incidencias activas, para el monitoreo en tiempo real del panel. */
    public static function activasRecientes(int $limite = 12): array
    {
        return static::todos(static::consultaBase() . " WHERE t.estado IN ('abierta','en_curso') ORDER BY t.creado_en DESC LIMIT $limite");
    }
}
