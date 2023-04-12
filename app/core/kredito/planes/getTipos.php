<?php
/*
	Devolver los tipos de planes disponibles.
*/


if (!ExisteArchivo(DIR_config."tipos_pagos.json")) {
	return $ws->SendResponse(500, "No se configuraron tipos de pagos en planes.");
}

$salida = json_decode(file_get_contents(DIR_config."tipos_pagos.json"));
if (json_last_error() != JSON_ERROR_NONE) {
	return $ws->SendResponse(500, "Configuración de tipos de pago mal formada.");
}

$tipo = $ws->params['tipo']??null;
if (!empty($tipo)) {
	$tipo = strtolower(substr(trim($tipo),0,32));
	if (!isset($salida->$tipo)) {
		return $ws->SendResponse(404, "Tipo de pago no encontrado.");
	}
	$salida = $salida->$tipo;
}



$ws->SendResponse(200, $salida);