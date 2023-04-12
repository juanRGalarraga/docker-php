<?php
/**
 * Visualización de los datos de una persona para el préstamo
 * Created: 2021-11-08
 * Author: Gastón Fernandez
 */
	require_once(DIR_model."clientes".DS."class.clientes.inc.php");
	require_once(DIR_model."clientes".DS."class.clientesData.inc.php");
	$clientes = new cClientes;
	$clientesData = new cClientesData;
	$cbu = "-";
	if(!isset($cliente_id) OR is_null($cliente_id)){
		cLogging::Write(__FILE__." ".__LINE__." El ID del cliente no fue indicadó con antererioridad");
		return cSideKick::ShowWarning("No se pueden visualizar los datos del client en este momento");
	}

	if(!$clientData = $clientes->Get($cliente_id)){
		cLogging::Write(__FILE__." ".__LINE__." El ID del cliente no fue indicadó con antererioridad");
		return cSideKick::ShowWarning("No se pueden visualizar los datos del client en este momento");
	}

	if($listado = $clientesData->GetCuentasBancarias($cliente_id)){
		//Obtiene la cuenta por omisión de una persona
		$cbu = array_shift($listado)->valor;
		foreach($listado as $value){
			if($value->default){ $cbu = $value->valor; break; }
		}
	}

	$faltante = "Dato faltante";
	$nombre = $clientData->Nombre ?? $faltante;
	$apellido = $clientData->Apellido ?? $faltante;
	$nro_doc = $clientData->NroDoc ?? $faltante;
	$fecha_nac = $clientData->FechaNacimiento ?? $faltante;
	$edad = (cFechas::LooksLikeDate($fecha_nac))? cFechas::CalcularEdad($fecha_nac):$faltante;
	$fecha_nac = (cFechas::LooksLikeDate($fecha_nac))? cFechas::SQLDate2Str($fecha_nac):$faltante;
	$email = $clientData->Email ?? $faltante;
	$tel = $clientData->TelefMovil ?? $faltante;
	$cuil = $clientData->Cuil ?? $faltante;
?>

	<div class="card client-data">
		<ul class="list-group list-group-flush border border-start-0 border-end-0">
			<div class="row row-cols-1 row-cols-sm-3">
				<!-- fila 1 -->
				<div class="col">
					<li class="list-group-item">
						<span>Nombre:</span>
						<span><?php echo $nombre; ?></span>
					</li>
				</div>

				<div class="col">
					<li class="list-group-item">
						<span>Apellido:</span>
						<span><?php echo $apellido; ?></span>
					</li>
				</div>

				<div class="col">
					<li class="list-group-item">
						<span>DNI:</span>
						<span><?php echo $nro_doc; ?></span>
					</li>
				</div>

				<!-- Collapse con más datos -->
				<div id="more-data" class="col-12 collapse">
					<!-- Fila 2 -->
					<div class="row row-cols-1 row-cols-sm-3">
						<div class="col">
							<li class="list-group-item border-top">
								<span>Fecha de nacimiento:</span>
								<span><?php echo $fecha_nac; ?></span>
							</li>
						</div>
	
	
						<div class="col">
							<li class="list-group-item border-top">
								<span>Edad:</span>
								<span><?php echo $edad; ?></span>
							</li>
						</div>
	
						<div class="col">
							<li class="list-group-item border-top">
								<span>Email:</span>
								<span><?php echo $email; ?></span>
							</li>
						</div>
					</div>

					<!-- Fila 3 -->
					<div class="row row-cols-1 row-cols-sm-3">
						<div class="col">
							<li class="list-group-item border-top">
								<span>CBU:</span>
								<span><?php echo $cbu; ?></span>
							</li>
						</div>
						<div class="col">
							<li class="list-group-item border-top">
								<span>Teléfono:</span>
								<span><?php echo $tel; ?></span>
							</li>
						</div>
						<div class="col">
							<li class="list-group-item border-top">
								<span>CUIL:</span>
								<span><?php echo $cuil; ?></span>
							</li>
						</div>
					</div>
				</div>
			</div>
		</ul>
		<div class="card-footer">
			<div class="row">
				<div class="col-12 justify-content-between text-end">
					<button class="btn btn-primary collapsed" data-bs-target="#more-data" data-bs-toggle="collapse" aria-expanded="false" aria-controls="more-data" title="Ver más datos" onclick="Giro(this);">
						<i class="fas fa-plus-circle" aria-hidden="true" style="transform: rotate(0deg); transition: all 0.5s ease 0.5s;"></i>
					</button>
				</div>
			</div>
		</div>
	</div>
	<style>
		.card.client-data ul li{
			border: none;
			overflow-x: auto;
		}
	</style>