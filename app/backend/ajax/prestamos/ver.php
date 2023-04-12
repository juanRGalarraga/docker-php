<?php
/**
 * Visualiza los detalles del préstamo
 * Created: 2021-11-08
 * Author: Gastón Fernandez
 */
	require_once(DIR_model."prestamos".DS."class.prestamos.inc.php");
	$prestamos = new cPrestamos;
	$prestamo_id = SecureInt(FindParam("id,prestamo_id,pid"));
	$cliente_id = null;
	if(!is_null($prestamo_id)){
		if($prestamo_data = $prestamos->Get($prestamo_id)){
			$cliente_id = $prestamo_data->persona_id ?? null;
		}
	}
	ResponseOk(null,true);
	
	$data = $prestamo_data->data ?? array();
	$nota = null;
	if(CanUseArray($data)){ 
		foreach($data as $value){
			if(isset($value->nota)){ $nota = trim($value->nota); break; }
		}
	}
?>

<div class="row">
	<div class="col-12">
		<div class="card">
			<div class="card-header">
				<div class="card-title">
					<div class="row">
						<div class="col-2 text-start">
							<button data-bs-target="#carrListado" data-bs-slide-to="0" aria-label="Slide 2" class="btn btn-primary" title="Volver al listado"><i class="fa fa-arrow-left" aria-hidden="true"></i></button>
						</div>
					</div>
				</div>
			</div>
			<div class="card-body">
				<?php
					if(is_null($prestamo_id)){
						cLogging::Write(__FILE__." ".__LINE__." El ID del préstamo no es un número");
						return cSideKick::ShowWarning("No se pueden visualizar los datos del préstamo en este momento.");
					}
					if(!CanUseArray($prestamo_data) AND !is_object($prestamo_data)){
						cLogging::Write(__FILE__." ".__LINE__." No se pudieron obtener datos del préstamo con ID ".$prestamo_id);
						return cSideKick::ShowWarning("No se pueden visualizar los datos del préstamo en este momento.");
					}
				?>
				<div class="card border-0 gap-3">
					<div class="card-header">
						<ul class="nav nav-tabs card-header-tabs" role="tablist">
							<li class="nav-item">
								<a class="nav-link active" id="detalles-tab" data-bs-toggle="tab" href="#detalles" role="tab" aria-controls="detalles" aria-selected="true">Detalles del préstamo</a>
							</li>
							<li class="nav-item">
								<a class="nav-link" id="cobros-tab" data-bs-toggle="tab" href="#cobros" role="tab" aria-controls="cobros" aria-selected="false">Cobros</a>
							</li>
							<li class="nav-item">
								<a class="nav-link" id="historial-tab" data-bs-toggle="tab" href="#historial" role="tab" aria-controls="historial" aria-selected="false">Historial</a>
							</li>
							<li class="nav-item">
								<a class="nav-link" id="ajustes-tab" data-bs-toggle="tab" href="#ajustes" role="tab" aria-controls="ajustes" aria-selected="false">Ajustes</a>
							</li>
						</ul>
					</div>
					<div class="card-body">
						<div class="tab-content">
							<div class="tab-pane fade show active" id="detalles" role="tabpanel" aria-labelledby="detalles-tab">
								<?php include("ver".DS."detalles_prestamo.php"); ?>
							</div>
							<div class="tab-pane fade" id="cobros" role="tabpanel" aria-labelledby="cobros-tab">
								<?php include("ver".DS."cobros.php"); ?>
							</div>
							<div class="tab-pane fade" id="historial" role="tabpanel" aria-labelledby="historial-tab">
							</div>
							<div class="tab-pane fade" id="ajustes" role="tabpanel" aria-labelledby="ajustes-tab">
								<?php include("ver".DS."ajustes.php"); ?>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="card-footer">
				<div class="row">
					<div class="col-12 text-end">
						<a href="<?php echo BASE_URL."gestion/".$prestamo_id; ?>" class="btn btn-primary" title="Ir a la gestión de este préstamo">Gestionar...</a>
					</div>
				</div>
				<p><b><?php echo $nota; ?></b></p>
			</div>
		</div>
	</div>
</div>