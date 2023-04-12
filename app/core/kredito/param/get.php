<?php
/*
	Devolver el valor de un parámetro general del sistema siempre y cuando exista y además esté expuesto.
*/

$nombre = strtolower(substr($ws->params['name']??null,0,64));
$nombre = (!empty($nombre))?str_replace(['-',' '],'_', $nombre):$nombre;
if (!preg_match("~[a-z0-9_]+~i", $nombre)) {
	return $ws->SendResponse(405, 'Nombre de parámetro no es aceptable');
}

$valor = $sysParams->Get($nombre);
if (!$sysParams->existe) {
	return $ws->SendResponse(404, 'No encontrado.', 'Nombre no encontrado.');
}
if (!$sysParams->exponer) {
	return $ws->SendResponse(404, 'No encontrado..', 'nombre no encontrado.');
}

$ws->SendResponse(200, $valor);