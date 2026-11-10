<?php

namespace App\Controllers;

use App\Models\Auditoria;

class AuditoriaController extends Controller
{
    public function index(): string
    {
        $pagina = max(1, (int) ($_GET['pagina'] ?? 1));
        [$filas, $total] = Auditoria::listar($_GET, $pagina, 40);
        return $this->vista('auditoria', ['filas' => $filas, 'total' => $total, 'pagina' => $pagina, 'servicios' => Auditoria::servicios()], 'Auditoría');
    }
}
