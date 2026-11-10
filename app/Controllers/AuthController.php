<?php

namespace App\Controllers;

use App\Core\Audit;
use App\Core\Clock;
use App\Core\Lang;
use App\Core\Params;
use App\Core\Router;
use App\Core\Sesion;
use App\Core\Vista;
use App\Models\Usuario;

class AuthController extends Controller
{
    public function formulario(): string
    {
        if (Sesion::usuario()) {
            Router::redirigir('');
        }
        return Vista::parcial('login', ['aviso' => Sesion::aviso()]);
    }

    public function entrar(): string
    {
        $email = trim($_POST['email'] ?? '');
        $clave = (string) ($_POST['password'] ?? '');
        $u = filter_var($email, FILTER_VALIDATE_EMAIL) ? Usuario::porEmail($email) : null;

        if ($u && $u['bloqueado_hasta'] && $u['bloqueado_hasta'] > Clock::ahora()) {
            $this->volver('login', Lang::t('auth.bloqueado'), 'error');
        }
        // solo los administradores activos pueden entrar al backoffice
        if (!$u || !password_verify($clave, $u['password_hash'])) {
            if ($u) {
                Usuario::registrarIntento($u, false, Params::int('login_max_intentos', 5), Params::int('login_bloqueo_minutos', 15));
            }
            $this->volver('login', Lang::t('auth.credenciales'), 'error');
        }
        if ($u['rol'] !== 'administrador' || $u['estado'] !== 'activo') {
            $this->volver('login', Lang::t('auth.solo_admin'), 'error');
        }
        Usuario::registrarIntento($u, true);
        Sesion::entrar($u);
        Audit::registrar('login', 'usuario', $u['id']);
        Router::redirigir('');
    }

    public function salir(): string
    {
        Audit::registrar('logout', 'usuario', Sesion::usuario()['id'] ?? null);
        Sesion::cerrar();
        Router::redirigir('login');
    }
}
