<?php use App\Core\Csrf; ?>
<div class="bo-encabezado"><h1>Catálogos</h1><p>Zonas y tipos de residuo que usan todas las aplicaciones.</p></div>
<div class="bo-columnas">
<section class="bo-tarjeta"><h2>Zonas</h2>
<div class="bo-lista"><div class="bo-fila-form bo-fila-zona bo-fila-titulo"><span>Nombre</span><span>Cont.</span><span>Activa</span><span></span></div>
<?php foreach ($zonas as $z): ?>
<form method="post" action="<?= url('catalogos/zona') ?>" class="bo-fila-form bo-fila-zona"><?= Csrf::campo() ?><input type="hidden" name="id" value="<?= (int) $z['id'] ?>">
  <input name="nombre" value="<?= e($z['nombre']) ?>" required maxlength="60"><span><?= (int) $z['contenedores'] ?></span>
  <select name="activa"><option value="1">Sí</option><option value="0" <?= $z['activa'] ? '' : 'selected' ?>>No</option></select><input type="hidden" name="descripcion" value="<?= e($z['descripcion']) ?>"><button class="bo-boton bo-chico">Guardar</button></form>
<?php endforeach; ?>
<form method="post" action="<?= url('catalogos/zona') ?>" class="bo-fila-form bo-fila-zona"><?= Csrf::campo() ?><input name="nombre" placeholder="Nueva zona" required maxlength="60"><span></span><span></span><input type="hidden" name="activa" value="1"><button class="bo-boton bo-chico bo-primario">Agregar</button></form>
</div></section>
<section class="bo-tarjeta"><h2>Tipos de residuo</h2>
<div class="bo-lista"><div class="bo-fila-form bo-fila-tipo bo-fila-titulo"><span>Nombre</span><span>Color</span><span>Reciclable</span><span>Activo</span><span></span></div>
<?php foreach ($tipos as $tp): ?>
<form method="post" action="<?= url('catalogos/tipo') ?>" class="bo-fila-form bo-fila-tipo"><?= Csrf::campo() ?><input type="hidden" name="id" value="<?= (int) $tp['id'] ?>">
  <input name="nombre" value="<?= e($tp['nombre']) ?>" required maxlength="50"><input type="color" name="color" value="<?= e($tp['color']) ?>">
  <select name="reciclable"><option value="1">Sí</option><option value="0" <?= $tp['reciclable'] ? '' : 'selected' ?>>No</option></select>
  <select name="activo"><option value="1">Sí</option><option value="0" <?= $tp['activo'] ? '' : 'selected' ?>>No</option></select><button class="bo-boton bo-chico">Guardar</button></form>
<?php endforeach; ?>
<form method="post" action="<?= url('catalogos/tipo') ?>" class="bo-fila-form bo-fila-tipo"><?= Csrf::campo() ?><input name="nombre" placeholder="Nuevo tipo de residuo" required maxlength="50"><input type="color" name="color" value="#2a7f8f">
  <select name="reciclable"><option value="1">Sí</option><option value="0">No</option></select><span></span><input type="hidden" name="activo" value="1"><button class="bo-boton bo-chico bo-primario">Agregar</button></form>
</div></section></div>
