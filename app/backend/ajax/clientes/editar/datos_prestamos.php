<?php
/**
 * Datos de los préstamos de un cliente
 * Created: 2021-11-04
 * Author: Gastón Fernandez
 */
	require_once(DIR_model."prestamos".DS."class.prestamos.inc.php");
	require_once(DIR_includes."class.listhelper.inc.php");
	$prestamos = new cPrestamos;
	$params = array();
	if(!isset($persona_id)){
		$persona_id = FindParam("id,persona_id");
		$pag = FindParam("pag");
		$pag = ($pag > 0)? $pag:1;
		$params["pag"] = $pag;
	}
	$params["persona_id"] = $persona_id;
	$listadoPrestamo = $prestamos->GetListado($params);
	$hlp = new ListHelper;
	$hlp->SetPropeties($prestamos);
?>
<div class="card">
	<div class="card-body">
		<div class="row">
			<div class="col-12">
				<table class="tabla-general table table-striped table-hover mb-0 d-block d-md-table table-responsive">
					<thead>
						<tr>
							<th class="text-end">ID</th>
							<th>Fecha Solic.</th>
							<th>Total</th>
							<th>Estado</th>
							<th>Fecha Venc.</th>
							<th>Días mora</th>
							<th></th>
						</tr>
					</thead>
					<tbody>
					<?php
						if(!CanUseArray($listadoPrestamo)){ ?>
								<tr>
									<td colspan="7" class="text-center">Sin préstamos</td>
								</tr>
					<?php }else{ 
							foreach($listadoPrestamo as $value){
									$id = $value->id;
									$emision = $value->fechahora_emision_txtshort;
									$estado = $value->estado;
									$vencimiento = $value->fecha_vencimiento;
									$dias_mora = ($vencimiento < cFechas::Ahora())? cFechas::DiferenciaEntreFechas($vencimiento, cFechas::Ahora()):"-";
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
									<td><?php echo "$".$total ?? ""; ?></td>
									<td>
										<div class="rounded-3 d-flex justify-content-center align-items-center" style="background-color:<?php echo $estado_color; ?>">
											<span class="text-white fw-bold"><?php echo $estado; ?></span>
										</div>
									</td>
									<td><?php echo $vencimiento ?? ""; ?></td>
									<td><?php echo $dias_mora ?? ""; ?></td>
									<td>
										<button class="btn btn-primary btn-sm" type="button" onclick="VerPrestamo(<?php echo $id; ?>)"><i class="fa fa-eye"></i></button>
									</td>
								</tr>
						<?php } ?>
					<?php } ?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
	<div class="card-footer border-top-0">
		<?php
		$hlp->ListadoMgr = "prestamosCliente";
		$hlp->ListadoMgrExtraParam = "{'persona_id':$persona_id}";
		$hlp->Footer();
		?>
	</div>
</div>