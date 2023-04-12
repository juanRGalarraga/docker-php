<?php
/**
 * Summary. Guarda la edición de un parámetro
 * Created: 2021-10-28
 * Author: Gastón Fernandez
 */
	require_once(DIR_model."configuraciones".DS."class.parametros.inc.php");
	$params = new cParametros;
	$msgerr = array();

	$nombre = FindParam("id");
	if(empty($nombre)){ 
		cLogging::Write(__FILE__." ".__LINE__." El nombre del parámetro esta vacío.");
		return EmitJSON("No se puede guardar la edición del parámetro en este momento");
	}

	if(!$data = $params->GetByName($nombre)){ 
		cLogging::Write(__FILE__." ".__LINE__." El parámetro con el nombre ".$nombre." no fue encontrado");
		return EmitJSON("No se puede guardar la edición del parámetro en este momento");
	}
	if(empty($data)){
		cLogging::Write(__FILE__." ".__LINE__." El parámetro con el nombre ".$nombre." no fue encontrado");
		return EmitJSON("No se puede guardar la edición del parámetro en este momento");
	}
	$id = $data->id;

	$valor = FindParam("valor");
	if(empty($valor)) {
		cLogging::Write(__FILE__." ".__LINE__." El valor del parámetro esta vacío.");
		$msgerr['valor'] = "El valor no puede estar vacío";
	}

	$exponer = !empty(FindParam("exponer"));
	$ofuscado = !empty(FindParam("ofuscado"));
	$descripcion = FindParam("descripcion");

	$tipo = strtoupper(FindParam("tipo"));
	if(empty($tipo)){
		cLogging::Write(__FILE__." ".__LINE__." El Tipo no puede estar vacío");
		$msgerr['tipo'] = "Debes indicar el tipo de dato";
	}

	if(!empty($tipo) AND !in_array($tipo,VALID_TYPES_VALUES)){
		cLogging::Write(__FILE__." ".__LINE__." El tipo del parámetro no es válido");
		$msgerr['tipo'] = "Debes indicar un tipo válido";
	}

	$estado = strtoupper(FindParam("estado"));
	if(empty($estado)){ 
		cLogging::Write(__FILE__." ".__LINE__." El estado del parámetro esta vacío");
		$msgerr['estado'] = "Debes indicar un estado al parámetro";
	}

	if(!empty($estado) AND !isset(ESTADOS_VALIDOS[$estado])){
		cLogging::Write(__FILE__." ".__LINE__." El estado del parámetro no es válido");
		$msgerr['estado'] = "Debes indicar un estado válido";
	}

	$grupo = SecureInt(FindParam("grupo"));
	if(is_null($grupo)){ 
		cLogging::Write(__FILE__." ".__LINE__." El Grupo esta vacío o no es un número");
		$msgerr['grupo'] = "Debes indicar un grupo";
	}

	if(CanUseArray($msgerr)){
		EmitJSON($msgerr);
		return;
	}

	$msgerr['tipo'] = "El tipo indicadó no es válido para el tipo de valor a guardar";
	switch($tipo){
		case 'INT':
				if(!is_null(SecureBigInt($valor))){ unset($msgerr['tipo']); }
			break;
		case 'FLOAT':
				if(!is_null(SecureBigFloat($valor))){ unset($msgerr['tipo']); }
			break;
		case 'BOOL':
				if(CheckBool($valor)){ unset($msgerr['tipo']); }
			break;
		case 'JSON':
				if(IsJsonEx($valor)){ unset($msgerr['tipo']); }
			break;
		default:
				if(is_string($valor)){ unset($msgerr['tipo']); }	
			break;
	}

	if(CanUseArray($msgerr)){
		EmitJSON($msgerr);
		return;
	}

	$reg = [
		"valor" => $valor,
		"tipo" => $tipo,
		"ofuscado" => $ofuscado,
		"estado" => $estado,
		"descripcion" => $descripcion,
		"exponer" => $exponer,
		"grupo" => $grupo
	];

	if(!$params->Editar($id,$reg)){
		cLogging::Write(__FILE__." ".__LINE__." No se pudo editar el parámetro");
		return EmitJSON("No se pudo editar el parámetro");
	}

	ResponseOk();