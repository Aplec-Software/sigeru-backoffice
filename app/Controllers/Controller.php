<?php

namespace App\Controllers;

use App\Core\HttpException;
use App\Core\Router;
use App\Core\Sesion;
use App\Core\Validator;
use App\Core\Vista;

abstract class Controller
{
    protected function vista(string $plantilla, array $datos = [], string $titulo = 'SiGeRU'): string
    {
        return Vista::render($plantilla, $datos + ['aviso' => Sesion::aviso()], $titulo);
    }

    protected function volver(string $ruta, string $mensaje, string $tipo = 'ok'): never
    {
        Sesion::aviso($mensaje, $tipo);
        Router::redirigir($ruta);
    }

    /** Valida con las mismas reglas que las APIs; si falla vuelve con el primer error. */
    protected function validar(array $datos, array $reglas, string $rutaError): array
    {
        try {
            return Validator::validar($datos, $reglas);
        } catch (HttpException $e) {
            $this->volver($rutaError, implode(' ', $e->errores ?? [$e->getMessage()]), 'error');
        }
    }

    protected function json(array $datos): string
    {
        header('Content-Type: application/json; charset=utf-8');
        return json_encode($datos, JSON_UNESCAPED_UNICODE);
    }
}
