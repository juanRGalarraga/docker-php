<?php
/*
	Devolver todos los datos de un plan. Para el backend.
	Created: 2021-10-30
	Author: DriverOp
	
	Nota: El cliente debe enviar el ID del usuario en el backend en la cabecera "Backend-Id"
	
	Updated: 2021-10-31
	Author: DriverOp
		Incluir la lista de cargos e impuestos que se le pueden aplicar y marcados los que actualmente se aplican.
*/

if (empty($ws->backendUserId)) {
	return $ws->SendResponse(400, 'ID de usuario backend requerido. Cabecera Backend-id está vacía.');
}

require_once(DIR_model."planes".DS."class.planes.inc.php");
require_once(DIR_model."planes".DS."class.cargos_impuestos.inc.php");

$planes = new cPlanes();

$id = $ws->GetParam('id');
$salida = $planes->Get($id);
if (!$planes->existe) {
	return $ws->SendResponse(404, 'Plan no encontrado: '.$id);
}

$cargosimp = new cCargosImp();

$cargosimp->selectFields = "id, nombre, alias, valor, tipo, estado";

$salida->cargos = $cargosimp->GetAllMarkByPlan($planes->id, "CARGO");
$salida->impuestos = $cargosimp->GetAllMarkByPlan($planes->id, "IMP");


$ws->SendResponse(200, $salida);


