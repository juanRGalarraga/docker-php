<?php
require_once(DIR_model."solicitudes".DS."class.solicitudes.inc.php");
require_once(DIR_includes."class.listhelper.inc.php");

$params = [];

$post = CleanArray($_POST);

unset($post['archivo']);
unset($post['content']);
unset($post[BASE_VPATH]);

$hlp = new ListHelper;
$solicitudes = new cSolicitudes;

$lid = FindParam("lid");
if($lid){
	$params["listid"] = $lid;
	
	//Intentamos porcesar el ordenamiento de la tabla...
	$orden = SecureInt(FindParam("ord"));
        
	if(!is_null($orden)){
		if($ordenamiento = $solicitudes->SetOrden($lid,$orden)){
			$params["orden"] = $ordenamiento;
		}
	}

	$buscar = FindParam("buscar");
	if(!is_null($buscar)){
		$params["buscar"] = $buscar;
	}
}
$solicitudes->GetListado($params);
if (SecureInt($solicitudes->header->cant??null,0) == 0) {
?>
	<p class="text-center">La consulta no devolvió resultados.</p>
<?php
	return;
}

$hlp = new ListHelper;
$hlp->SetPropeties(clone $solicitudes);
$hlp->ListadoMgr = "listado";
$hlp->ListadoMgrExtraParam = "";
// Showvar($solicitudes);
?>
<span class="lid" id="lid" hidden><?php echo $solicitudes->listid; ?></span>
<table class="tabla-general table table-striped table-hover mb-0 d-block d-xxl-table table-responsive">
	<thead>
		<tr>
			<th class="text-center <?php echo $hlp->Orden(0); ?>" data-field="0">N° Solicitud</th>
			<th>Fecha hora</th>
			<th>Nombre</th>
			<th>Apellido</th>
			<th>Nro. Doc.</th>
			<th class="text-end" >Capital / plazo</th>
			<th class="text-center">Origen</th>
			<th class="text-center">Paso</th>
			<th class="text-center">Estado</th>
			<th class="text-center"> </th>
		</tr>
	</thead>
	<tbody  title="Doble clic para ver detalles">
<?php
	foreach($solicitudes->listado as $linea) {
?>
		<tr onDblClick="VerSolicitud('<?php echo $linea->id; ?>');">
			<td class="text-center"><?php echo $linea->id; ?></td>
			<td><?php echo $linea->sys_fecha_modif_txtshort; ?></td>
			<td><?php echo $linea->nombre; ?></td>
			<td><?php echo $linea->apellido; ?></td>
			<td><?php echo $linea->nro_doc; ?></td>
			<td class="text-end"><?php echo "$ ".F($linea->capital)." / ".$linea->plazo; ?></td>
			<td class="text-center"><?php echo $linea->origen ?? "-"; ?></td>
			<td class="text-center"><?php echo $linea->paso ?? "-"; ?></td>
			<td class="text-center" title="<?php echo (isset(ESTADOS_SOLICITUDES[$linea->estado_solicitud]))?ESTADOS_SOLICITUDES[$linea->estado_solicitud]:$linea->estado_solicitud; ?>">
			<?php
			switch($linea->estado_solicitud) {
				case 'APRO': echo '<i class="fas fa-check text-success"></i>'; break;
				case 'RECH': echo '<i class="fas fa-times text-danger"></i>'; break;
				case 'FAIL': echo '<i class="fas fa-ban text-dark"></i>'; break;
				case 'ANUL': echo '<i class="fas fa-minus-circle text-warning"></i>'; break;
			}
			?></td>
			<td title="Ver"> <button class="btn btn-primary btn-sm" onclick="VerSolicitud(<?php echo $linea->id; ?>);"> <i class="fas fa-eye"></i> </button> </td>
		</tr>
<?php
	}
?>
	</tbody>
</table>
<?php

// $hlp->Paginador = $solicitudes->Paginador;
// $hlp->ItemsPorPagina = $solicitudes->ItemsPorPagina;
// $hlp->PaginaActual = $solicitudes->PaginaActual;
// $hlp->ItemsTotales = $solicitudes->ItemsTotales;
// $hlp->ItemsActuales = $solicitudes->ItemsActuales;
// $hlp->ListadoMgr = "listado";
// $hlp->ListadoMgrExtraParam = "";
$hlp->Footer();


