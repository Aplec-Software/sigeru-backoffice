<?php

namespace Tests\Feature;

use App\Models\Catalogo;
use App\Models\Usuario;
use Tests\Support\TestCase;

class CatalogoUsuarioTest extends TestCase
{
    public function testDetectaNombresRepetidosSinImportarMayusculas(): void
    {
        $this->assertTrue(Catalogo::nombreRepetido('zonas', 'BUCEO', null));
        $this->assertFalse(Catalogo::nombreRepetido('zonas', 'Punta Carretas', null));
        $buceo = (int) $this->db->query("SELECT id FROM zonas WHERE nombre = 'Buceo'")->fetchColumn();
        $this->assertFalse(Catalogo::nombreRepetido('zonas', 'Buceo', $buceo), 'Al editar la misma zona no es repetido');
    }

    public function testGuardarZonaNuevaYModificarla(): void
    {
        Catalogo::guardarZona(null, ['nombre' => 'Punta Carretas', 'descripcion' => null, 'activa' => 1]);
        $id = (int) $this->db->query("SELECT id FROM zonas WHERE nombre = 'Punta Carretas'")->fetchColumn();
        $this->assertGreaterThan(0, $id);
        Catalogo::guardarZona($id, ['nombre' => 'Punta Carretas', 'descripcion' => 'Costa sur', 'activa' => 0]);
        $this->assertSame(1, $this->contar('zonas', "id = $id AND activa = 0"));
    }

    public function testBloqueoPorIntentosFallidos(): void
    {
        $u = Usuario::porEmail('ADMIN@sigeru.uy');
        $this->assertNotNull($u);
        for ($n = 0; $n < 5; $n++) {
            Usuario::registrarIntento(Usuario::porEmail('admin@sigeru.uy'), false);
        }
        $this->assertNotNull(Usuario::porEmail('admin@sigeru.uy')['bloqueado_hasta']);
        Usuario::registrarIntento($u, true);
        $this->assertNull(Usuario::porEmail('admin@sigeru.uy')['bloqueado_hasta']);
    }

    public function testElListadoMuestraPrimeroLosPendientes(): void
    {
        [$filas] = Usuario::listar([], 1, 50);
        $this->assertSame('pendiente', $filas[0]['estado']);
    }
}
