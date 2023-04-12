<?php
/**
 * Formulario de edición de clientes
 * Created: 2021-11-03
 * Author: Gastón Fernandez
 */

	require_once(DIR_model."clientes".DS."class.clientes.inc.php");
	require_once(DIR_model."usuarios".DS."class.usuarios.inc.php");
	$clientes = new cClientes;
	$usuarios = new cUsuarios;

	$persona_id = SecureInt(FindParam("id"));
	if(is_null($persona_id)){
		cLogging::Write(__FILE__." ".__LINE__." El ID del cliente no es válido");
		return EmitJSON("No se puede editar el cliente en este momento");
	}

	if(!$data = $clientes->Get($persona_id)){
		cLogging::Write(__FILE__." ".__LINE__." El cliente con ID ".$persona_id." no pudo ser encontrado");
		return EmitJSON("No se puede editar el cliente en este momento");
	}

	$fecha_modif = $data->FechaModif ?? "Nunca";
	if(cFechas::LooksLikeISODate($fecha_modif) OR cFechas::LooksLikeISODateTime($fecha_modif)){
		$fecha_modif = cFechas::SQLDate2Str($fecha_modif,CDATE_LONG);
	}

	$userModif = $data->UsuarioId ?? "Nadie";
	if(!is_null(SecureInt($userModif))){
		if($name = $usuarios->GetCompleteName($userModif)){
			$userModif = $name;
		}
	}
	ResponseOk(null,true);
?>
<div class="row">
	<div class="col-12">
		<div class="card">
			<div class="card-header">
				<div class="card-title">
					<div class="row">
						<div class="col-12 text-end">
							<button data-bs-target="#carrListado" data-bs-slide-to="0" aria-label="Slide 2" class="btn btn-primary btn-sm" title="Volver al listado"><i class="fa fa-arrow-left" aria-hidden="true"></i></button>
						</div>
					</div>
				</div>
			</div>
			<div class="card-body">
				<div class="card border-0 gap-3">
					<div class="card-header">
						<ul class="nav nav-tabs card-header-tabs" role="tablist">
							<li class="nav-item" role="presentation">
								<a class="nav-link active" id="personal-tab" data-bs-toggle="tab" href="#personal" role="tab" aria-controls="personal" aria-selected="true">Datos personales</a>
							</li>
							<li class="nav-item" role="presentation">
								<a class="nav-link" id="contact-tab" data-bs-toggle="tab" href="#contact" role="tab" aria-controls="contact" aria-selected="false">Datos contacto</a>
							</li>
							<li class="nav-item">
								<a class="nav-link" id="bank-tab" data-bs-toggle="tab" href="#bank" role="tab" aria-controls="bank" aria-selected="false">Datos bancarios</a>
							</li>
							<li class="nav-item">
								<a class="nav-link" id="prestamo-tab" data-bs-toggle="tab" href="#listadoPrestamosCliente" role="tab" aria-controls="listadoPrestamosCliente" aria-selected="false">Datos Préstamos</a>
							</li>
							<!-- Seguimiento customer -->
							<li class="nav-item">
								<a class="nav-link" id="seguimientoCustomer-tab" data-bs-toggle="tab" href="#seguimientoCustomer" role="tab" aria-controls="seguimientoCustomer" aria-selected="false">Seguimiento Customer</a>
							</li>

							<!-- Seguimiento cobranza -->
							<li class="nav-item">
								<a class="nav-link" id="seguimientoCobros-tab" data-bs-toggle="tab" href="#seguimientoCobros" role="tab" aria-controls="seguimientoCobros" aria-selected="false">Seguimiento de cobranza</a>
							</li>

							<!-- Datos complementarios -->
							<li class="nav-item">
								<a class="nav-link" id="solicitudComplementarios-tab" data-bs-toggle="tab" href="#solicitudComplementarios" role="tab" aria-controls="solicitudComplementarios" aria-selected="false">Datos complementarios de la solicitud</a>
							</li>

							<!-- Archivos asociados -->
							<li class="nav-item">
								<a class="nav-link" id="archivosAsociados-tab" data-bs-toggle="tab" href="#archivosAsociados" role="tab" aria-controls="archivosAsociados" aria-selected="false">Archivos asociados</a>
							</li>
						</ul>
					</div>
					<div class="card-body">
						<div class="tab-content" id="myTabContent">
							<div class="tab-pane fade show active" id="personal" role="tabpanel" aria-labelledby="personal-tab">
								<?php include("editar".DS."datos_personales.php"); ?>
							</div>
							<div class="tab-pane fade" id="contact" role="tabpanel" aria-labelledby="contact-tab">
								<?php include("editar".DS."datos_contacto.php"); ?>
							</div>
							<div class="tab-pane fade" id="bank" role="tabpanel" aria-labelledby="bank-tab">
								<?php include("editar".DS."datos_bancarios.php"); ?>
							</div>
							<div class="tab-pane fade" id="listadoPrestamosCliente" role="tabpanel" aria-labelledby="prestamo-tab">
								<?php include("editar".DS."datos_prestamos.php"); ?>
							</div>

							<div class="tab-pane fade" id="seguimientoCustomer" role="tabpanel" aria-labelledby="seguimientoCustomer-tab">
								<?php include("editar".DS."seguimiento_customer.php"); ?>
							</div>

							<div class="tab-pane fade" id="seguimientoCobros" role="tabpanel" aria-labelledby="seguimientoCobros-tab">
								<?php include("editar".DS."seguimiento_cobranzas.php"); ?>
							</div>

							<div class="tab-pane fade" id="solicitudComplementarios" role="tabpanel" aria-labelledby="solicitudComplementarios-tab">
								<?php include("editar".DS."solicitud_complementarios.php"); ?>
							</div>

							<div class="tab-pane fade" id="archivosAsociados" role="tabpanel" aria-labelledby="archivosAsociados-tab">
								<?php include(DIR_ajax."biblioteca".DS."archivos_clientes.php"); ?>
							</div>
							<p class="text-start d-flex justify-content-between">
								<small>
									Última modificacion: <b><?php echo $fecha_modif; ?></b>
								</small>
								<small>
									Por: <b><?php echo $userModif; ?></b>
								</small>
							</p>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>