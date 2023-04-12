<?php


$persona = new cClientes();
// $persona_data = new cPersonasData($prestamos->theData->persona_id);
// ShowVar($prestamos->theData);
$persona = $persona->Get($prestamos->theData->persona_id);

// $extra_data = $persona_data->GetAllData();
$this_file = substr(__FILE__, strlen(DIR_BASE)) . " ";

$oculto_segundo_nombre = null;
$oculto_dni = null;
$oculto_email = null;

?>
<div class="card">
	<div class="card-body">
		<h4>Datos del titular (<?php echo (isset($persona->id)) ? $persona->id : ''; ?>)</h4>
		<div class="row">
			<input type="hidden" name="persona_id" id="persona_id" readonly value="<?php echo (isset($persona->id)) ? $persona->id : ''; ?>">
			<div class="col-12 form-group">
				<div class="row mb-3">
					<!-- <div class="col-2 form-group text-end pt-2">
					</div> -->
					<div class="col-6 col-md-4 form-group">
						<label for="nombre_ape" class="fw-bold"><?php EchoLang('Nombre y Apellido'); ?></label>
						<input type="text" class="form-control" name="nombre_ape" id="nombre_ape" value="<?php echo $objeto_usuario->IsUser() ? @$oculto_segundo_nombre : @$persona->Nombre; echo " ".@$persona->Apellido?>" readonly />
					</div>

					<!-- <div class="col-1 form-group text-end pt-2">
					</div> -->
					<div class="col-6 col-md-4 form-group">
						<label for="tipo_doc" class="fw-bold"><?php echo (isset($persona->TipoDoc)) ? $persona->TipoDoc : ''; ?></label>
						<input type="text" class="form-control" name="nro_doc" id="nro-doc" value="<?php echo $objeto_usuario->IsUser() ? @$oculto_dni : @$persona->NroDoc; ?>" readonly />			
					</div>

					<!-- <div class="col-1 form-group text-end pt-2">
						</div> -->
					<div class="col-4 col-md-2 form-group">
						<label for="email" class="fw-bold"><?php EchoLang('Fecha Nac.'); ?></label>
						<input type="email" class="form-control" name="fecha_nac" id="fecha_nac" value="<?php echo $objeto_usuario->IsUser() ? @$oculto_email : @$persona->FechaNacimiento; ?>" readonly />
					</div>
					<div class="col-4 col-sm-2 form-group">
						<label for="fecha-nac" class="fw-bold"><?php EchoLang('Edad'); ?></label>
						<input type="email" class="form-control" name="edad" id="edad" value="<?php echo (!empty($persona->FechaNacimiento)) ? cFechas::CalcularEdad($persona->FechaNacimiento) : ''; ?>" readonly />
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div id="mas-datos" class="collapse col-12">
				<div class="row">
					<div class="col-4 form-group">
						<label for="dir_post" class="fw-bold"><?php EchoLang('Dirección'); ?></label>
						<input type="text" class="form-control" name="dir_post" id="dir_post" value="" readonly />
					</div>

					
					<div class="col-3 form-group">
						<label for="ciudad_nombre" class="fw-bold"><?php EchoLang('Ciudad'); ?></label>
						<input type="text" class="form-control" name="ciudad_nombre" id="ciudad_nombre" value="<?php echo @$persona->Ciudad; ?>" readonly />
					</div>

					<div class="col-3 form-group">
						<label for="region_nombre" class="fw-bold"><?php EchoLang('Provincia'); ?></label>
						<input type="text" class="form-control" name="region_nombre" id="region_nombre" value="<?php echo @$persona->Region; ?>" readonly />
					</div>
				</div>
			</div>
		</div>
		<div class="row d-flex justify-content-center">
			<button class="btn bg-outline-primary text-center" href="#mas-datos" data-bs-toggle="collapse" aria-expanded="false" aria-controls="mas-datos" onclick="Giro(this, true);">
				<i class="fas fa-plus-circle text-primary"></i>
			</button>
		</div>
	</div>
</div>