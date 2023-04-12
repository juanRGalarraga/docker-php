<?php
/**
 * Listado del historial de un préstamo
 * Created: 2021-11-08
 * Author: Gastón Fernandez
 */
	require_once(DIR_model."prestamos".DS."class.prestamos_hist.inc.php");
	require_once(DIR_includes."class.listhelper.inc.php");
	$hist = new cPrestamosHist;
	$params = array();
	if(!isset($prestamo_id)){
		$prestamo_id = FindParam("id,pid,prestamo_id");
		$pag = FindParam("pag");
		$pag = ($pag > 0)? $pag:1;
		$params["pag"] = $pag;

		$lid = FindParam("lid");
		if($lid){
			$params["listid"] = $lid;
			
			//Intentamos porcesar el ordenamiento de la tabla...
			$orden = SecureInt(FindParam("ord"));
			if(!is_null($orden)){
				if($ordenamiento = $hist->SetOrden($lid,$orden)){
					$params["orden"] = $ordenamiento;
				}
			}
		}
	}
	$params["id"] = $prestamo_id;

	$histPrestamo = $hist->ListarHistorial($prestamo_id,$params);
	$lid = $hist->listid ?? null;

	$hlp = new ListHelper;
	$hlp->SetPropeties(clone $hist);
	$hlp->ListadoMgr = "historialPrestamo";
	$hlp->ListadoMgrExtraParam = "{'prestamo_id':$prestamo_id}";
?>
<span class="lid" id="lid" hidden><?php echo $lid; ?></span>
<div class="card">
	<div class="card-body">
		<div class="row">
			<div class="col-12">
				<table class="tabla-general table table-striped table-hover mb-0 d-block d-xxl-table table-responsive">
					<thead>
						<tr>
							<th class="<?php echo $hlp->orden(1); ?>" data-field="1">Fecha y hora</th>
							<th class="text-end">Cobro acreditado</th>
							<th>Observacion</th>
							<th>Estado</th>
							<th class="text-end">Capital restante</th>
							<th class="text-end">Interés* capital</th>
							<th class="text-end">Interés punitorio</th>
							<th class="text-end">IVA Int. punitorio</th>
							<th class="text-end">Cargos*</th>
							<th class="text-end">Total pagado</th>
							<th class="text-end">Total a pagar</th>
						</tr>
					</thead>
					<tbody>
					<?php
						if(!CanUseArray($histPrestamo)){ ?>
								<tr>
									<td colspan="11" class="text-center">Préstamo sin historial</td>
								</tr>
					<?php }else{ 
							foreach($histPrestamo as $value){
									$fechahora = $value->fechahora ?? null;
									$fechahora = (cFechas::LooksLikeISODate($fechahora) OR cFechas::LooksLikeISODateTime($fechahora))? cFechas::SQLDate2Str($fechahora,CDATE_SHORT):$fechahora;
									$acreditado = $value->monto_cobro ?? 0;
									$observacion = $value->observaciones ?? null;
									$estado = $value->estado ?? null;
									$estado_color = (isset(ESTADOS_COLORES[$estado]))? ESTADOS_COLORES[$estado]:ESTADOS_COLORES['ANUL'];
									$estado = (isset(ESTADOS_PRESTAMOS[$estado]))? ESTADOS_PRESTAMOS[$estado]:"Desconocido";
									$capital = $value->capital ?? 0;
									$interes_capital = $value->interes_capital ?? 0;
									$iva_interes = $value->iva_interes_capital ?? 0;
									$interes_capital += $iva_interes;
									$total_mora = $value->total_mora ?? 0;
									$iva_mora = $value->iva_mora ?? 0;
									$total_cargos = $value->total_cargos ?? 0;
									$total_pagado = $value->total_pagado ?? 0;
									$total_a_pagar = $value->total_imponible ?? 0;

									$acreditado = "$".F($acreditado);
									$capital = "$".F($capital);
									$interes_capital = "$".F($interes_capital);
									$total_mora = "$".F($total_mora);
									$iva_mora = "$".F($iva_mora);
									$total_cargos = "$".F($total_cargos);
									$total_pagado = "$".F($total_pagado);
									$total_a_pagar = "$".F($total_a_pagar);
								?>
								<tr>
									<td><?php echo $fechahora; ?></td>
									<td class="text-end"><?php echo $acreditado; ?></td>
									<td><b><?php echo $observacion; ?></b></td>
									<td>
										<div class="rounded-3 d-flex justify-content-center align-items-center" style="background-color:<?php echo $estado_color; ?>">
											<span class="text-white fw-bold"><?php echo $estado; ?></span>
										</div>
									</td>
									<td class="text-end"><?php echo $capital; ?></td>
									<td class="text-end"><?php echo $interes_capital; ?></td>
									<td class="text-end"><?php echo $total_mora; ?></td>
									<td class="text-end"><?php echo $iva_mora; ?></td>
									<td class="text-end"><?php echo $total_cargos; ?></td>
									<td class="text-end"><?php echo $total_pagado; ?></td>
									<td class="text-end"><?php echo $total_a_pagar; ?></td>

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
		$hlp->Footer();
	?>
	</div>
</div>