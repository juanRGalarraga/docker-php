<?php

	
	$campos_orden = ["nombre"];
	$campos_busqueda = ["nombre"];
	
	require_once(DIR_model."listados".DS."class.listado.inc.php");
	
	$select = "SELECT `id`, `nombre`, `estado`, `descripcion` ";
	$from = "FROM ".SQLQuote(TBL_config_parametros_grupos)." AS `grupos`";
	$join = "";
	$where = "WHERE 1=1 ";
	
	$listado->SetSQL(implode(" ",[$select, $from, $join, $where]));
	
	
	$db = new cModels();
	
	try {

		$ws->SendResponse(200, $listado->GetResult($db));
	
	} catch(Exception $e) {
		$ws->SendResponse(500, 'Ocurrió un error al acceder a la base de datos.');
	} finally {
		$db->Disconnect();
	}