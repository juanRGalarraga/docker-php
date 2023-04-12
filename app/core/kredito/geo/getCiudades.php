<?php
	/**
	* Obtiene un listado con las ciudades
	* Created: 2021-10-27
	* Pseudo-Author: Juan Galarraga
	*/
	
	$campos_orden = ["nombre","nombre_region"];
	$campos_busqueda = ["nombre","nombre_region"];

	require_once(DIR_model."listados".DS."class.listado.inc.php");
	
	$sql = "SELECT * FROM ".SQLQuote(TBL_ciudades);
	$listado->SetSQL($sql);
	$db = new cModels();
	
	try {

		$ws->SendResponse(200, $listado->GetResult($db));
	
	} catch(Exception $e) {
		$ws->SendResponse(500, 'Ocurrió un error al acceder a la base de datos.');
	} finally {
		$db->Disconnect();
	}