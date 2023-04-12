<?php
/**
 * Tabla con los detalles del préstamo
 * Created: 2021-11-04
 * Author: Gastón Fernandez
 */
	require_once(DIR_model."prestamos".DS."class.prestamos.inc.php");
	require_once(DIR_model."planes".DS."class.planes.inc.php");
	$prestamos = new cPrestamos;
	$planes = new cPlanes;

	$prestamo_id = SecureInt(FindParam("id,pid,prestamo_id"));
	if(is_null($prestamo_id)){
		cLogging::Write(__FILE__." ".__LINE__." El ID del préstamo no es un número");
		return cSideKick::ShowWarning("No se pudieron obtener los detalles de este préstamo");
	}

	if(!isset($data)){//Si ya realize una petición para obtener los datos del préstamo antes, no lo vuelvo a hacer
		if(!$data = $prestamos->Get($prestamo_id)){
			cLogging::Write(__FILE__." ".__LINE__." El préstamo con ID ".$prestamo_id." no fue encontrado");
			return cSideKick::ShowWarning("No se pudieron obtener los detalles de este préstamo");
		}
	}

	$capital = $data->cur_capital ?? 0;
	$interes_capital = $data->cur_interes_capital ?? 0;
	$cargos = $data->cur_total_cargos ?? 0;
	$impuestos = ($data->total_impuestos ?? 0)-($data->iva_mora ?? 0);//Cuz no me interesa tener el IVA de la mora en el original
	$mora = ($data->total_mora ?? 0)+($data->iva_mora ?? 0);
	$impuestos = "$".F($impuestos);
	$mora = "$".F($mora);

	$total_adeudado = ($data->capital ?? 0)+($data->total_intereses ?? 0)+($data->total_cargos ?? 0)+($data->total_impuestos ?? 0);
	$total = $total_adeudado-(($data->total_mora ?? 0)+($data->iva_mora ?? 0));
	$total_adeudado = "$".F($total_adeudado);
	$total = "$".F($total);
	$total_pagado = $data->cur_total_pagado ?? 0;

	$estado = $data->estado ?? "ANUL";
	$color = ESTADOS_COLORES[$estado] ?? ESTADOS_COLORES["ANUL"];
	$estado = ESTADOS_PRESTAMOS[$estado] ?? "Desconocido";

	$fecha_solic = $data->fechahora_emision_txtshort ?? "Desconocida";
	$plazo = $data->periodo;
	$vencimiento = $data->fecha_vencimiento ?? "Desconocido";
	$dias_mora = "-";
	if(cFechas::LooksLikeISODate($vencimiento) OR cFechas::LooksLikeISODateTime($vencimiento)){
		$vencimiento = (cFechas::LooksLikeISODateTime($vencimiento))? cFechas::ExtractSQLDate($vencimiento):$vencimiento;

		if($vencimiento < cFechas::Hoy()){
			$dias_mora = cFechas::DiferenciaEntreFechas($vencimiento,cFechas::Hoy());
		}
		$vencimiento = cFechas::IsoToLatin($vencimiento);
	}

	$plan_id = $data->plan_id ?? null;
	$plan = "Desconocido";
	if(!is_null(SecureInt($plan_id))){
		if($dataPlan = $planes->Get($plan_id)){
			$plan = $dataPlan->nombreComercial ?? $plan;
		}
	}
	
	$tna = $data->tasas->tna ?? $data->tasas->TNA ?? "-";
	if(!is_null(SecureFloat($tna))){
		$tna = number_format($tna, 2, ",","")."%";
	}

	$porc_iva = $data->tasas_impuestos->iva_insc ?? 21;
	if(!is_null(SecureFloat($porc_iva))){
		$porc_iva = number_format($porc_iva, 2, ",","")."%";
	}

	$ted = $data->tasas->ted ?? $data->tasas->TED ?? "-";
	if(!is_null(SecureFloat($ted))){
		$ted = number_format($ted, 2, ",","")."%";
	}

	$tem = $data->tasas->tem ?? $data->tasas->TEM ?? "-";
	if(!is_null(SecureFloat($tem))){
		$tem = number_format($tem, 2, ",","")."%";
	}

	$tnm = $data->tasas->tnm ?? $data->tasas->TNM ?? "-";
	if(!is_null(SecureFloat($tnm))){
		$tnm = number_format($tnm, 2, ",","")."%";
	}

	$cft = $data->tasas->cft ?? $data->tasas->CFT ?? "-";
	if(!is_null(SecureFloat($cft))){
		$cft = number_format($cft, 2, ",","")."%";
	}

	$tipo_moneda = $data->tipo_moneda ?? "ARS";
	$factura = ($data->facturado ?? false)? "Si":"No";
	$transferido = (!empty($data->data_transferencia ?? ""))? "Si":"No";
?>

