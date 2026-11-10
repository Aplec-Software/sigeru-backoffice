<?php

namespace App\Controllers;

use App\Core\Audit;
use App\Core\Lang;
use App\Models\Catalogo;

class CatalogoController extends Controller
{
    public function index(): string
    {
        return $this->vista('catalogos', ['zonas' => Catalogo::zonas(), 'tipos' => Catalogo::tipos()], 'Catálogos');
    }

    public function guardarZona(): string
    {
        $d = $this->validar($_POST, ['nombre' => 'required|string|min:2|max:60', 'descripcion' => 'nullable|string|max:200', 'activa' => 'required|in:0,1'], 'catalogos');
        $id = (int) ($_POST['id'] ?? 0) ?: null;
        if (Catalogo::nombreRepetido('zonas', $d['nombre'], $id)) {
            $this->volver('catalogos', Lang::t('catalogos.repetido'), 'error');
        }
        Catalogo::guardarZona($id, $d + ['descripcion' => null]);
        Audit::registrar($id ? 'modificar' : 'crear', 'zona', $id, $d['nombre']);
        $this->volver('catalogos', Lang::t('ok.guardado'));
    }

    public function guardarTipo(): string
    {
        $d = $this->validar($_POST, ['nombre' => 'required|string|min:3|max:50', 'color' => 'required|color', 'reciclable' => 'required|in:0,1', 'activo' => 'required|in:0,1'], 'catalogos');
        $id = (int) ($_POST['id'] ?? 0) ?: null;
        if (Catalogo::nombreRepetido('tipos_residuo', $d['nombre'], $id)) {
            $this->volver('catalogos', Lang::t('catalogos.repetido'), 'error');
        }
        Catalogo::guardarTipo($id, $d);
        Audit::registrar($id ? 'modificar' : 'crear', 'tipo_residuo', $id, $d['nombre']);
        $this->volver('catalogos', Lang::t('ok.guardado'));
    }
}
