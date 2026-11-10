<?php $inc = $ind['incidencias']; $cont = $ind['contenedores']; $cam = $ind['camiones']; ?>
<div class="bo-encabezado"><h1>Panel de administración</h1><p>Monitoreo en tiempo real · se actualiza cada 20 segundos <span id="hora-monitoreo"></span></p></div>
<div class="bo-kpis" id="kpis">
  <div class="bo-kpi alerta"><strong data-kpi="abierta"><?= (int) ($inc['abierta'] ?? 0) ?></strong><span>Incidencias abiertas</span></div>
  <div class="bo-kpi"><strong data-kpi="en_curso"><?= (int) ($inc['en_curso'] ?? 0) ?></strong><span>En curso</span></div>
  <div class="bo-kpi"><strong><?= (int) ($cont['desbordado'] ?? 0) ?></strong><span>Contenedores desbordados</span></div>
  <div class="bo-kpi"><strong><?= (int) ($cam['disponible'] ?? 0) ?> / <?= array_sum(array_map('intval', $cam)) ?></strong><span>Camiones disponibles</span></div>
  <div class="bo-kpi <?= $ind['pendientes'] ? 'alerta' : '' ?>"><strong><?= $ind['pendientes'] ?></strong><span>Solicitudes de registro</span></div>
  <div class="bo-kpi <?= $ind['stock_bajo'] ? 'alerta' : '' ?>"><strong><?= $ind['stock_bajo'] ?></strong><span>Repuestos bajo el mínimo</span></div>
</div>
<div class="bo-columnas">
  <section class="bo-tarjeta"><h2>Mapa de incidencias activas</h2>
    <div id="mapa" class="bo-mapa" data-lat="<?= e($mapa['lat']) ?>" data-lng="<?= e($mapa['lng']) ?>" data-zoom="<?= e($mapa['zoom']) ?>"></div></section>
  <section class="bo-tarjeta"><h2>Últimas incidencias activas</h2>
    <table class="bo-tabla"><thead><tr><th>#</th><th>Tipo</th><th>Contenedor</th><th>Zona</th><th>Estado</th></tr></thead>
    <tbody id="recientes"><?php foreach ($recientes as $r): ?>
      <tr><td><a href="<?= url('incidencias/' . (int) $r['id']) ?>">#<?= (int) $r['id'] ?></a></td><td><?= e(str_replace('_', ' ', $r['tipo'])) ?></td><td><?= e($r['contenedor_codigo']) ?></td><td><?= e($r['zona']) ?></td><td><span class="bo-badge <?= e($r['estado']) ?>"><?= e(str_replace('_', ' ', $r['estado'])) ?></span></td></tr>
    <?php endforeach; ?></tbody></table></section>
</div>
