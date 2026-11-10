<?php

namespace App\Controllers;

use App\Models\Incidencia;
use App\Models\Reporte;
use App\Core\Params;

/**
 * Panel de control: indicadores, mapa y monitoreo de incidencias en tiempo real.
 */
class PanelController extends Controller
{
    public function index(): string
    {
        return $this->vista('panel', [
            'ind' => Reporte::indicadores(),
            'recientes' => Incidencia::activasRecientes(),
            'mapa' => ['lat' => Params::get('mapa_latitud', '-34.896'), 'lng' => Params::get('mapa_longitud', '-56.15'), 'zoom' => Params::get('mapa_zoom', '13')],
        ], 'Panel');
    }

    /** GET /api/monitoreo: el panel lo consulta cada 20 segundos. */
    public function monitoreo(): string
    {
        return $this->json(['indicadores' => Reporte::indicadores(), 'recientes' => Incidencia::activasRecientes()]);
    }

    public function mapa(): string
    {
        return $this->json(Reporte::mapa());
    }
}
