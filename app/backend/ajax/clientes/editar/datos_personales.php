<?php
/**
 * Edición de clientes, datos personales
 * Created: 2021-11-04
 * Author: Gastón Fernandez
 */
	$nombre = $data->Nombre ?? "-";
	$apellido = $data->Apellido ?? "-";
	$tipo_doc = $data->TipoDoc ?? "-";
	$nro_doc = $data->NroDoc ?? "-";
	$fecha_nac = $data->FechaNacimiento ?? null;
	$edad = "";
	if(cFechas::LooksLikeISODate($fecha_nac)){
		$edad = cFechas::CalcularEdad($fecha_nac);
	}
?>
<div class="card">
	<div class="card-body">
		<form name="frmDatosPersonales" id="frmDatosPersonales">
			<div class="row row-cols-1 row-cols-sm-2">
				<!-- NOM APE -->
				<div class="col">
					<label class="form-label" for="nombre">Nombre</label>
					<input class="form-control form-control-sm" type="text" name="nombre" id="nombre" value="<?php echo $nombre; ?>" placeholder="Nombre del cliente" disabled>
				</div>

				<div class="col">
					<label class="form-label" for="apellido">Apellido</label>
					<input class="form-control form-control-sm" type="text" name="apellido" id="apellido" value="<?php echo $apellido; ?>" placeholder="Apellido del cliente" disabled>
				</div>

				<!-- DOC -->
				<div class="col">
					<label class="form-label" for="tipo_doc">Tipo documento</label>
					<select name="tipo_doc" id="tipo_doc" class="form-select form-select-sm" disabled>
						<option selected disabled>-</option>
						<?php foreach (DOCUMENTOS as $key => $value){ ?>
								<option value="<?php echo $key ?>" <?php echo ($tipo_doc == $key)? "selected":"" ?>><?php echo $value ?></option>
					<?php } ?>
					</select>
				</div>

				<div class="col">
					<label class="form-label">Nro. Documento</label>
					<input class="form-control form-control-sm" type="text" value="<?php echo $nro_doc; ?>" disabled placeholder="Número de documento del cliente">
				</div>

				<!-- Edad -->
				<div class="col">
					<label class="form-label" for="fecha_nacimiento">Fecha de nacimiento</label>
					<input class="form-control form-control-sm" type="date" name="fecha_nacimiento" id="fecha_nacimiento" value="<?php echo $fecha_nac; ?>" placeholder="Fecha de nacimiento" disabled>
				</div>

				<div class="col">
					<label class="form-label">Edad</label>
					<input class="form-control form-control-sm" type="text" disabled value="<?php echo $edad; ?>" placeholder="Edad">
				</div>
			</div>	
		</form>
	</div>
</div>