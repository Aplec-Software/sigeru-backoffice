<?php use App\Core\Csrf; ?>
<div class="bo-encabezado"><h1>Parámetros del sistema</h1><p>Permiten ajustar el comportamiento de SiGeRU sin modificar el código.</p></div>
<section class="bo-tarjeta"><form method="post" action="<?= url('parametros') ?>"><?= Csrf::campo() ?>
<table class="bo-tabla"><thead><tr><th>Parámetro</th><th>Descripción</th><th>Tipo</th><th>Valor</th></tr></thead><tbody>
<?php foreach ($parametros as $p): ?>
  <tr><td><code><?= e($p['clave']) ?></code><?= $p['publico'] ? ' <span class="bo-badge activo">público</span>' : '' ?></td><td><?= e($p['descripcion']) ?></td><td><?= e($p['tipo']) ?></td>
  <td><?php if ($p['tipo'] === 'booleano'): ?><select name="valor[<?= e($p['clave']) ?>]"><option value="1">Sí</option><option value="0" <?= $p['valor'] === '0' ? 'selected' : '' ?>>No</option></select>
  <?php else: ?><input name="valor[<?= e($p['clave']) ?>]" value="<?= e($p['valor']) ?>" <?= $p['tipo'] === 'entero' ? 'type="number" min="0"' : '' ?>><?php endif; ?></td></tr>
<?php endforeach; ?>
</tbody></table><div class="bo-acciones-form"><button class="bo-boton bo-primario">Guardar cambios</button></div></form></section>