<div class="row row-cols-1 row-cols-sm-2 row-cols-md-3">
	<div class="col">
		<div class="card">
			<ul class="list-group">
				<!-- Capital -->
				<li class="list-group-item list-group-item-action">
						<span>Capital solicitado:</span>
						<span><?php echo $capital; ?></span>
				</li>

				<!-- Interés capital -->
				<li class="list-group-item list-group-item-action">
						<span>Intereses:</span>
						<span><?php echo $interes_capital; ?></span>
				</li>

				<!-- Cargos -->
				<li class="list-group-item list-group-item-action">
						<span>Cargos:</span>
						<span><?php echo $cargos; ?></span>
				</li>

				<!-- Impuestos -->
				<li class="list-group-item list-group-item-action" style="border-bottom: 2px solid rgba(0,0,0,0.5);">
						<span>Impuestos:</span>
						<span><?php echo $impuestos; ?></span>
				</li>

				<!-- Total -->
				<li class="list-group-item list-group-item-action">
						<span>Total del préstamo:</span>
						<div class="fw-bold text-white rounded-3 bg-secondary px-1 d-flex justify-content-center align-items-center">
							<?php echo $total; ?>
						</div>
				</li>

				<!-- Mora -->
				<li class="list-group-item list-group-item-action">
						<span>Monto mora:</span>
						<span><?php echo $mora; ?></span>
				</li>

				<!-- Mora -->
				<li class="list-group-item list-group-item-action">
						<span>Total pagado:</span>
						<span><?php echo $total_pagado; ?></span>
				</li>

				<!-- Total adeudado -->
				<li class="list-group-item list-group-item-action bg-secondary text-white">
						<span>Total adeudado:</span>
						<span><?php echo $total_adeudado; ?></span>
				</li>
			</ul>
		</div>
	</div>

	<div class="col">
		<div class="card">
			<ul class="list-group">
				<!-- Estado -->
				<li class="list-group-item list-group-item-action" style="background-color: <?php echo $color; ?>">
					<span>Estado:</span>
					<span><?php echo $estado; ?></span>
				</li>

				<!-- Desembolso -->
				<li class="list-group-item list-group-item-action">
					<span>Desembolso:</span>
					<span><?php echo $capital; ?></span>
				</li>

				<!-- Fecha solicitado -->
				<li class="list-group-item list-group-item-action">
					<span>Fecha solicitado:</span>
					<span><?php echo $fecha_solic; ?></span>
				</li>

				<!-- Plazo/periodo -->
				<li class="list-group-item list-group-item-action">
					<span>Plazo:</span>
					<span><?php echo $plazo; ?></span>
				</li>

				<!-- Fecha de vencimiento -->
				<li class="list-group-item list-group-item-action">
					<span>Vencimiento:</span>
					<span><?php echo $vencimiento; ?></span>
				</li>

				<!-- Días de mora -->
				<li class="list-group-item list-group-item-action">
					<span>Días de mora:</span>
					<span><?php echo $dias_mora; ?></span>
				</li>

				<!-- Plan -->
				<li class="list-group-item list-group-item-action">
					<span>Plan usado:</span>
					<span><?php echo $plan; ?></span>
				</li>

				<!-- Tipo moneda -->
				<li class="list-group-item list-group-item-action">
					<span>Tipo moneda:</span>
					<span><?php echo $tipo_moneda; ?></span>
				</li>
			</ul>
		</div>
	</div>

	<div class="col">
		<div class="card">
			<ul class="list-group">
			  <!-- Porc IVA -->
				<li class="list-group-item list-group-item-action">
					<span>Porc IVA:</span>
					<span><?php echo $porc_iva; ?></span>
				</li>

				<!-- TNA -->
				<li class="list-group-item list-group-item-action">
					<span>TNA:</span>
					<span><?php echo $tna; ?></span>
				</li>

				<!-- TED -->
				<li class="list-group-item list-group-item-action">
					<span>TED:</span>
					<span><?php echo $ted; ?></span>
				</li>

				<!-- TEM -->
				<li class="list-group-item list-group-item-action">
					<span>TEM:</span>
					<span><?php echo $tem; ?></span>
				</li>

				<!-- tnm -->
				<li class="list-group-item list-group-item-action">
					<span>TNM:</span>
					<span><?php echo $tnm; ?></span>
				</li>

				<!-- CFT -->
				<li class="list-group-item list-group-item-action">
					<span>CFT:</span>
					<span><?php echo $cft; ?></span>
				</li>

				<!-- Factura -->
				<li class="list-group-item list-group-item-action">
					<span>Factura emitida:</span>
					<span><?php echo $factura; ?></span>
				</li>

				<!-- Transferido -->
				<li class="list-group-item list-group-item-action">
					<span>Transferencia realizada:</span>
					<span><?php echo $transferido; ?></span>
				</li>
			</ul>
		</div>
	</div>
</div>

<style>
	div.card .list-group .list-group-item :last-child {
		font-weight: bold;
	}

	div.card .list-group .list-group-item{
		display: flex;
		justify-content: space-between;
	}
</style>