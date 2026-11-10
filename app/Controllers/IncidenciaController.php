<?php

namespace App\Controllers;

use App\Core\Audit;
use App\Core\Clock;
use App\Core\Lang;
use App\Core\Notifier;
use App\Core\Sesion;
use App\Models\Incidencia;

class IncidenciaController extends Controller
{
    public function index(): string
    {
        $pagina = max(1, (int) ($_GET['pagina'] ?? 1));
        [$filas, $total] = Incidencia::listar($_GET, $pagina, 25);
        return $this->vista('incidencias/index', ['filas' => $filas, 'total' => $total, 'pagina' => $pagina], 'Incidencias');
    }

    public function ver(int $id): string
    {
        $i = Incidencia::buscar($id) ?? $this->volver('incidencias', Lang::t('error.no_encontrado'), 'error');
        return $this->vista('incidencias/ver', ['i' => $i, 'historial' => Incidencia::historial($id), 'cuadrillas' => Incidencia::cuadrillas()], 'Incidencia #' . $id);
    }

    public function asignar(int $id): string
    {
        $i = Incidencia::buscar($id) ?? $this->volver('incidencias', Lang::t('error.no_encontrado'), 'error');
        $cuadrilla = (int) ($_POST['cuadrilla_id'] ?? 0);
        $validas = array_column(Incidencia::cuadrillas(), 'id');
        if (!in_array($i['estado'], ['abierta', 'en_curso'], true) || !in_array($cuadrilla, array_map('intval', $validas), true)) {
            $this->volver("incidencias/$id", Lang::t('incidencias.no_asignable'), 'error');
        }
        Incidencia::cambiar($id, ['cuadrilla_id' => $cuadrilla, 'estado' => 'en_curso', 'asignado_en' => Clock::ahora()],
            $i['estado'], 'en_curso', Sesion::usuario()['id'], Lang::t('incidencias.asignada_desde_bo'));
        Notifier::aCuadrilla($cuadrilla, 'incidencia_asignada', Lang::t('incidencias.tarea_nueva'), "{$i['tipo']} · {$i['contenedor_codigo']} ({$i['direccion']})", '#/tareas');
        Audit::registrar('asignar', 'incidencia', $id, (string) $cuadrilla);
        $this->volver("incidencias/$id", Lang::t('ok.guardado'));
    }

    public function estado(int $id): string
    {
        $i = Incidencia::buscar($id) ?? $this->volver('incidencias', Lang::t('error.no_encontrado'), 'error');
        $nuevo = $_POST['estado'] ?? '';
        $comentario = trim(strip_tags((string) ($_POST['comentario'] ?? ''))) ?: null;
        if (!in_array($nuevo, Incidencia::TRANSICIONES[$i['estado']] ?? [], true)) {
            $this->volver("incidencias/$id", Lang::t('incidencias.transicion_invalida'), 'error');
        }
        if ($nuevo === 'resuelta' && !$comentario) {
            $this->volver("incidencias/$id", Lang::t('incidencias.resolucion_requerida'), 'error');
        }
        $cambios = ['estado' => $nuevo];
        if ($nuevo === 'resuelta') {
            $cambios += ['resuelto_en' => Clock::ahora(), 'resolucion' => mb_substr($comentario, 0, 300)];
        } elseif ($nuevo === 'abierta') {
            $cambios += ['resuelto_en' => null, 'resolucion' => null, 'cuadrilla_id' => null, 'asignado_en' => null];
        } elseif ($nuevo === 'en_curso' && empty($i['cuadrilla_id'])) {
            $this->volver("incidencias/$id", Lang::t('incidencias.no_asignable'), 'error');
        }
        Incidencia::cambiar($id, $cambios, $i['estado'], $nuevo, Sesion::usuario()['id'], $comentario);
        Audit::registrar('estado_' . $nuevo, 'incidencia', $id);
        $this->volver("incidencias/$id", Lang::t('ok.guardado'));
    }
}
