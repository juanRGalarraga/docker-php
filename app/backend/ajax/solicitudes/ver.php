<?php 

    require_once(DIR_model."solicitudes".DS."class.solicitudes.inc.php");
    
    $solicitudes = new cSolicitudes;
    $post = CleanArray($_POST);
    // Showvar($post);

	$incompleto = '<em> Dato Faltante </em>';

    if(!$solicitud_id = SecureInt($post['id'])){ 
        echo '<p class="text-center"> No se encontro la solicitud </p>';
    }

    if(!$data_solicitud = $solicitudes->Get($solicitud_id)){
        echo '<p class="text-center"> No se encontro la solicitud </p>';
    }
    // Showvar($data_solicitud);
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
				<?php
					if(is_null($solicitud_id)){
						cLogging::Write(__FILE__." ".__LINE__." El ID del préstamo no es un número");
						return cSideKick::ShowWarning("No se pueden visualizar los datos del préstamo en este momento.");
					}
					if(!CanUseArray($data_solicitud) AND !is_object($data_solicitud)){
						cLogging::Write(__FILE__." ".__LINE__." No se pudieron obtener datos del préstamo con ID ".$solicitud_id);
						return cSideKick::ShowWarning("No se pueden visualizar los datos del préstamo en este momento.");
					}
				?>
				<div class="card border-0 gap-3">
					<div class="card-header">
						<ul class="nav nav-tabs card-header-tabs" role="tablist">
							<li class="nav-item">
								<a class="nav-link active" id="detalles-tab" data-bs-toggle="tab" href="#detalles" role="tab" aria-controls="detalles" aria-selected="true">Detalles Solicitud</a>
							</li>
							<li class="nav-item">
								<a class="nav-link" id="simulador-tab" data-bs-toggle="tab" href="#simulador" role="tab" aria-controls="simulador" aria-selected="false">Simulador</a>
							</li>
							<li class="nav-item">
								<a class="nav-link" id="simulador-tab" data-bs-toggle="tab" href="#log" role="tab" aria-controls="log" aria-selected="false">Bitácora</a>
							</li>
							<li class="nav-item">
								<a class="nav-link" id="archivosAsociados-tab" data-bs-toggle="tab" href="#archivosAsociados" role="tab" aria-controls="archivosAsociados" aria-selected="false">Biblioteca</a>
							</li>
						</ul>
					</div>
					<div class="card-body">
						<div class="tab-content">
							<div class="tab-pane fade show active" id="detalles" role="tabpanel" aria-labelledby="detalles-tab">
                                <?php require_once(DIR_ajax."solicitudes".DS."ver".DS."detalles_solicitud.php"); ?>
							</div>
							<!-- <div class="tab-pane fade" id="cotizacion" role="tabpanel" aria-labelledby="cotizacion-tab">
								
							</div> -->
							<div class="tab-pane fade" id="simulador" role="tabpanel" aria-labelledby="simulador-tab">
                                <div>
                                    <div id="simulador">
										<?php require_once(DIR_ajax."solicitudes".DS."ver".DS."simular.php"); ?>
									</div>
                                </div>
                            </div>
							<div class="tab-pane fade" id="log" role="tabpanel" aria-labelledby="log-tab">
                                <div>
									<h5>Bitácora de eventos</h5>
                                    <div id="log">
										<?php require_once(DIR_ajax."solicitudes".DS."ver".DS."log.php"); ?>

										
									</div>
                                </div>
                            </div>

							<div class="tab-pane fade" id="archivosAsociados" role="tabpanel" aria-labelledby="archivosAsociados-tab">
								<?php include(DIR_ajax."biblioteca".DS."archivos_clientes.php"); ?>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="card-footer"></div>
		</div>
	</div>
</div>

<!-- <json>{"ok":"ok"}</json> -->

<?php 

?>