<?php

namespace App\Models;

use App\Core\Model;

class Auditoria extends Model
{
    protected static string $tabla = 'auditoria';
    protected static array $filtros = ['servicio', 'accion', 'entidad'];
    protected static array $busqueda = ['t.detalle', 'u.email'];
    protected static string $orden = 't.creado_en DESC, t.id DESC';

    protected static function consultaBase(): string
    {
        return 'SELECT t.*, u.nombre AS usuario_nombre, u.apellido AS usuario_apellido, u.email FROM auditoria t LEFT JOIN usuarios u ON u.id = t.usuario_id';
    }

    public static function servicios(): array
    {
        return array_column(static::todos('SELECT DISTINCT servicio FROM auditoria ORDER BY servicio'), 'servicio');
    }
}
