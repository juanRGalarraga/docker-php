<?php
/*
	Devolver los datos de un plan, para el cotizador
*/

require_once(DIR_model."planes".DS."class.planes.inc.php");

$plan = new cPlanes();
$plan->usuario = $ws->backendUserId??null;
$tipos_pagos = json_decode(file_get_contents(DIR_config."tipos_pagos.json"));

$msgerr = array();
$cargosImp = array();

require_once(DIR_model."negocios".DS."class.negocios.inc.php");
$negocios = new cNegocios;


$id = $ws->GetParam('id');

if (empty($id)) {
	return $ws->SendResponse(400, 'Falta indicar Id de plan');
}

$salida = $plan->Get($id);
if (!$salida) {
	return $ws->SendResponse(404, 'Plan no encontrado.');
}

require_once(__DIR__ .DS."checkParams.php");

if (CanUseArray($msgerr)) {
	return $ws->SendResponse(406, $msgerr);
}

if (!$plan->Set()) {
	return $ws->SendResponse(500, "No se pudo actualizar el plan.");
}
$ws->SendResponse(200, "Plan actualizado.");
