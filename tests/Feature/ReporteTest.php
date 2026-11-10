<?php

namespace Tests\Feature;

use App\Models\Reporte;
use Tests\Support\TestCase;

class ReporteTest extends TestCase
{
    public function testTodosLosReportesDevuelvenDatos(): void
    {
        foreach (Reporte::TIPOS as $tipo) {
            $filas = Reporte::generar($tipo, '2026-01-01', '2026-10-30');
            $this->assertNotEmpty($filas, "El reporte $tipo vino vacío");
        }
    }

    public function testIncidenciasPorZonaSumaElTotal(): void
    {
        $filas = Reporte::generar('incidencias_zona', '2000-01-01', '2026-12-31');
        $this->assertSame($this->contar('incidencias'), array_sum(array_map('intval', array_column($filas, 'total'))));
    }

    public function testUnPeriodoSinDatosNoFalla(): void
    {
        $this->assertSame([], Reporte::generar('tiempos', '2001-01-01', '2001-01-31'));
    }

    public function testIndicadoresDelPanel(): void
    {
        $ind = Reporte::indicadores();
        $this->assertSame($this->contar('usuarios', "estado = 'pendiente'"), $ind['pendientes']);
        $this->assertSame($this->contar('incidencias', "estado = 'abierta'"), (int) ($ind['incidencias']['abierta'] ?? 0));
        $mapa = Reporte::mapa();
        foreach (['contenedores', 'centros', 'incidencias', 'camiones'] as $capa) {
            $this->assertArrayHasKey($capa, $mapa);
        }
    }
}
