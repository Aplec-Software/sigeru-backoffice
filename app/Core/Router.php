<?php

namespace App\Core;

/**
 * Enrutador del backoffice. Todas las rutas, salvo el login, exigen un
 * administrador con sesión iniciada; los POST exigen además el token CSRF.
 */
class Router
{
    private array $rutas = [];

    public function get(string $patron, array $accion, bool $publica = false): void
    {
        $this->rutas[] = ['GET', $patron, $accion, $publica];
    }

    public function post(string $patron, array $accion, bool $publica = false): void
    {
        $this->rutas[] = ['POST', $patron, $accion, $publica];
    }

    public function despachar(string $metodo, string $ruta): string
    {
        foreach ($this->rutas as [$m, $patron, $accion, $publica]) {
            $regex = '#^' . preg_replace('/\{(\w+)\}/', '(?P<$1>\d+)', $patron) . '$#';
            if ($m !== $metodo || !preg_match($regex, $ruta, $coinc)) {
                continue;
            }
            if (!$publica && Sesion::usuario() === null) {
                self::redirigir('login');
            }
            if ($metodo === 'POST' && !Csrf::valido($_POST['_csrf'] ?? null)) {
                http_response_code(403);
                return Vista::render('error', ['mensaje' => Lang::t('error.csrf')], 'Error');
            }
            $params = array_filter($coinc, 'is_string', ARRAY_FILTER_USE_KEY);
            [$clase, $metodoAccion] = $accion;
            return (new $clase())->$metodoAccion(...array_map('intval', $params));
        }
        http_response_code(404);
        return Vista::render('error', ['mensaje' => Lang::t('error.no_encontrado')], 'Error');
    }

    public static function redirigir(string $ruta): never
    {
        header('Location: ' . url($ruta), true, 303);
        exit;
    }
}
