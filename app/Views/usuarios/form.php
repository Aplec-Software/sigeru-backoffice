<?php use App\Core\Csrf; $v = fn ($c) => e($u[$c] ?? ''); ?>
<div class="bo-encabezado"><h1><?= $u ? 'Editar usuario' : 'Nuevo usuario' ?></h1><a class="bo-boton" href="<?= url('usuarios') ?>">‹ Volver</a></div>
<section class="bo-tarjeta">
<form method="post" action="<?= url($u ? 'usuarios/' . (int) $u['id'] : 'usuarios') ?>" class="bo-form">
  <?= Csrf::campo() ?>
  <label>Nombre <input name="nombre" required maxlength="60" value="<?= $v('nombre') ?>"></label>
  <label>Apellido <input name="apellido" required maxlength="60" value="<?= $v('apellido') ?>"></label>
  <label>Correo <input name="email" type="email" required maxlength="120" value="<?= $v('email') ?>"></label>
  <label>Teléfono <input name="telefono" maxlength="20" value="<?= $v('telefono') ?>"></label>
  <label>Rol <select name="rol_id" required><?php foreach ($roles as $r): ?><option value="<?= (int) $r['id'] ?>" <?= ($u['rol_id'] ?? 4) == $r['id'] ? 'selected' : '' ?>><?= e($r['nombre']) ?></option><?php endforeach; ?></select></label>
  <label>Cédula (personal municipal) <input name="documento" maxlength="11" value="<?= $v('documento') ?>"></label>
  <label>Centro (solo operarios) <select name="centro_id"><option value="">—</option><?php foreach ($centros as $c): ?><option value="<?= (int) $c['id'] ?>" <?= ($u['centro_id'] ?? '') == $c['id'] ? 'selected' : '' ?>><?= e($c['nombre']) ?></option><?php endforeach; ?></select></label>
  <label>Idioma <select name="idioma"><option value="es">Español</option><option value="en" <?= ($u['idioma'] ?? '') === 'en' ? 'selected' : '' ?>>English</option></select></label>
  <label class="completo">Contraseña <?= $u ? '(dejar vacío para no cambiarla)' : '' ?> <input name="password" type="password" autocomplete="new-password" <?= $u ? '' : 'required' ?>>
    <small>Mínimo 10 caracteres, con mayúsculas, minúsculas, números y un símbolo.</small></label>
  <div class="completo bo-acciones-form"><button class="bo-boton bo-primario">Guardar</button></div>
</form>
<?php if ($u): ?>
<form method="post" action="<?= url('usuarios/' . (int) $u['id'] . '/clave') ?>" class="bo-reset"><?= Csrf::campo() ?>
  <button class="bo-boton">Generar contraseña temporal</button> <small>Cierra las sesiones abiertas del usuario.</small></form>
<?php endif; ?>
</section>
