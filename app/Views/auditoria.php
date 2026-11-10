<?php $paginas = (int) ceil($total / 40); ?>
<div class="bo-encabezado"><h1>Auditoría</h1><p>Registro de las acciones sensibles realizadas en todos los servicios.</p></div>
<form class="bo-filtros" method="get">
  <input type="search" name="q" value="<?= e($_GET['q'] ?? '') ?>" placeholder="Detalle o correo">
  <select name="servicio"><option value="">Todos los servicios</option><?php foreach ($servicios as $s): ?><option <?= ($_GET['servicio'] ?? '') === $s ? 'selected' : '' ?>><?= e($s) ?></option><?php endforeach; ?></select>
  <input name="entidad" value="<?= e($_GET['entidad'] ?? '') ?>" placeholder="Entidad (ej. usuario)">
  <button class="bo-boton">Filtrar</button>
</form>
<section class="bo-tarjeta">
<table class="bo-tabla bo-tabla-densa"><thead><tr><th>Fecha</th><th>Usuario</th><th>Servicio</th><th>Acción</th><th>Entidad</th><th>Detalle</th><th>IP</th></tr></thead><tbody>
<?php foreach ($filas as $a): ?>
  <tr><td><?= fecha($a['creado_en']) ?></td><td><?= $a['email'] ? e($a['usuario_nombre'] . ' ' . $a['usuario_apellido']) . '<br><small>' . e($a['email']) . '</small>' : '<small>anónimo / sistema</small>' ?></td>
  <td><?= e($a['servicio']) ?></td><td><code><?= e($a['accion']) ?></code></td><td><?= e($a['entidad']) ?><?= $a['entidad_id'] ? ' #' . e($a['entidad_id']) : '' ?></td><td><?= e($a['detalle'] ?? '') ?></td><td><small><?= e($a['ip'] ?? '') ?></small></td></tr>
<?php endforeach; ?>
<?php if (!$filas): ?><tr><td colspan="7" class="vacio">Sin registros.</td></tr><?php endif; ?>
</tbody></table>
<p class="bo-pie-tabla"><?= $total ?> registros<?php if ($paginas > 1): ?> · página <?= $pagina ?> de <?= $paginas ?>
  <?php if ($pagina > 1): ?><a href="?<?= e(http_build_query(['pagina' => $pagina - 1] + $_GET)) ?>">‹ anterior</a><?php endif; ?>
  <?php if ($pagina < $paginas): ?><a href="?<?= e(http_build_query(['pagina' => $pagina + 1] + $_GET)) ?>">siguiente ›</a><?php endif; ?><?php endif; ?></p>
</section>
