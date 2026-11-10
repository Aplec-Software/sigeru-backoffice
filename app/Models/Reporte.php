<?php

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

/**
 * Consultas de los reportes estadísticos del backoffice.
 * Cada método devuelve filas listas para mostrar en tabla, gráfico o CSV.
 */
class Reporte extends Model
{
    protected static string $tabla = 'incidencias';

    public const TIPOS = ['incidencias_zona', 'tiempos', 'top_desbordados', 'volumen_zona', 'volumen_tipo', 'ocupacion', 'flota', 'cuadrillas'];

    public static function generar(string $tipo, string $desde, string $hasta): array
    {
        $hasta .= ' 23:59:59';
        $horas = Database::horasEntre('i.creado_en', 'i.resuelto_en');
        return match ($tipo) {
            'incidencias_zona' => static::todos("SELECT z.nombre AS zona, COUNT(i.id) AS total,
                    SUM(CASE WHEN i.estado IN ('abierta','en_curso') THEN 1 ELSE 0 END) AS activas,
                    SUM(CASE WHEN i.estado = 'resuelta' THEN 1 ELSE 0 END) AS resueltas
                FROM zonas z LEFT JOIN contenedores c ON c.zona_id = z.id
                LEFT JOIN incidencias i ON i.contenedor_id = c.id AND i.creado_en BETWEEN ? AND ?
                GROUP BY z.id, z.nombre ORDER BY total DESC", [$desde, $hasta]),
            'tiempos' => static::todos("SELECT i.tipo, COUNT(*) AS resueltas, ROUND(AVG($horas), 1) AS horas_promedio, ROUND(MAX($horas), 1) AS horas_maximo
                FROM incidencias i WHERE i.estado = 'resuelta' AND i.creado_en BETWEEN ? AND ? GROUP BY i.tipo ORDER BY horas_promedio DESC", [$desde, $hasta]),
            'top_desbordados' => static::todos("SELECT c.codigo, c.direccion, z.nombre AS zona, COUNT(*) AS desbordes, SUM(i.cantidad_reportes) AS reportes
                FROM incidencias i JOIN contenedores c ON c.id = i.contenedor_id JOIN zonas z ON z.id = c.zona_id
                WHERE i.tipo = 'desborde' AND i.creado_en BETWEEN ? AND ? GROUP BY c.id, c.codigo, c.direccion, z.nombre ORDER BY desbordes DESC LIMIT 15", [$desde, $hasta]),
            'volumen_zona' => static::todos("SELECT z.nombre AS zona, COUNT(*) AS recolecciones, ROUND(SUM(r.peso_kg) / 1000, 2) AS toneladas
                FROM recolecciones r JOIN contenedores c ON c.id = r.contenedor_id JOIN zonas z ON z.id = c.zona_id
                WHERE r.creado_en BETWEEN ? AND ? GROUP BY z.id, z.nombre ORDER BY toneladas DESC", [$desde, $hasta]),
            'volumen_tipo' => static::todos("SELECT tr.nombre AS tipo_residuo, COUNT(*) AS recolecciones, ROUND(SUM(r.peso_kg) / 1000, 2) AS toneladas
                FROM recolecciones r JOIN contenedores c ON c.id = r.contenedor_id JOIN tipos_residuo tr ON tr.id = c.tipo_residuo_id
                WHERE r.creado_en BETWEEN ? AND ? GROUP BY tr.id, tr.nombre ORDER BY toneladas DESC", [$desde, $hasta]),
            'ocupacion' => static::todos('SELECT nombre, tipo, capacidad_ton, ocupacion_ton, porcentaje FROM v_ocupacion_centros ORDER BY porcentaje DESC'),
            'flota' => static::todos("SELECT ca.matricula, ca.estado, COUNT(a.id) AS recorridos,
                    (SELECT COUNT(*) FROM mantenimientos m WHERE m.camion_id = ca.id AND m.estado = 'realizado') AS mantenimientos,
                    (SELECT ROUND(COALESCE(SUM(m.costo), 0), 2) FROM mantenimientos m WHERE m.camion_id = ca.id AND m.estado = 'realizado') AS costo_mantenimiento
                FROM camiones ca LEFT JOIN asignaciones a ON a.camion_id = ca.id AND a.estado = 'completada' AND a.fecha BETWEEN ? AND ?
                WHERE ca.activo = 1 GROUP BY ca.id, ca.matricula, ca.estado ORDER BY recorridos DESC", [substr($desde, 0, 10), substr($hasta, 0, 10)]),
            'cuadrillas' => static::todos("SELECT cu.nombre AS cuadrilla, COUNT(DISTINCT a.id) AS recorridos,
                    (SELECT COUNT(*) FROM incidencias i WHERE i.cuadrilla_id = cu.id AND i.estado = 'resuelta' AND i.resuelto_en BETWEEN ? AND ?) AS incidencias_resueltas
                FROM cuadrillas cu LEFT JOIN asignaciones a ON a.cuadrilla_id = cu.id AND a.estado = 'completada' AND a.fecha BETWEEN ? AND ?
                GROUP BY cu.id, cu.nombre ORDER BY recorridos DESC", [$desde, $hasta, substr($desde, 0, 10), substr($hasta, 0, 10)]),
            default => [],
        };
    }

    public static function indicadores(): array
    {
        $porEstado = fn (string $sql) => array_column(static::todos($sql), 'n', 'estado');
        return [
            'incidencias' => $porEstado('SELECT estado, COUNT(*) AS n FROM incidencias GROUP BY estado'),
            'contenedores' => $porEstado('SELECT estado, COUNT(*) AS n FROM contenedores WHERE activo = 1 GROUP BY estado'),
            'camiones' => $porEstado('SELECT estado, COUNT(*) AS n FROM camiones WHERE activo = 1 GROUP BY estado'),
            'pendientes' => (int) static::valor("SELECT COUNT(*) FROM usuarios WHERE estado = 'pendiente'"),
            'stock_bajo' => (int) static::valor('SELECT COUNT(*) FROM repuestos WHERE activo = 1 AND stock < stock_minimo'),
        ];
    }

    public static function mapa(): array
    {
        return [
            'contenedores' => static::todos('SELECT c.id, c.codigo, c.direccion, c.latitud, c.longitud, c.estado, c.nivel_llenado, tr.nombre AS tipo_residuo
                FROM contenedores c JOIN tipos_residuo tr ON tr.id = c.tipo_residuo_id WHERE c.activo = 1'),
            'centros' => static::todos('SELECT id, nombre, tipo, latitud, longitud, ROUND(ocupacion_ton * 100 / capacidad_ton, 1) AS porcentaje FROM centros WHERE activo = 1'),
            'incidencias' => static::todos("SELECT i.id, i.tipo, i.estado, i.prioridad, c.codigo AS contenedor_codigo, c.latitud, c.longitud
                FROM incidencias i JOIN contenedores c ON c.id = i.contenedor_id WHERE i.estado IN ('abierta','en_curso')"),
            'camiones' => static::todos('SELECT id, matricula, estado, ultima_latitud AS latitud, ultima_longitud AS longitud FROM camiones WHERE activo = 1 AND ultima_latitud IS NOT NULL'),
        ];
    }
}
