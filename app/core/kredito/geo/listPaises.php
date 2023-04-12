<?php
	/**
	* Obtiene un listado con los países
	* Created: 2021-10-27
	* Pseudo-Author: Juan Galarraga
	*/
	
	$campos_orden = ["nombre_pais"];
	$campos_busqueda = ["nombre_pais", "isonum", "iso2", "iso3"];

	require_once(DIR_model."listados".DS."class.listado.inc.php");
	
	$sql = "SELECT * FROM ".SQLQuote(TBL_paises);
	$listado->SetSQL($sql);
	
	$db = new cModels();
	// ShowVar($_SESSION);
	try {

		$ws->SendResponse(200, $listado->GetResult($db));
	
	} catch(Exception $e) {
		$ws->SendResponse(500, 'Ocurrió un error al acceder a la base de datos.');
	} finally {
		$db->Disconnect();
	}