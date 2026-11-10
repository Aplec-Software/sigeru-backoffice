<?php use App\Core\Csrf; use App\Models\Incidencia; $siguientes = Incidencia::TRANSICIONES[$i['estado']] ?? []; ?>
<div class="bo-encabezado"><h1>Incidencia #<?= (int) $i['id'] ?> <span class="bo-badge <?= e($i['estado']) ?>"><?= e(str_replace('_', ' ', $i['estado'])) ?></span></h1>
  <a class="bo-boton" href="<?= url('incidencias') ?>">‹ Volver al listado</a></div>
<div class="bo-columnas">
<section class="bo-tarjeta"><h2>Datos</h2>
  <dl class="bo-datos">
    <dt>Tipo</dt><dd><?= e(str_replace('_', ' ', $i['tipo'])) ?></dd>
    <dt>Prioridad</dt><dd><span class="bo-prioridad <?= e($i['prioridad']) ?>"><?= e($i['prioridad']) ?></span></dd>
    <dt>Contenedor</dt><dd><code><?= e($i['contenedor_codigo']) ?></code> · <?= e($i['direccion']) ?> (<?= e($i['zona']) ?>)</dd>
    <dt>Origen</dt><dd><?= e($i['origen']) ?> · <?= (int) $i['cantidad_reportes'] ?> reporte(s)</dd>
    <dt>Descripción</dt><dd><?= nl2br(e($i['descripcion'] ?: '—')) ?></dd>
    <dt>Creada</dt><dd><?= fecha($i['creado_en']) ?></dd>
    <dt>Cuadrilla</dt><dd><?= e($i['cuadrilla'] ?? 'Sin asignar') ?><?= $i['asignado_en'] ? ' · desde ' . fecha($i['asignado_en']) : '' ?></dd>
    <?php if ($i['resuelto_en']): ?><dt>Resuelta</dt><dd><?= fecha($i['resuelto_en']) ?> — <?= e($i['resolucion']) ?></dd><?php endif; ?>
  </dl>
  <div id="mapa" class="bo-mapa bo-mapa-chico" data-lat="<?= e($i['latitud']) ?>" data-lng="<?= e($i['longitud']) ?>" data-zoom="16" data-punto="<?= e($i['contenedor_codigo']) ?>"></div>
</section>
<section class="bo-tarjeta"><h2>Gestión</h2>
  <?php if (in_array($i['estado'], ['abierta', 'en_curso'], true)): ?>
  <form method="post" action="<?= url('incidencias/' . (int) $i['id'] . '/asignar') ?>" class="bo-form-linea"><?= Csrf::campo() ?>
    <label>Asignar a cuadrilla <select name="cuadrilla_id" required><option value="">Elegir…</option>
      <?php foreach ($cuadrillas as $c): ?><option value="<?= (int) $c['id'] ?>" <?= $i['cuadrilla_id'] == $c['id'] ? 'selected' : '' ?>><?= e($c['nombre']) ?></option><?php endforeach; ?></select></label>
    <button class="bo-boton bo-primario"><?= $i['cuadrilla_id'] ? 'Reasignar' : 'Asignar' ?></button></form>
  <?php endif; ?>
  <?php if ($siguientes): ?>
  <form method="post" action="<?= url('incidencias/' . (int) $i['id'] . '/estado') ?>" class="bo-form"><?= Csrf::campo() ?>
    <label>Cambiar estado <select name="estado" required>
      <?php foreach ($siguientes as $s): if ($s === 'en_curso' && !$i['cuadrilla_id']) continue; ?><option value="<?= $s ?>"><?= str_replace('_', ' ', $s) ?></option><?php endforeach; ?></select></label>
    <label class="completo">Comentario (obligatorio para resolver) <textarea name="comentario" maxlength="300" rows="3"></textarea></label>
    <div class="completo bo-acciones-form"><button class="bo-boton">Aplicar cambio</button></div></form>
  <?php endif; ?>
  <h2>Historial</h2>
  <ol class="bo-historial">
    <?php foreach ($historial as $h): ?>
      <li><time><?= fecha($h['creado_en']) ?></time> <strong><?= e($h['estado_anterior'] ? str_replace('_', ' ', $h['estado_anterior']) . ' → ' : '') ?><?= e(str_replace('_', ' ', $h['estado_nuevo'])) ?></strong>
        <?= $h['usuario_nombre'] ? '· ' . e($h['usuario_nombre'] . ' ' . $h['usuario_apellido']) : '' ?><?= $h['comentario'] ? '<br><span>' . e($h['comentario']) . '</span>' : '' ?></li>
    <?php endforeach; ?>
  </ol>
</section></div>
