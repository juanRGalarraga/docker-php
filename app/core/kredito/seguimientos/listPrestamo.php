<?php
    /**
     * Obtiene un listado de todos los seguimientos de un prestamo
     * Created: 2021-11-19
     * Author: api_creator
     */
/*
	TODO List:
	- Recolectar los seguimientos del préstamo.
*/

$prestamo_id = $ws->params['prestamo'];

require_once(DIR_model."prestamos".DS."class.prestamos.inc.php");

$objPrestamo = new cPrestamos;
$prestamo = $objPrestamo->Get($prestamo_id);
if (!$objPrestamo->existe) {
	return $ws->SendResponse(404, "Préstamo no encontrado.", 13);
}

if ($backendUser->negocio_id != $prestamo->negocio_id) {
	return $ws->SendResponse(409, "No pertenece a tu negocio.",21);
}
/* ************************************************************************ */
	$setup = json_decode(
<<<END
	{
	"descripcion":"Lista los seguimientos de un préstamo",
	"fields":[
		"`seguimientos`.`id`",
		"`seguimientos`.`prestamo_id`",
		"`seguimientos`.`persona_id`",
		"`seguimientos`.`accion_id`",
		"`seguimientos`.`notas`",
		"`seguimientos`.`data`",
		"`seguimientos`.`sys_fecha_modif`",
		"`seguimientos`.`sys_fecha_alta`",
		"`seguimientos`.`sys_usuario_id`",
		"`acciones`.`nombre` AS `accion`"
	]
}
END
);



require_once(DIR_model."listados".DS."class.listado.inc.php");

	$listado->SetOrderFields(['sys_fecha_alta','id']);
	$listado->SetSesValue('orden', ["sys_fecha_alta"=>"DESC","id"=>"DESC"]);

	$db = new cModels();
	
	$listado->withLimit = false;

	$select = "SELECT ".implode(", ",$setup->fields);
	$from = "FROM ".SQLQuote(TBL_seguimientos)." AS `seguimientos`";
	$join = "LEFT JOIN ".SQLQuote(TBL_seguimientos_acciones)." AS `acciones` ON `acciones`.`id` = `seguimientos`.`accion_id`";
	$where = "WHERE 1=1 AND `prestamo_id` = ".$prestamo_id;

	$listado->SetSQL(implode(" ",[$select, $from, $join, $where]));

	//EchoLog($listado->sql);

	try {
		$ws->log_max_length = 1024;
		$ws->SendResponse(200, $listado->GetResult($db));
	
	} catch(Exception $e) {
		$ws->SendResponse(500, 'Ocurrió un error al acceder a la base de datos.');
	} finally {
		$db->Disconnect();
	}
