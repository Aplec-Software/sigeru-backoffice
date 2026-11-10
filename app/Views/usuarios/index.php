<?php use App\Core\Csrf; $paginas = (int) ceil($total / 25); ?>
<div class="bo-encabezado"><h1>Usuarios y roles</h1><a class="bo-boton bo-primario" href="<?= url('usuarios/nuevo') ?>">+ Nuevo usuario</a></div>
<form class="bo-filtros" method="get">
  <input type="search" name="q" value="<?= e($_GET['q'] ?? '') ?>" placeholder="Nombre, correo o cédula">
  <select name="rol_id"><option value="">Todos los roles</option><?php foreach ($roles as $r): ?><option value="<?= (int) $r['id'] ?>" <?= ($_GET['rol_id'] ?? '') == $r['id'] ? 'selected' : '' ?>><?= e($r['nombre']) ?></option><?php endforeach; ?></select>
  <select name="estado"><option value="">Todos los estados</option><?php foreach (['pendiente', 'activo', 'inactivo', 'rechazado'] as $es): ?><option <?= ($_GET['estado'] ?? '') === $es ? 'selected' : '' ?>><?= $es ?></option><?php endforeach; ?></select>
  <button class="bo-boton">Filtrar</button>
</form>
<section class="bo-tarjeta">
<table class="bo-tabla"><thead><tr><th>Nombre</th><th>Correo</th><th>Rol</th><th>Centro</th><th>Estado</th><th>Último acceso</th><th></th></tr></thead><tbody>
<?php foreach ($filas as $u): ?>
  <tr class="<?= $u['estado'] === 'pendiente' ? 'resaltada' : '' ?>">
    <td><strong><?= e($u['nombre'] . ' ' . $u['apellido']) ?></strong></td><td><?= e($u['email']) ?></td><td><?= e($u['rol']) ?></td><td><?= e($u['centro'] ?? '—') ?></td>
    <td><span class="bo-badge <?= e($u['estado']) ?>"><?= e($u['estado']) ?></span></td><td><?= fecha($u['ultimo_acceso']) ?></td>
    <td class="bo-acciones">
      <a class="bo-boton bo-chico" href="<?= url('usuarios/' . (int) $u['id'] . '/editar') ?>">Editar</a>
      <?php foreach (($u['estado'] === 'pendiente' ? ['activo' => 'Aprobar', 'rechazado' => 'Rechazar'] : ($u['estado'] === 'activo' ? ['inactivo' => 'Desactivar'] : ['activo' => 'Activar'])) as $nuevo => $texto): ?>
        <form method="post" action="<?= url('usuarios/' . (int) $u['id'] . '/estado') ?>"><?= Csrf::campo() ?><input type="hidden" name="estado" value="<?= $nuevo ?>"><button class="bo-boton bo-chico <?= $nuevo === 'activo' ? 'bo-exito' : '' ?>"><?= $texto ?></button></form>
      <?php endforeach; ?>
    </td></tr>
<?php endforeach; ?>
</tbody></table>
<p class="bo-pie-tabla"><?= $total ?> usuarios<?php if ($paginas > 1): ?> · página <?= $pagina ?> de <?= $paginas ?>
  <?php if ($pagina > 1): ?><a href="?<?= e(http_build_query(['pagina' => $pagina - 1] + $_GET)) ?>">‹ anterior</a><?php endif; ?>
  <?php if ($pagina < $paginas): ?><a href="?<?= e(http_build_query(['pagina' => $pagina + 1] + $_GET)) ?>">siguiente ›</a><?php endif; ?><?php endif; ?></p>
</section>
