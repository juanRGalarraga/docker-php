<?php
/**
 * Listado de clientes
 * Created: 2021-11-03
 * Author: Gastón Fernandez
 */
	require_once(DIR_model."clientes".DS."class.clientes.inc.php");
	require_once(DIR_includes."class.listhelper.inc.php");
	$clientes = new cClientes;
	$params = array();

	//Buscamos los filtros...
	$pag = FindParam("pag");
	$pag = ($pag > 0)? $pag:1;
	$params["pag"] = $pag;

	$buscar = $_POST['buscar'] ?? null;
	if(!is_null($buscar)){ $params['buscar'] = $buscar; }
	$lid = FindParam("lid");
	if($lid){
		$params["listid"] = $lid;
		
		//Intentamos porcesar el ordenamiento de la tabla...
		$orden = SecureInt(FindParam("ord"));
		if(!is_null($orden)){
			if($ordenamiento = $clientes->SetOrden($lid,$orden)){
				$params["orden"] = $ordenamiento;
			}
		}
	}
	$clientes->GetListado($params);
	if (SecureInt($clientes->header->cant??null,0) == 0) {
	?>
		<p class="text-center">La consulta no devolvió resultados.</p>
	<?php
		return;
	}

	$hlp = new ListHelper;
	$hlp->SetPropeties($clientes);
?>
	<span class="lid" id="lid" hidden><?php echo $clientes->listid; ?></span>
	<table class="tabla-general table table-striped table-hover mb-0 d-block d-xxl-table table-responsive">
		<thead>
			<tr>
				<th class="text-end <?php echo $hlp->Orden(0); ?>" data-field="0">ID</th>
				<th class="<?php echo $hlp->Orden(1); ?>" data-field="1">Nombre</th>
				<th class="<?php echo $hlp->Orden(2); ?>" data-field="2">Apellido</th>
				<th class="text-end <?php echo $hlp->Orden(3); ?>" data-field="3">N° Doc.</th>
				<th>Email</th>
				<th>Teléfono</th>
				<th>Dirección</th>
				<th>Ciudad</th>
				<th>Región</th>
				<th>Fecha alta</th>
				<th></th>
			</tr>
		</thead>
		<tbody  title="Doble clic para editar">
	<?php
		foreach($clientes->listado as $linea) {
	?>
			<tr onDblClick="Editar('<?php echo $linea->id; ?>');">
				<td class="text-end"><?php echo $linea->id; ?></td>
				<td><?php echo $hlp->replace($linea->nombre ?? "-"); ?></td>
				<td><?php echo $hlp->replace($linea->apellido ?? "-"); ?></td>
				<td class="text-end"><?php echo $hlp->replace($linea->nro_doc ?? "-"); ?></td>
				<td><?php echo $hlp->replace($linea->email ?? "-"); ?></td>
				<td><?php echo $hlp->replace($linea->tel_movil ?? "-"); ?></td>
				<td><?php echo $linea->dir ?? "-"; ?></td>
				<td><?php echo $linea->ciudad_nombre ?? "-"; ?></td>
				<td><?php echo $linea->region_nombre ?? "-"; ?></td>
				<td><?php echo $linea->sys_fecha_alta_txtshort ?? "-"; ?></td>
				<td onclick="Editar('<?php echo $linea->id; ?>');">
					<button class="btn btn-primary btn-sm" type="button">
						<i class="fas fa-eye" aria-hidden="true"></i>
					</button>
				</td>
			</tr>
	<?php
		}
	?>
		</tbody>
	</table>
	<?php
	$hlp->ListadoMgr = "listado";
	$hlp->ListadoMgrExtraParam = "";
	$hlp->Footer();

