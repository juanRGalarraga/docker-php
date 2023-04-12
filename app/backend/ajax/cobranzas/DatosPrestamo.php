<?php

$this_file = substr(__FILE__,strlen(DIR_BASE))." ";

$plan = new cPlanes();
$plan->Get($prestamos->theData->plan_id);

$colores_estado = array("CANC"=>"bg-success text-white","PEND"=>"bg-primary text-white");
$cuotas = null;


$total_a_pagar = $prestamos->theData->total_mora +  $prestamos->theData->total_imponible + $prestamos->theData->iva_mora;
$monto_mora = $prestamos->theData->total_mora + $prestamos->theData->iva_mora;

// if($prestamos->theData->tipo_pagos == "mensual"){
// 	$cuotas = $prestamos->theData->GetCuotasPlan($prestamos->theData->id);
// }

$estado = $prestamos->theData->estado ?? null;
$estado_color = ESTADOS_COLORES[$estado] ?? ESTADOS_COLORES['ANUL'];
$estado = ESTADOS_PRESTAMOS[$estado] ?? "Desconocido";
?>
<div class="card">
	<div class="card-body">
		<input type="hidden" name="prestamo_id" id="prestamo_id" value="<?php echo $prestamos->theData->id; ?>" />
		<h4><?php EchoLang('Datos del préstamo'); ?></h4>
		<div class="row">
			<div class="col">
				<div class="card">
					<div class="card-body">
						<p class="text-center fw-bold"><small><?php EchoLang('Total adeudado'); ?></small></p>
						<p class="text-center "><?php echo " $ ".$prestamos->theData->tipo_moneda." ".F($total_a_pagar); ?></p>		
					</div>
				</div>
			</div>
			<div class="col">
				<div class="card">
					<div class="card-body">
						<p class="text-center fw-bold"><small><?php EchoLang('Capital solicitado'); ?></small></p>
						<p class="text-center "><?php echo " $ ".$prestamos->theData->tipo_moneda." ".F($prestamos->theData->capital); ?></p>
					</div>
				</div>
			</div>
			<div class="col">
				<div class="card">
					<div class="card-body">
						<p class="text-center fw-bold"><?php EchoLang('Fecha vencimiento'); ?></p>
						<p class="text-center "><?php
							if ($prestamos->theData->estado == 'CANC') {
						?>
						<strike><?php echo cFechas::SQLDate2Str($prestamos->theData->fecha_vencimiento, CDATE_SHORT+CDATE_IGNORETIME); ?></strike><?php 
						} else {
							echo cFechas::SQLDate2Str($prestamos->theData->fecha_vencimiento, CDATE_SHORT+CDATE_IGNORETIME);
						}?></p>
					</div>
				</div>
			</div>
			<div class="col">
				<div class="card">
					<div class="card-body">
						<p class="text-center fw-bold"><small><?php EchoLang('Monto Mora (Incluye impuestos)'); ?></small></p>
						<p class="text-center "><?php echo " $ ".$prestamos->theData->tipo_moneda." ".$monto_mora; ?></p>
					</div>
				</div>
			</div>
			<div class="col">
				<div class="card" style="background-color:<?php echo $estado_color; ?>">
					<div class="card-body">
						<p class="text-center fw-bold"><?php EchoLang('Estado'); ?></p>
						<p class="text-center "><?php echo $estado; ?></p>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div id="datos-prestamo" class="collapse col-12">
				<div class="row">
					<div class="col-xl-12 d-flex flex-wrap justify-content-between">
						<span class="fill"></span>
						<div class="card flex-fill me-2 card-monto" title="">
							<p class="text-center fw-bold"><small><?php EchoLang('Desembolso'); ?></small></p>
							<p class="text-center "><?php echo " $ ".$prestamos->theData->tipo_moneda." ".F($prestamos->theData->capital); ?></p>
						</div>
						<span class="fill"></span>
						<div class="card flex-fill me-2 card-monto" title="">
							<p class="text-center fw-bold"><small><?php EchoLang('Interés'); ?></small></p>
							<p class="text-center "><?php echo " $ ".$prestamos->theData->tipo_moneda." ".F($prestamos->theData->total_intereses); ?></p>
						</div>
						<span class="fill"></span>
						<div class="card flex-fill me-2 card-monto" title="">
							<p class="text-center fw-bold"><small><?php EchoLang('Impuestos'); ?></small></p>
							<p class="text-center "><?php echo " $ ".$prestamos->theData->tipo_moneda." ".F($prestamos->theData->total_impuestos+$prestamos->theData->total_iva_interes); ?></p>
						</div>
						<span class="fill"></span>
						<div class="card flex-fill me-2 card-monto" title="">
							<p class="text-center fw-bold"><small><?php EchoLang('Cargos'); ?></small></p>
							<p class="text-center "><?php echo " $ ".$prestamos->theData->tipo_moneda." " .F($prestamos->theData->total_cargos); ?></p>
						</div>
						<span class="fill"></span>
						<div class="card flex-fill me-2 card-monto" title="">
							<p class="text-center fw-bold"><small><?php EchoLang('Total'); ?></small></p>
							<p class="text-center "><?php echo " $ ".$prestamos->theData->tipo_moneda." ".F($prestamos->theData->total_imponible); ?></p>
						</div>
						<span class="fill"></span>
					</div>
				</div>
				<div class="row">
					<div class="col-4">
						<div class="card" title="<?php echo cFechas::SQLDate2Str($prestamos->theData->fechahora_emision).". Hace ".cFechas::TiempoTranscurrido($prestamos->theData->fechahora_emision, Date('Y-m-d'), false); ?>">
							<p class="text-center fw-bold"><?php EchoLang('Fecha solicitado'); ?></p>
							<p class="text-center "><?php echo cFechas::SQLDate2Str($prestamos->theData->fechahora_emision, CDATE_SHORT+CDATE_IGNORETIME); ?></p>
						</div>
					</div>
					<div class="col-4">
						<div class="card" title="">
							<p class="text-center fw-bold"><?php EchoLang('Plazo'); ?></p>
							<p class="text-center "><?php echo $prestamos->theData->periodo; ?></p>
						</div>
					</div>
					<div class="col-4">
						<div class="card" title="">
							<p class="text-center fw-bold"><?php EchoLang('TNA'); ?></p>
							<p class="text-center "><?php echo (isset($prestamos->theData->tasas->TNA)) ? F($prestamos->theData->tasas->TNA) : ''; ?></p>
						</div>
					</div>
				</div>

				<div class="row">
					<div class="col-4">
						<div class="card" title="">
							<p class="text-center fw-bold">Plan usado</p>
							<p class="text-center font-weight-bold"><?php //echo $prestamos->theData->plan_id;
								if (isset($plan->theData) && isset($plan->theData->nombreComercial)) {
									echo $plan->theData->nombreComercial;
								} else {
									$prestamos->theData->plan_id;
								}
							?></p>
						</div>
					</div>
					<div class="col-4">
						<div class="card" title="">
							<p class="text-center fw-bold"><?php EchoLang('Días de mora'); ?></p>
							<p class="text-center font-weight-bold"><?php
							$vencimiento = $prestamos->theData->fecha_vencimiento;
							$dias_vencido = "-";
							if((cFechas::LooksLikeISODate($vencimiento) OR cFechas::LooksLikeISODateTime($vencimiento)) OR $vencimiento < cFechas::Ahora()){
								$dias_vencido = cFechas::DiferenciaEntreFechas($vencimiento, cFechas::Ahora());
							}
							if ($prestamos->theData->estado == 'CANC') {
									echo '<strike>'.$dias_vencido.'</strike>';
							} else {
								echo $dias_vencido;
								
							}?></p><?php
							if ($prestamos->theData->estado == 'MORA') {
								echo '<p class="text-center"><small>Hace '.cFechas::TiempoTranscurrido($prestamos->theData->fecha_vencimiento, cFechas::Ahora(),false).'</small></p>';
							}
							?>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="row d-flex justify-content-end">
			<?php
			if (isset($prestamos->theData) && isset($prestamos->theData->estado)) {
				if (($prestamos->theData->estado == 'MORA') and (!in_array(@$reg['alias'], array('NOCONTACT', 'CERRADO')))) {
					if ($objeto_contenido->puede_crear) { 	?>
		
						<div class="col text-left"><button type="button" class=" btn bg-gradient-primary" onClick="Refinanciar();" title="Refinanciar el préstamo en mora">Refinanciar...</button></div>
			<?php
					}
				}
			}
			?>
			<button class="btn bg-outline-primary text-right" href="#datos-prestamo" data-bs-toggle="collapse" aria-expanded="false" aria-controls="datos-prestamo" onclick="Giro(this, true);">
				<i class="fas fa-plus-circle text-primary"></i>
			</button>
		</div>
	</div>
</div>

