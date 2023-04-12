<?php
/**
 * Muestra los cobros de un préstamo
 * Created: 2021-11-08
 * Author: Gastón Fernandez
 */
	require_once(DIR_model."cobros".DS."class.cobros.inc.php");
	require_once(DIR_model."brokers".DS."class.brokers.inc.php");
	$cobros = new cCobros;
	$brokers = new cBrokers;

	$cobrosData = $cobros->GetByPrestamo($prestamo_id);
	if(!CanUseArray($cobrosData)){ 
		cLogging::Write(__FILE__." ".__LINE__." No se encontraron cobros para el préstamo ".$prestamo_id);
		return cSideKick::ShowWarning("El préstamo no tiene cobros registrados");
	}
?>
<div class="card">
	<div class="card-body">
		<div class="row">
			<div class="col-12">
				<table class="table table-striped table-borderless d-block d-lg-table table-responsive">
					<thead>
						<tr>
							<th>Fecha de cobro</th>
							<th>Estado</th>
							<th>Medio de pago</th>
							<th>Tipo</th>
							<th class="text-end">Monto</th>
							<th class="text-end">Comprobante</th>
						</tr>
					</thead>
					<tbody>
					<?php
						if(!CanUseArray($cobrosData)){ ?>
								<tr>
									<td colspan="11" class="text-center">Préstamo sin cobros</td>
								</tr>
					<?php }else{ 
							foreach($cobrosData as $value){
									$fecha = $value->fecha_de_cobro_txtshort ?? null;
									$estado = $value->estado ?? null;
									$estado_color = ESTADOS_COLORES[$estado] ?? ESTADOS_COLORES['ANUL'];
									$estado = ESTADOS_COBROS[$estado] ?? "Desconocido";
									$medio = SecureInt($value->broker_id ?? null);
									$tipo = $value->data->tipo_pago ?? "No indicadó";
									$monto = $value->cur_monto ?? null;
									$comprobante = $value->archivo_id ?? null;

									if(!is_null($medio)){
										if($brokerData = $brokers->Get($medio)){
											$medio = $brokerData->nombre;
										}
									}
								?>
								<tr ondblclick="VerComprobante(<?php echo $comprobante ?>);">
									<td><?php echo $fecha; ?></td>
									<td>
										<div class="rounded-3 d-flex justify-content-center align-items-center" style="background-color:<?php echo $estado_color; ?>">
											<span class="text-white fw-bold"><?php echo $estado; ?></span>
										</div>
									</td>
									<td><?php echo $medio; ?></td>
									<td><?php echo $tipo; ?></td>
									<td class="text-end"><?php echo $monto; ?></td>
									<td class="text-end" title="Ver comprobante" onclick="VerComprobante(<?php echo $comprobante ?>);">
									<?php 
										if($comprobante){ ?>
										<button class="btn" type="button"><i class="fas fa-eye"></i></button>
									<?php } else{ ?>
										<span>Sin comprobante</span>
									<?php } ?>
									</td>
								</tr>
						<?php } ?>
					<?php } ?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>