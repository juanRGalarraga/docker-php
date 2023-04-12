<?php
/*
	Devolver información sobre un CBU.
	Created: 2021-10-21
	Author: DriverOp

	Pasado como parámetro un CBU, se devuelve información sobre ese CBU.
	Si además se pasa como parámetro el ID de una solicitud, entonces también se evalúa, aplicando las políticas de negocios correspondientes, si ese CBU es válido como medio de transferencia del préstamo.
	
	NO prueba que el CBU esté en la base de datos. Para eso, usar la API checkcbu/

*/

	require_once(DIR_model."personas".DS."class.personasData.inc.php");
	require_once(DIR_model."personas".DS."class.personas.inc.php");
	require_once(DIR_integraciones."bind".DS."class.bind.inc.php");
	require_once(DIR_model."bancos".DS."class.bancos.inc.php");
	require_once(DIR_model. "solicitudes". DS. "class.solicitud.inc.php");

	$cbu = $ws->GetParams(['cbu']);
	if (empty($cbu)) {
		 return $ws->SendResponse(400, 'No se indicó CBU');
	}
	$solicitudid = $ws->GetParams(['sid']); // Parámetro que puede valer el id de una solicitud o bién la palabra "test"
	
	$bind = new cBindAPI();
	
	$bind->stress = false;
	//$bind->file_stress = DIR_BASE."tests".DS."bindstress".DS."01.json";
	
	if (!$bind->CuentaPorCBU($cbu)) {
		if ($bind->http_nroerr >= 500) {
			 return $ws->SendResponse(500, 'No se pudo recuperar información del CBU');
		}
		return $ws->SendResponse($bind->http_nroerr, $bind->message??'Error desconocido.',51);
	}
	//ShowVar($bind);
	$respuesta = array(
		"CBU"=>$cbu, // El CBU o CVU
		"nombreBanco"=>"[Banco no encontrado]", // Nombre del banco donde está la cuenta
		"tipoCuenta"=>strtoupper($bind->type??"desconocido"), // Tipo de cuenta en código CA, CC, etc
		"tipoCuentaDisplay"=>((in_array($bind->type,TIPOS_CUENTAS))?TIPOS_CUENTAS_DISPLAY[$bind->type]:"Desconocido"), // Nombre del tipo de cuenta para mostrar
		"titulares"=>array(), // Lista los titulares de la cuenta
		"activa"=>$bind->is_active??false, // La cuenta está o no activa.
		"moneda"=>$bind->currency??"ARS", // ISO moneda
		"alias"=>$bind->label??"" // El alias de CBU
	);

	$banco = new cBancos();
	if ($banco->Get(SecureInt(substr($cbu,0,3),null))) {
		$respuesta['nombreBanco'] = titleCase($banco->nombre);
	} else {
		if (isset($bind->bank_routing) and isset($bind->bank_routing->address) and !empty($bind->bank_routing->address)) {
			$respuesta['nombreBanco'] = $bind->bank_routing->address??$respuesta['nombreBanco'];
			$respuesta['nombreBanco'] = trim($respuesta['nombreBanco']);
		}
	}
	
	if (isset($bind->owners) and !empty($bind->owners) and is_array($bind->owners)) {
		foreach($bind->owners as $owner) {
			$respuesta["titulares"][] = array(
				"nombreApellido"=>titleCase(trim($owner->display_name)),
				"nroDoc"=>$owner->id,
				"tipoDoc"=>$owner->id_type,
				"tipoPersona"=>($owner->is_physical_person)?"FISICA":"JURIDICA"
			);
		}
	}
	if (empty($solicitudid)) {
		// No hay que hacer nada más, mostramos salida y terminamos...
		$ws->SendResponse(200, $respuesta);
		return;
	}
	
	// Si el parámetro sid es un entero, ver si existe una solicitud con ese id.
	if (CheckInt($solicitudid)) {
		$solicitud = new cSolicitudBase;
		$solicitud->Get($solicitudid);
		if (!$solicitud->existe) {
			$ws->SendResponse(404, "Solicitud indicada no fue encontrada", 153);
			return;
		}
	}
	
	$test = array(
		'pass'=>false,
		'motivo'=>'Es válido'
	);

/**
	La prueba de validez consiste en verificar si el CBU es válido para transferirle el préstamo de acuerdo a unas reglas de negocios.
	En caso de no pasar la prueba, el status HTTP debe ser 406 (No aceptable).
*/

//	- Tiene consistencia interna? no vacío, 22 caracteres, todos números.?
	if (!cCheckInput::CBU($cbu)) {
		$test['motivo'] = "No es un CBU válido.";
		$ws->SendResponse(406, $respuesta+$test, 50);
		return;
	}
	
// - El dígito verificador es válido.
	if (!cCheckInput::ValidarCBU($cbu)) {
		$test['motivo'] = "El CBU es incorrecto";
		$ws->SendResponse(406, $respuesta+$test, 51);
		return;
	}
// - El código de banco existe?
	if (!$banco->existe) {
		$test['motivo'] = "Código de banco no corresponde con ningún banco.";
		$ws->SendResponse(406, $respuesta+$test, 54);
		return;
	}
// - La cuenta está activa?
	if (!$bind->is_active) {
		$test['motivo'] = "La cuenta está inactiva.";
		$ws->SendResponse(406, $respuesta+$test, 52);
		return;
	}
// - El tipo de cuenta está permitido?
	$tiposPermitidos = $sysParams->Get('tipos_cuentas_permitidos','CA,CC,VIRTUAL');
	$tiposPermitidos = explode(',',$tiposPermitidos);
	$tiposPermitidos = array_map("strtoupper", $tiposPermitidos);
	if (!in_array($respuesta['tipoCuenta'], $tiposPermitidos)) {
		$test['motivo'] = "No se permite el tipo de cuenta.";
		$ws->SendResponse(406, $respuesta+$test, 53);
		return;
	}

	$test['pass'] = true;
	$ws->SendResponse(200, $respuesta+$test);