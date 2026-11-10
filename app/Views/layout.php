<?php use App\Core\Csrf; use App\Core\Sesion; $u = Sesion::usuario(); $actual = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH); ?>
<!doctype html>
<html lang="<?= e(App\Core\Lang::idioma()) ?>">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($titulo) ?> · Backoffice SiGeRU</title>
  <link rel="icon" href="<?= url('img/favicon.png') ?>">
  <link rel="stylesheet" href="<?= url('css/backoffice.css') ?>">
</head>
<body data-base="<?= e(url('')) ?>">
<header class="bo-barra">
  <a href="<?= url('') ?>" class="bo-marca"><img src="<?= url('img/logo-sigeru-icono.png') ?>" alt="" width="30" height="30"> SiGeRU <span>Backoffice</span></a>
  <nav>
    <?php foreach (['' => 'menu.panel', 'usuarios' => 'menu.usuarios', 'incidencias' => 'menu.incidencias', 'reportes' => 'menu.reportes', 'parametros' => 'menu.parametros', 'catalogos' => 'menu.catalogos', 'auditoria' => 'menu.auditoria'] as $ruta => $clave):
        $activo = $ruta === '' ? rtrim($actual, '/') === rtrim(url(''), '/') : str_starts_with($actual, url($ruta)); ?>
      <a href="<?= url($ruta) ?>" class="<?= $activo ? 'activo' : '' ?>"><?= e(t($clave)) ?></a>
    <?php endforeach; ?>
  </nav>
  <form method="post" action="<?= url('salir') ?>" class="bo-salir"><?= Csrf::campo() ?><span><?= e($u['nombre'] ?? '') ?></span><button><?= e(t('menu.salir')) ?></button></form>
</header>
<main class="bo-contenido">
  <?php if (!empty($aviso)): ?><div class="aviso aviso-<?= e($aviso['tipo']) ?>" role="status"><?= e($aviso['texto']) ?></div><?php endif; ?>
  <?= $contenido ?>
</main>
<script type="module" src="<?= url('js/backoffice.js') ?>"></script>
</body>
</html>
