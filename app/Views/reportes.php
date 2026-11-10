<?php
$nombres = [
    'incidencias_zona' => ['Incidencias por zona', 'zona', 'total'],
    'tiempos' => ['Tiempo promedio de resolución (horas) por tipo', 'tipo', 'horas_promedio'],
    'top_desbordados' => ['Contenedores con más desbordes', 'codigo', 'desbordes'],
    'volumen_zona' => ['Toneladas recolectadas por zona', 'zona', 'toneladas'],
    'volumen_tipo' => ['Toneladas recolectadas por tipo de residuo', 'tipo_residuo', 'toneladas'],
    'ocupacion' => ['Ocupación actual de los centros (%)', 'nombre', 'porcentaje'],
    'flota' => ['Recorridos completados por camión', 'matricula', 'recorridos'],
    'cuadrillas' => ['Recorridos completados por cuadrilla', 'cuadrilla', 'recorridos'],
];
[$tituloGrafico, $colEtiqueta, $colValor] = $nombres[$tipo];
$maximo = max([1, ...array_map(fn ($f) => (float) $f[$colValor], $filas)]);
$alto = 30;
?>
<div class="bo-encabezado"><h1>Reportes estadísticos</h1><p>Información para la toma de decisiones. Se puede exportar a CSV (Excel).</p></div>
<form class="bo-filtros" method="get">
  <select name="tipo"><?php foreach ($nombres as $clave => [$nombre]): ?><option value="<?= $clave ?>" <?= $clave === $tipo ? 'selected' : '' ?>><?= e($nombre) ?></option><?php endforeach; ?></select>
  <label>Desde <input type="date" name="desde" value="<?= e($desde) ?>"></label>
  <label>Hasta <input type="date" name="hasta" value="<?= e($hasta) ?>"></label>
  <button class="bo-boton">Generar</button>
  <a class="bo-boton bo-primario" href="<?= url('reportes/exportar') ?>?<?= e(http_build_query(['tipo' => $tipo, 'desde' => $desde, 'hasta' => $hasta])) ?>">Exportar CSV</a>
</form>
<section class="bo-tarjeta">
  <h2><?= e($tituloGrafico) ?><?= $tipo === 'ocupacion' ? ' <small>(no depende del período)</small>' : '' ?></h2>
  <?php if ($filas): ?>
  <svg class="bo-grafico" viewBox="0 0 760 <?= count($filas) * $alto + 10 ?>" role="img" aria-label="<?= e($tituloGrafico) ?>">
    <?php foreach ($filas as $n => $f): $ancho = round(((float) $f[$colValor]) / $maximo * 520); $y = $n * $alto + 5; ?>
      <text x="170" y="<?= $y + 18 ?>" text-anchor="end" class="etiqueta"><?= e(mb_strimwidth((string) $f[$colEtiqueta], 0, 26, '…')) ?></text>
      <rect x="180" y="<?= $y + 4 ?>" width="<?= max(2, $ancho) ?>" height="<?= $alto - 10 ?>" rx="4" class="<?= $tipo === 'ocupacion' && $f[$colValor] >= 85 ? 'barra-alerta' : 'barra' ?>"></rect>
      <text x="<?= 186 + $ancho ?>" y="<?= $y + 18 ?>" class="valor"><?= e($f[$colValor]) ?></text>
    <?php endforeach; ?>
  </svg>
  <table class="bo-tabla"><thead><tr><?php foreach ($filas[0] as $col => $valor): ?><th class="<?= is_numeric($valor) ? 'num' : '' ?>"><?= e(ucfirst(str_replace('_', ' ', $col))) ?></th><?php endforeach; ?></tr></thead><tbody>
    <?php foreach ($filas as $f): ?><tr><?php foreach ($f as $valor): ?><td class="<?= is_numeric($valor) ? 'num' : '' ?>"><?= e($valor ?? '—') ?></td><?php endforeach; ?></tr><?php endforeach; ?>
  </tbody></table>
  <?php else: ?><p class="vacio">No hay datos para el período elegido.</p><?php endif; ?>
</section>
