<?php

namespace Tests\Feature;

use App\Models\Incidencia;
use Tests\Support\TestCase;

class IncidenciaTest extends TestCase
{
    public function testElListadoFiltraPorEstado(): void
    {
        [$filas, $total] = Incidencia::listar(['estado' => 'abierta'], 1, 50);
        $this->assertSame($this->contar('incidencias', "estado = 'abierta'"), $total);
        foreach ($filas as $f) {
            $this->assertSame('abierta', $f['estado']);
        }
    }

    public function testCambiarDejaRegistroEnElHistorial(): void
    {
        $id = (int) $this->db->query("SELECT id FROM incidencias WHERE estado = 'abierta' LIMIT 1")->fetchColumn();
        $antes = $this->contar('incidencia_historial', "incidencia_id = $id");
        Incidencia::cambiar($id, ['estado' => 'en_curso', 'cuadrilla_id' => 1], 'abierta', 'en_curso', 1, 'Prueba');
        $i = Incidencia::buscar($id);
        $this->assertSame('en_curso', $i['estado']);
        $this->assertSame($antes + 1, $this->contar('incidencia_historial', "incidencia_id = $id"));
    }

    public function testElMonitoreoSoloMuestraActivas(): void
    {
        $recientes = Incidencia::activasRecientes();
        $this->assertNotEmpty($recientes);
        foreach ($recientes as $r) {
            $this->assertContains($r['estado'], ['abierta', 'en_curso']);
        }
    }

    public function testLasTransicionesCoincidenConLaApiDeGestion(): void
    {
        $api = dirname(__DIR__, 3) . '/sigeru-api-gestion/app/Models/Incidencia.php';
        if (!is_file($api)) {
            $this->assertTrue(true); // el repositorio de la API no está al lado
            return;
        }
        $codigo = file_get_contents($api);
        foreach (Incidencia::TRANSICIONES as $desde => $hacia) {
            $this->assertStringContainsString("'$desde' => ['" . implode("', '", $hacia) . "']", $codigo);
        }
    }
}
