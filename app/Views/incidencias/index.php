<?php $paginas = (int) ceil($total / 25);
$opciones = ['estado' => ['abierta', 'en_curso', 'resuelta', 'descartada'], 'tipo' => ['desborde', 'rotura', 'quema', 'falta_recoleccion', 'otro'], 'prioridad' => ['alta', 'media', 'baja']]; ?>
<div class="bo-encabezado"><h1>Incidencias</h1><p>Reportes de vecinos, códigos QR y cuadrillas.</p></div>
<form class="bo-filtros" method="get">
  <input type="search" name="q" value="<?= e($_GET['q'] ?? '') ?>" placeholder="Código o dirección del contenedor">
  <?php foreach ($opciones as $campo => $valores): ?>
    <select name="<?= $campo ?>"><option value=""><?= ucfirst($campo) ?>: todos</option>
      <?php foreach ($valores as $v): ?><option value="<?= $v ?>" <?= ($_GET[$campo] ?? '') === $v ? 'selected' : '' ?>><?= str_replace('_', ' ', $v) ?></option><?php endforeach; ?></select>
  <?php endforeach; ?>
  <button class="bo-boton">Filtrar</button>
</form>
<section class="bo-tarjeta">
<table class="bo-tabla"><thead><tr><th>#</th><th>Tipo</th><th>Contenedor</th><th>Zona</th><th>Prioridad</th><th>Estado</th><th>Reportes</th><th>Origen</th><th>Creada</th><th>Cuadrilla</th></tr></thead><tbody>
<?php foreach ($filas as $i): ?>
  <tr><td><a href="<?= url('incidencias/' . (int) $i['id']) ?>">#<?= (int) $i['id'] ?></a></td><td><?= e(str_replace('_', ' ', $i['tipo'])) ?></td>
  <td><code><?= e($i['contenedor_codigo']) ?></code><br><small><?= e($i['direccion']) ?></small></td><td><?= e($i['zona']) ?></td>
  <td><span class="bo-prioridad <?= e($i['prioridad']) ?>"><?= e($i['prioridad']) ?></span></td><td><span class="bo-badge <?= e($i['estado']) ?>"><?= e(str_replace('_', ' ', $i['estado'])) ?></span></td>
  <td class="num"><?= (int) $i['cantidad_reportes'] ?></td><td><?= e($i['origen']) ?></td><td><?= fecha($i['creado_en']) ?></td><td><?= e($i['cuadrilla'] ?? '—') ?></td></tr>
<?php endforeach; ?>
<?php if (!$filas): ?><tr><td colspan="10" class="vacio">No hay incidencias con esos filtros.</td></tr><?php endif; ?>
</tbody></table>
<p class="bo-pie-tabla"><?= $total ?> incidencias<?php if ($paginas > 1): ?> · página <?= $pagina ?> de <?= $paginas ?>
  <?php if ($pagina > 1): ?><a href="?<?= e(http_build_query(['pagina' => $pagina - 1] + $_GET)) ?>">‹ anterior</a><?php endif; ?>
  <?php if ($pagina < $paginas): ?><a href="?<?= e(http_build_query(['pagina' => $pagina + 1] + $_GET)) ?>">siguiente ›</a><?php endif; ?><?php endif; ?></p>
</section>
