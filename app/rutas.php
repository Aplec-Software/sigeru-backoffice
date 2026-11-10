<?php

use App\Controllers\AuditoriaController;
use App\Controllers\AuthController;
use App\Controllers\CatalogoController;
use App\Controllers\IncidenciaController;
use App\Controllers\PanelController;
use App\Controllers\ParametroController;
use App\Controllers\ReporteController;
use App\Controllers\UsuarioController;
use App\Core\Router;

$r = new Router();
$r->get('/login', [AuthController::class, 'formulario'], true);
$r->post('/login', [AuthController::class, 'entrar'], true);
$r->post('/salir', [AuthController::class, 'salir']);

$r->get('/', [PanelController::class, 'index']);
$r->get('/api/monitoreo', [PanelController::class, 'monitoreo']);
$r->get('/api/mapa', [PanelController::class, 'mapa']);

$r->get('/usuarios', [UsuarioController::class, 'index']);
$r->get('/usuarios/nuevo', [UsuarioController::class, 'nuevo']);
$r->post('/usuarios', [UsuarioController::class, 'guardar']);
$r->get('/usuarios/{id}/editar', [UsuarioController::class, 'editar']);
$r->post('/usuarios/{id}', [UsuarioController::class, 'guardar']);
$r->post('/usuarios/{id}/estado', [UsuarioController::class, 'estado']);
$r->post('/usuarios/{id}/clave', [UsuarioController::class, 'resetearClave']);

$r->get('/parametros', [ParametroController::class, 'index']);
$r->post('/parametros', [ParametroController::class, 'guardar']);
$r->get('/catalogos', [CatalogoController::class, 'index']);
$r->post('/catalogos/zona', [CatalogoController::class, 'guardarZona']);
$r->post('/catalogos/tipo', [CatalogoController::class, 'guardarTipo']);

$r->get('/incidencias', [IncidenciaController::class, 'index']);
$r->get('/incidencias/{id}', [IncidenciaController::class, 'ver']);
$r->post('/incidencias/{id}/asignar', [IncidenciaController::class, 'asignar']);
$r->post('/incidencias/{id}/estado', [IncidenciaController::class, 'estado']);

$r->get('/reportes', [ReporteController::class, 'index']);
$r->get('/reportes/exportar', [ReporteController::class, 'exportar']);
$r->get('/auditoria', [AuditoriaController::class, 'index']);

return $r;
