<?php

namespace App\Controllers;

use App\Core\Clock;
use App\Models\Reporte;

/**
 * Reportes estadísticos para la toma de decisiones, con exportación a CSV.
 */
class ReporteController extends Controller
{
    private function parametros(): array
    {
        $tipo = in_array($_GET['tipo'] ?? '', Reporte::TIPOS, true) ? $_GET['tipo'] : 'incidencias_zona';
        $fecha = fn ($v, $def) => preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $v) ? $v : $def;
        $hasta = $fecha($_GET['hasta'] ?? '', Clock::hoy());
        $desde = $fecha($_GET['desde'] ?? '', substr(Clock::mas(-30 * 24 * 60), 0, 10));
        return [$tipo, $desde, $hasta];
    }

    public function index(): string
    {
        [$tipo, $desde, $hasta] = $this->parametros();
        return $this->vista('reportes', ['tipo' => $tipo, 'desde' => $desde, 'hasta' => $hasta, 'filas' => Reporte::generar($tipo, $desde, $hasta)], 'Reportes');
    }

    public function exportar(): string
    {
        [$tipo, $desde, $hasta] = $this->parametros();
        $filas = Reporte::generar($tipo, $desde, $hasta);
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="reporte_' . $tipo . '_' . $desde . '_' . $hasta . '.csv"');
        $salida = fopen('php://temp', 'r+');
        fwrite($salida, "\xEF\xBB\xBF");
        if ($filas) {
            fputcsv($salida, array_keys($filas[0]), ';');
            foreach ($filas as $f) {
                // Excel en español espera la coma como separador decimal
                $f = array_map(fn ($v) => preg_match('/^-?\d+\.\d+$/', (string) $v) ? str_replace('.', ',', (string) $v) : $v, $f);
                fputcsv($salida, $f, ';');
            }
        }
        rewind($salida);
        return (string) stream_get_contents($salida);
    }
}
