<?php
    /**
     * Escribe o registra un dato
     * Created: [date]
     * Author: api_creator
     */
	 
	require_once(DIR_model."class.sysparams.inc.php");
	$params = new cSysParams;
	$msgerr = array();
	
	$id = SecureInt($ws->CutParam(["id"]));
	if(is_null($id)){
		cLogging::Write(__FILE__." ".__LINE__."El ID del parámetro no es un número entero válido");
		return $ws->SendResponse(400,null,10);
	}
	
	if(!$params->GetById($id)){
		cLogging::Write(__FILE__." ".__LINE__."El parámetro indicadó no fue encontrado");
		return $ws->SendResponse(404,null,170);
	}
	
	$valor = $ws->CutParam(["valor"]);
	if(empty($valor)){
		cLogging::Write(__FILE__." ".__LINE__."El valor indicadó para el parámetro con ID ".$id." no puede estar vacío");
		$msgerr["valor"] = "El valor no puede estar vacío";
	}
	
	$tipo = strtoupper($ws->CutParam(["tipo"]));
	if(empty($tipo)){
		cLogging::Write(__FILE__." ".__LINE__."El tipo de valor indicadó para el parámetro con ID ".$id." no puede estar vacío");
		$msgerr["tipo"] = "El tipo no puede estar vacío";
	}
	
	if(!in_array($tipo,VALID_TYPES_VALUES)){
		cLogging::Write(__FILE__." ".__LINE__."El tipo de valor indicadó para el parámetro con ID ".$id." no es válido");
		$msgerr["tipo"] = "El tipo indicadó no es válido";
	}
	
	$ofuscado = $ws->CutParam(["ofuscado"]) ?? true;
	$exponer = $ws->CutParam(["exponer"]) ?? false;
	$descripcion = $ws->CutParam(["descripcion"]) ?? "";
	$estado = strtoupper($ws->CutParam(["estado"]) ?? "HAB");
	if(!isset(ESTADOS_VALIDOS[$estado])){
		cLogging::Write(__FILE__." ".__LINE__."El estado indicadó para el parámetro con ID ".$id." no es válido");
		$msgerr["estado"] = "El estado indicadó no es válido";
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
	
	$grupos = $params->GetGrupos();
	$grupo = $ws->CutParam(["grupo"]) ?? 1;
	$find = false;
	foreach($grupos as $value){
		if($value['id'] != $grupo){ continue; }
		$find = true;
	}
	
	if(!$find){
		cLogging::Write(__FILE__." ".__LINE__."El grupo indicadó para el parámetro con ID ".$id." no fue encontrado");
		$msgerr["grupo"] = "El grupo indicadó no es válido";
	}
	
	if(CanUseArray($msgerr)){
		return $ws->SendResponse(406,$msgerr);
	}
	
	$reg = array(
		"valor" => $valor,
		"tipo" => $tipo,
		"ofuscado" => $ofuscado,
		"estado" => $estado,
		"descripcion" => $descripcion,
		"exponer" => $exponer,
		"grupo_id" => $grupo
	);
	
	if(!$params->UpdateReg($id,$reg)){
		cLogging::Write(__FILE__." ".__LINE__."No se pudo editar el parámetro con ID ".$id);
		return $ws->SendResponse(500,null,171);	
	}
	
	$ws->SendResponse(200,true);