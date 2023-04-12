<?php
/**
*	Obtiene todo el historial de un préstamo
*	Created: 2021-11-08
*	Author: Gastón Fernandez
*/
	$prestamo_id = SecureInt($ws->GetParam("id"));
	if(is_null($prestamo_id)){
		cLogging::Write(__FILE__." ".__LINE__." El ID del préstamo no fue indicadó");
		return $ws->SendResponse(400,null,180);
	}
	
	$setup = json_decode(
<<<END
	{
	"descripcion":"Listado de los campos que se envían al cliente",
	"fields":[
		"`hist`.`id`",
		"`hist`.`monto_cobro`",
		"`hist`.`fechahora`",
		"`hist`.`estado`",
		"`hist`.`capital`",
		"`hist`.`interes_capital`",
		"`hist`.`iva_interes_capital`",
		"`hist`.`total_mora`",
		"`hist`.`iva_mora`",
		"`hist`.`total_cargos`",
		"`hist`.`total_pagado`",
		"`hist`.`total_imponible`",
		"`hist`.`observaciones`"
	]
}
END
);
	
	$campos_orden = ["id","fechahora","orden_hist"];
	require_once(DIR_model."listados".DS."class.listado.inc.php");
	$db = new cModels();
	
	if(!$listado->GetSesValue("orden")){
		$listado->SetSesValue("orden",["fechahora"=>"DESC"]);
	}

	$select = "SELECT ".implode(", ",$setup->fields);
	$from = "FROM ".SQLQuote(TBL_prestamos_hist)." AS `hist`";
	$join = "";
	$where = "WHERE `hist`.`prestamo_id`=".$prestamo_id;
	$listado->SetSQL(implode(" ",[$select, $from, $join, $where]));
	
	try {
		$ws->log_max_length = 1024;
		$ws->SendResponse(200, $listado->GetResult($db));
	
	} catch(Exception $e) {
		$ws->SendResponse(500, 'Ocurrió un error al acceder a la base de datos.');
	} finally {
		$db->Disconnect();
	}
