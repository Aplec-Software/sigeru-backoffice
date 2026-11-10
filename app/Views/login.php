<?php use App\Core\Csrf; ?>
<!doctype html>
<html lang="es">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Backoffice SiGeRU</title>
<link rel="icon" href="<?= url('img/favicon.png') ?>"><link rel="stylesheet" href="<?= url('css/backoffice.css') ?>"></head>
<body class="bo-login">
  <form method="post" action="<?= url('login') ?>" class="bo-login-caja">
    <img src="<?= url('img/logo-sigeru.png') ?>" alt="SiGeRU" width="70" height="84">
    <h1>Backoffice de administración</h1>
    <?php if (!empty($aviso)): ?><div class="aviso aviso-<?= e($aviso['tipo']) ?>" role="alert"><?= e($aviso['texto']) ?></div><?php endif; ?>
    <?= Csrf::campo() ?>
    <label>Correo electrónico <input type="email" name="email" required autocomplete="username" autofocus></label>
    <label>Contraseña <input type="password" name="password" required autocomplete="current-password"></label>
    <button class="bo-boton bo-primario">Ingresar</button>
    <p class="bo-nota">Acceso restringido a administradores municipales. Las acciones quedan registradas.</p>
  </form>
</body>
</html>
