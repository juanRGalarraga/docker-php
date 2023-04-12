<?php
/**
*	Obtiene el listado de planes
*	Created: 2021-09-20
*	Author: Gastón Fernandez
*/
	$campos_orden = ["id","alias","sys_fecha_alta"];
	$campos_busqueda = ["alias","nombre_comercial"];
	
	$buscar = mb_strtolower(mb_substr($ws->params['buscar']??null,0,25));
	$novacias = $ws->GetParam(['novacia','novacias','noempty']);

	require_once(DIR_model."listados".DS."class.listado.inc.php");
	
	if(!$listado->GetSesValue("orden")){
		$listado->SetSesValue("orden",["alias"=>"ASC"]);
	}
	
	$db = new cModels();

	$select = "SELECT * ";
	$from = "FROM ".SQLQuote(TBL_planes)." AS `planes`";
	$join = "";
	$where = "WHERE 1=1 ";
	$where .= $listado->SetSearch($buscar, $campos_busqueda);
	
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
