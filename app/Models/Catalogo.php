<?php

namespace App\Models;

use App\Core\Model;

/** Catálogos del negocio: zonas y tipos de residuo (se editan sin tocar el código). */
class Catalogo extends Model
{
    protected static string $tabla = 'zonas';

    public static function zonas(): array
    {
        return static::todos('SELECT z.*, (SELECT COUNT(*) FROM contenedores c WHERE c.zona_id = z.id AND c.activo = 1) AS contenedores
                              FROM zonas z ORDER BY z.nombre');
    }

    public static function tipos(): array
    {
        return static::todos('SELECT t.*, (SELECT COUNT(*) FROM contenedores c WHERE c.tipo_residuo_id = t.id AND c.activo = 1) AS contenedores
                              FROM tipos_residuo t ORDER BY t.id');
    }

    /** Los nombres son únicos (uq_zonas_nombre, uq_tipos_residuo_nombre): se controla antes de guardar. */
    public static function nombreRepetido(string $tabla, string $nombre, ?int $id): bool
    {
        $tabla = $tabla === 'zonas' ? 'zonas' : 'tipos_residuo';
        return (bool) static::valor("SELECT COUNT(*) FROM $tabla WHERE LOWER(nombre) = LOWER(?) AND id <> ?", [$nombre, $id ?? 0]);
    }

    public static function guardarZona(?int $id, array $d): void
    {
        if ($id) {
            static::ejecutar('UPDATE zonas SET nombre = ?, descripcion = ?, activa = ? WHERE id = ?', [$d['nombre'], $d['descripcion'], $d['activa'], $id]);
        } else {
            static::ejecutar('INSERT INTO zonas (nombre, descripcion, activa) VALUES (?, ?, ?)', [$d['nombre'], $d['descripcion'], $d['activa']]);
        }
    }

    public static function guardarTipo(?int $id, array $d): void
    {
        if ($id) {
            static::ejecutar('UPDATE tipos_residuo SET nombre = ?, color = ?, reciclable = ?, activo = ? WHERE id = ?', [$d['nombre'], $d['color'], $d['reciclable'], $d['activo'], $id]);
        } else {
            static::ejecutar('INSERT INTO tipos_residuo (nombre, color, reciclable, activo) VALUES (?, ?, ?, ?)', [$d['nombre'], $d['color'], $d['reciclable'], $d['activo']]);
        }
    }
}
