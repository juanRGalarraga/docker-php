<?php
/*
	Determinar si el PIN es correcto para el PinId
	
	El cliente tiene que pasar los dos datos,
	El PINID es el ID que Infobip devuelve cuando se genera un PIN.
	El PIN es lo que el solicitante en el front escribió.
	
*/
$aca = __DIR__.DS;
$continue = true;
$msgerr = [];

$pinid = $ws->params['pinid']; // Este es el PIN ID que generó Infobip
$pin = $ws->params['pin']; // Esto es lo que escribió el solicitante.

// if ($sysParams->Get('infobip_modo_test', false)) {
	// $ws->SendResponse(200, 'PIN es correcto'); return;
// }

if (empty($pinid)) {
	$msgerr['pinid'] = "No se indicó";
}
if (empty($pin)) {
	$msgerr['pin'] = "No se indicó";
}
if (CanUseArray($msgerr)) {
	return $ws->SendResponse(406, $msgerr, 11);
}

require($aca."InfobipCheck.php");

if (!$continue) { return; }

$ws->SendResponse(200, $infobip_response["response"]);
