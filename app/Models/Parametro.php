<?php

namespace App\Models;

use App\Core\Model;

class Parametro extends Model
{
    protected static string $tabla = 'parametros';

    public static function lista(): array
    {
        return static::todos('SELECT * FROM parametros ORDER BY clave');
    }

    public static function cambiar(string $clave, string $valor): void
    {
        static::ejecutar('UPDATE parametros SET valor = ? WHERE clave = ?', [$valor, $clave]);
    }
}
