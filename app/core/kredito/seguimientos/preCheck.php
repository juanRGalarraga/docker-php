<?php
/*
	Este es el precheck de seguimiento en donde se verifica que el cliente haya enviado el ID del usuario del backend y que ese ID sea válido.
*/

if (empty($ws->backendUserId)) {
	return $ws->SendResponse(400, 'Falta indicar ID de usuario.', 15);
}
if (!checkInt($ws->backendUserId)) {
	return $ws->SendResponse(400, 'ID de usuario incorrecto', 11);
}
require_once(DIR_model."backenduser".DS."class.backenduser.inc.php");

$objBackendUser = new cBackendUser;
$backendUser = $objBackendUser->Get($ws->backendUserId);

if (!$backendUser) {
	return $ws->SendResponse(400, 'Usuario backend no existe.', 13);
}

