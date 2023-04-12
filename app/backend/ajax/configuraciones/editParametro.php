<?php
/**
 * Formulario para la edición de parámetros del sistema
 * Created: 2021-10-27
 * Author: Gastón Fernandez
 */
	require_once(DIR_model."configuraciones".DS."class.parametros.inc.php");
	require_once(DIR_model."usuarios".DS."class.usuarios.inc.php");
	$params = new cParametros;
	$usuarios = new cUsuarios;

	$name = FindParam("nombre,nom");
	if(empty($name)){ 
		cLogging::Write(__FILE__." ".__LINE__." No se indicó el nombre del parámetro a editar");
		return EmitJSON("No se puede editar este parametro en este momento");
	}

	if(!$data = $params->GetByName($name)){ 
		cLogging::Write(__FILE__." ".__LINE__." No se pudo obtener el parámetro del servidor");
		return EmitJSON("No se puede editar este parametro en este momento");
	}
	if(empty($data)){
		cLogging::Write(__FILE__." ".__LINE__." El parámetro con el nombre ".$nombre." no fue encontrado");
		return EmitJSON("No se puede guardar la edición del parámetro en este momento");
	}

	$grupos = $params->GetGrupos();
	
	$data = (array)$data;
	$grupo = $data['grupo_id'] ?? "-";
	$valor = $data['valor'] ?? "-";
	$tipo = $data['tipo'] ?? "-";
	$estado = $data['estado'] ?? "-";
	$descripcion = $data['descripcion'] ?? "-";
	$exponer = $data['exponer'] ?? "-";
	$ofuscado = $data['ofuscado'] ?? "-";
	$fecha_modif = $data['sys_fecha_modif'] ?? "-";
	$fecha_alta = $data['sys_fecha_alta'] ?? "-";	
	$usuario_id = $data['sys_usuario_id'] ?? "-";
	$nombre = "Nadie";

	if(cFechas::LooksLikeISODateTime($fecha_modif) OR cFechas::LooksLikeISODate($fecha_modif)){
		$fecha_modif = cFechas::SQLDate2Str($fecha_modif);
	}

	if(cFechas::LooksLikeISODateTime($fecha_alta) OR cFechas::LooksLikeISODate($fecha_alta)){
		$fecha_alta = cFechas::SQLDate2Str($fecha_alta);
	}

	if(!is_null(SecureInt($usuario_id))){
		if($tmp = $usuarios->GetCompleteName($usuario_id)){
			$nombre = $tmp;
		}
	}
	$created = ReturnLang("Alta").": <b>".$fecha_alta."</b>. ";
	$modif = ReturnLang("Modificado").": <b>".$fecha_modif."</b>. ";
	$by = ReturnLang("Por").": (<b>".$nombre."</b>).";

	$footer = $created.$modif.$by;
?>

	<div class="row">
		<form name="formEditParam" id="formEditParam">
			<input id="id" name="id" type="hidden" value="<?php echo $name; ?>">
			<div class="row">
				<div class="col-12 col-sm-6">
					<label><?php EchoLang("Nombre"); ?></label>
					<input class="form-control" type="text" value="<?php echo $name; ?>" disabled>
				</div>

				<div class="col-12 col-sm-6">
					<label><?php EchoLang("Valor"); ?></label>
					<input class="form-control" type="text" value="<?php echo $valor; ?>" name="valor" id="valor" placeholder="Valor">
				</div>

				<div class="col-12 col-sm-6 custom-control custom-checkbox text-sm-end offset-sm-6">
					<input id="exponer" class="custom-control-input" type="checkbox" name="exponer" <?php echo ($exponer)? 'checked':''; ?> >
					<label for="exponer" class="custom-control-label"><?php EchoLang("Exponer Valor"); ?></label>

					<input id="ofuscado" class="custom-control-input" type="checkbox" name="ofuscado" <?php echo ($ofuscado)? 'checked':''; ?> >
					<label for="ofuscado" class="custom-control-label"><?php EchoLang("Ofuscar Valor"); ?></label>
				</div>
			</div>

			<div class="row">
				<div class="col-12 col-sm-6">
					<label><?php EchoLang("Grupo"); ?></label>
					<select id="grupo" name="grupo" class="form-select">
							<?php
							foreach ($grupos as $value) { 
									$nom = $value->nombre ?? null;
									$id = $value->id ?? null;
								?>
								<option value="<?php echo $id; ?>" <?php echo ($id == $grupo)? 'selected':''; ?>><?php echo $nom; ?></option>
					<?php	} ?>
					</select>
				</div>

				<div class="col-12 col-sm-6">
						<label><?php EchoLang("Tipo"); ?></label>
						<select id="tipo" name="tipo" class="form-select">
							<?php
							foreach (VALID_TYPES_VALUES as $value) { ?>
								<option value="<?php echo $value; ?>" <?php echo ($value == $tipo)? 'selected':''; ?>><?php echo $value; ?></option>
					<?php	} ?>
						</select>
				</div>
			</div>

			<div class="row">
				<div class="col-12">
					<label><?php EchoLang("Descripción"); ?></label>
					<textarea class="form-control"  rows="3" value="<?php echo $descripcion; ?>" name="descripcion" id="descripcion" placeholder="Descripción"><?php echo $descripcion; ?></textarea>
				</div>
			</div>

			<div class="row">
				<div class="col-12 col-sm-6 custom-control custom-checkbox d-flex flex-column">
					<span>
						<input id="habilitado" class="custom-control-input" type="radio" name="estado" value="HAB" <?php echo ($estado == 'HAB')? 'checked':''; ?> >
						<label for="habilitado" class="custom-control-label"><?php EchoLang("Habilitado"); ?></label>
					</span>

					<span>
						<input id="deshabilitado" class="custom-control-input" type="radio" name="estado" value="DES" <?php echo ($estado == 'DES')? 'checked':''; ?> >
						<label for="deshabilitado" class="custom-control-label"><?php EchoLang("Deshabilitado"); ?></label>
					</span>

					<span>
						<input id="eliminado" class="custom-control-input" type="radio" name="estado" value="ELI" <?php echo ($estado == 'ELI')? 'checked':''; ?> >
						<label for="eliminado" class="custom-control-label"><?php EchoLang("Eliminado"); ?></label>
					</span>
				</div>
			</div>

			<div class="row">
				<div class="col-12">
					<span><?php EchoLang($footer); ?></span>
				</div>
			</div>

			<div class="card-footer d-flex justify-content-between">
				<button class="btn btn-danger" data-bs-target="#seccionParametros" data-bs-slide-to="0" aria-label="Slide 2"><i class="mx-3 fas fa-backspace" aria-hidden="true" title="Cancelar edición"></i> Volver</button>
				<button class="btn btn-success" id="btnConfirm" onclick="GuardarEdicion(this);"><i class="mx-3 fas fa-check-double" aria-hidden="true"></i> <?php EchoLang("Guardar"); ?></button>
			</div>
		</form>
	</div>
	