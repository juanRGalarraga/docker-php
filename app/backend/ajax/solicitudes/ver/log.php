<?php
require_once(DIR_model."solicitudes".DS."class.solicitudLog.inc.php");

$solicitudesLog = new cSolicitudLog;

$listado = $solicitudesLog->Listado($data_solicitud->id);

if (empty($listado)) {
	return cSideKick::ShowEmptySearchMessage();
}
?>
<table class="tabla-general table table-striped table-hover mb-0 td-top">
	<thead>
		<tr>
			<th>Fecha hora</th>
			<th>Tipo</th>
			<th>Paso onBoarding</th>
			<th>Data</th>
			<th>Descripción</th>
			<th>Tag</th>
		</tr>
	</thead>
	<tbody  title="Doble clic para ver detalles">
<?php
	foreach($listado as $linea) {
?>
		<tr>
			<td><?php echo $linea->fechahora; ?></td>
			<td><?php echo $linea->tipo_evento; ?></td>
			<td><?php echo $linea->paso; ?></td>
			<td><?php PonerDatos($linea->data); ?></td>
			<td><?php echo $linea->descripcion; ?></td>
			<td><?php echo $linea->tag; ?></td>
		</tr>
<?php
	}
?>
	</tbody>
</table>
<?php
function PonerDatos($data) {
	global $maxRange;
	if (!empty($data)) {
		echo '<ul class="list-group list-group-flush">';
		foreach($data as $key => $value) {
			echo '<li class="list-group-item">';
			if (is_object($value) or is_array($value)) {
				PonerDatos($value); continue;
			}
			echo '<span class="lbl">';
			echo CortarElipse(str_replace('_',' ',$key), 25);
			echo '</span>';
			echo "<span title=\"$value\">";
			echo CortarElipse($value, 25);
			echo '</span>';
			if (strtolower($key) == 'total') {
				if (is_numeric($value)) {
					$maxRange = floor($value);
				}
			}
		}
		echo '</ul>';
	}
}?>