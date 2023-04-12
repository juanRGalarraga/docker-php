<?php
/*
	Dar de alta un nuevo plan.
	Created: 2021-11-04
	Author: DriverOp
*/

require_once(DIR_model."planes".DS."class.planes.inc.php");

$plan = new cPlanes();
$plan->usuario = $ws->backendUserId??null;
$tipos_pagos = json_decode(file_get_contents(DIR_config."tipos_pagos.json"));

$msgerr = array();
$cargosImp = array();

require_once(DIR_model."negocios".DS."class.negocios.inc.php");
$negocios = new cNegocios;


require_once(__DIR__ .DS."checkParams.php");


if (CanUseArray($msgerr)) {
	return $ws->SendResponse(406, $msgerr);
}

if ($id = $plan->New()) {
	return $ws->SendResponse(200, $id, "Plan creado");
}
$ws->SendResponse(500, "No se pudo crear el plan.");
