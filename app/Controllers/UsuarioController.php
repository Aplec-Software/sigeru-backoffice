<?php

namespace App\Controllers;

use App\Core\Audit;
use App\Core\Clock;
use App\Core\Lang;
use App\Core\Notifier;
use App\Core\Sesion;
use App\Core\Validator;
use App\Models\Usuario;

/**
 * Gestión de usuarios y roles desde el backoffice (acceso directo a la base).
 */
class UsuarioController extends Controller
{
    public function index(): string
    {
        $pagina = max(1, (int) ($_GET['pagina'] ?? 1));
        [$filas, $total] = Usuario::listar($_GET, $pagina, 25);
        return $this->vista('usuarios/index', ['filas' => $filas, 'total' => $total, 'pagina' => $pagina, 'roles' => Usuario::roles()], 'Usuarios');
    }

    public function nuevo(): string
    {
        return $this->vista('usuarios/form', ['u' => null, 'roles' => Usuario::roles(), 'centros' => Usuario::centros()], 'Nuevo usuario');
    }

    public function editar(int $id): string
    {
        $u = Usuario::buscar($id) ?? $this->volver('usuarios', Lang::t('error.no_encontrado'), 'error');
        return $this->vista('usuarios/form', ['u' => $u, 'roles' => Usuario::roles(), 'centros' => Usuario::centros()], 'Editar usuario');
    }

    public function guardar(int $id = 0): string
    {
        $volverA = $id ? "usuarios/$id/editar" : 'usuarios/nuevo';
        $reglas = [
            'rol_id' => 'required|int|in:1,2,3,4',
            'nombre' => 'required|string|min:2|max:60',
            'apellido' => 'required|string|min:2|max:60',
            'email' => 'required|email|max:120',
            'telefono' => 'nullable|telefono',
            'documento' => 'nullable|ci',
            'centro_id' => 'nullable|int',
            'idioma' => 'required|in:es,en',
        ];
        if (!$id || !empty($_POST['password'])) {
            $reglas['password'] = 'required|password';
        }
        $d = $this->validar($_POST, $reglas, $volverA);
        $d['email'] = mb_strtolower($d['email']);
        if (Usuario::existe('email', $d['email'], $id ?: null)) {
            $this->volver($volverA, Lang::t('usuarios.email_existente'), 'error');
        }
        if ($d['rol_id'] !== 4 && empty($d['documento'])) {
            $this->volver($volverA, Lang::t('usuarios.documento_requerido'), 'error');
        }
        if ($d['rol_id'] === 3 && empty($d['centro_id'])) {
            $this->volver($volverA, Lang::t('usuarios.centro_requerido'), 'error');
        }
        if ($d['rol_id'] !== 3) {
            $d['centro_id'] = null;
        }
        if (isset($d['password'])) {
            $d['password_hash'] = password_hash($d['password'], PASSWORD_BCRYPT, ['cost' => 12]);
            unset($d['password']);
        }
        if ($id === Sesion::usuario()['id'] && $d['rol_id'] !== 1) {
            $this->volver($volverA, Lang::t('usuarios.no_auto_modificar'), 'error');
        }
        if ($id) {
            Usuario::actualizar($id, $d);
            Audit::registrar('modificar', 'usuario', $id);
        } else {
            $id = Usuario::crear($d + ['estado' => 'activo', 'creado_en' => Clock::ahora()]);
            Audit::registrar('crear', 'usuario', $id);
        }
        $this->volver('usuarios', Lang::t('ok.guardado'));
    }

    /** Aprobar, rechazar, activar o desactivar. */
    public function estado(int $id): string
    {
        $u = Usuario::buscar($id) ?? $this->volver('usuarios', Lang::t('error.no_encontrado'), 'error');
        $estado = $_POST['estado'] ?? '';
        if (!in_array($estado, ['activo', 'inactivo', 'rechazado'], true) || $id === Sesion::usuario()['id']) {
            $this->volver('usuarios', Lang::t('usuarios.no_auto_modificar'), 'error');
        }
        Usuario::actualizar($id, ['estado' => $estado]);
        if ($estado !== 'activo') {
            Usuario::revocarSesiones($id);
        } elseif ($u['estado'] === 'pendiente') {
            Notifier::aUsuarios([$id], 'cuenta_aprobada', Lang::t('usuarios.cuenta_aprobada'), Lang::t('usuarios.cuenta_aprobada_detalle'));
        }
        Audit::registrar('estado_' . $estado, 'usuario', $id);
        $this->volver('usuarios', Lang::t('ok.guardado'));
    }

    /** Genera una contraseña temporal que se muestra una sola vez al administrador. */
    public function resetearClave(int $id): string
    {
        Usuario::buscar($id) ?? $this->volver('usuarios', Lang::t('error.no_encontrado'), 'error');
        do {
            $temporal = 'Sg-' . bin2hex(random_bytes(4)) . '#' . random_int(10, 99) . 'A';
        } while (!Validator::claveSegura($temporal));
        Usuario::actualizar($id, ['password_hash' => password_hash($temporal, PASSWORD_BCRYPT, ['cost' => 12])]);
        Usuario::revocarSesiones($id);
        Audit::registrar('resetear_clave', 'usuario', $id);
        $this->volver("usuarios/$id/editar", Lang::t('usuarios.clave_temporal', ['clave' => $temporal]));
    }
}
