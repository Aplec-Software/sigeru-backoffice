<?php

namespace App\Controllers;

use App\Core\Audit;
use App\Core\Lang;
use App\Models\Parametro;

class ParametroController extends Controller
{
    public function index(): string
    {
        return $this->vista('parametros', ['parametros' => Parametro::lista()], 'Parámetros');
    }

    public function guardar(): string
    {
        $cambiados = 0;
        foreach (Parametro::lista() as $p) {
            if (!array_key_exists($p['clave'], $_POST['valor'] ?? [])) {
                continue;
            }
            $valor = trim((string) $_POST['valor'][$p['clave']]);
            $valido = match ($p['tipo']) {
                'entero' => filter_var($valor, FILTER_VALIDATE_INT) !== false && (int) $valor >= 0,
                'decimal' => is_numeric($valor),
                'booleano' => in_array($valor, ['0', '1'], true),
                default => $valor !== '' && mb_strlen($valor) <= 255,
            };
            if (!$valido) {
                $this->volver('parametros', Lang::t('parametros.invalido', ['clave' => $p['clave']]), 'error');
            }
            if ($valor !== $p['valor']) {
                Parametro::cambiar($p['clave'], strip_tags($valor));
                Audit::registrar('modificar', 'parametro', $p['clave'], $valor);
                $cambiados++;
            }
        }
        $this->volver('parametros', Lang::t('parametros.guardados', ['n' => $cambiados]));
    }
}
