<?php
/**
 * Listado de préstamos
 * Created: 2021-11-05
 * Author: Gastón Fernandez
 */
	require_once(DIR_model."prestamos".DS."class.prestamos.inc.php");
	require_once(DIR_includes."class.listhelper.inc.php");
	$prestamos = new cPrestamos;
	$params = array();

	//Buscamos los filtros...
	$pag = FindParam("pag");
	$pag = ($pag > 0)? $pag:1;
	$params["pag"] = $pag;

	$buscar = $_POST['buscar'] ?? null;
	if(!is_null($buscar)){ $params['buscar'] = $buscar; }

	$estado = FindParam("estado,filter_estado");
	if(!is_null($estado)){ $params['estado'] = $estado; }

	$mora = SecureInt(FindParam("mora,filter_mora"));
	if(!is_null($mora)){ $params['mora'] = $mora; }

	$plan = SecureInt(FindParam("plan,filter_plan"));
	if(!is_null($plan)){ $params['plan'] = $plan; }


	$lid = FindParam("lid");
	if($lid){
		$params["listid"] = $lid;
		
		//Intentamos porcesar el ordenamiento de la tabla...
		$orden = SecureInt(FindParam("ord"));
		if(!is_null($orden)){
			if($ordenamiento = $prestamos->SetOrden($lid,$orden)){
				$params["orden"] = $ordenamiento;
			}
		}
	}

	$listadoPrestamo = $prestamos->GetListado($params);

	$hlp = new ListHelper;
	$hlp->SetPropeties($prestamos);
?>
<span class="lid" id="lid" hidden><?php echo $prestamos->listid ?? null; ?></span>
<table class="tabla-general table table-striped table-hover mb-0 d-block d-lg-table table-responsive">
	<thead>
		<tr>
			<th class="text-end <?php echo $hlp->Orden(0); ?>" data-field="0">ID</th>
			<th class="<?php echo $hlp->Orden(2); ?>" data-field="2">Fecha Solic.</th>
			<th class="<?php echo $hlp->Orden(3); ?>" data-field="3">Nombre y apellido.</th>
			<th class="<?php echo $hlp->Orden(4); ?>" data-field="4">Nro. Doc.</th>
			<th class="text-end">Total</th>
			<th>Estado</th>
			<th>Fecha Venc.</th>
			<th class="text-end">Días mora</th>
			<th></th>
		</tr>
	</thead>
	<tbody>
	<?php
		if(!CanUseArray($listadoPrestamo)){ ?>
				<tr>
					<td colspan="8" class="text-center">Sin préstamos</td>
				</tr>
	<?php }else{ 
			foreach($listadoPrestamo as $value){
					$id = $value->id;
					$emision = $value->fechahora_emision_txtshort;
					$estado = $value->estado;
					$vencimiento = $value->fecha_vencimiento;
					$dias_mora = cFechas::DiferenciaEntreFechas($vencimiento, cFechas::Ahora());
					$vencimiento = (cFechas::LooksLikeISODateTime($vencimiento))? cFechas::ExtractSQLDate($vencimiento):$vencimiento;
					$vencimiento = cFechas::IsoToLatin($vencimiento);
					$total = ($value->capital ?? 0)+($value->total_intereses ?? 0)+($value->total_cargos ?? 0)+($value->total_impuestos ?? 0);
					$total = F($total);
					$estado_color = ESTADOS_COLORES[$estado] ?? ESTADOS_COLORES['ANUL'];
					$estado = ESTADOS_PRESTAMOS[$estado] ?? "Desconocido";
				?>
				<tr ondblclick="VerPrestamo(<?php echo $id; ?>);">
					<td class="text-end"><?php echo $id; ?></td>
					<td><?php echo $emision; ?></td>
					<td><?php echo $value->apellido.", ".$value->nombre; ?></td>
					<td><?php echo $value->nro_doc; ?></td>
					<td class="text-end"><?php echo "$".$total ?? ""; ?></td>
					<td>
						<div class="rounded-3 d-flex justify-content-center align-items-center" style="background-color:<?php echo $estado_color; ?>">
							<span class="text-white fw-bold"><?php echo $estado; ?></span>
						</div>
					</td>
					<td><?php echo $vencimiento ?? ""; ?></td>
					<td class="text-end"><?php echo $dias_mora ?? ""; ?></td>
					<td>
						<button class="btn btn-primary" type="button" onclick="VerPrestamo(<?php echo $id; ?>)"><i class="fa fa-eye"></i></button>
					</td>
				</tr>
		<?php } ?>
	<?php } ?>
	</tbody>
</table>

<div class="card-footer">
	<?php
	$hlp->ListadoMgr = "prestamosCliente";
	$hlp->Footer();
	?>
</div>