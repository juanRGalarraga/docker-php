<?php
	/**
	* Obtiene un listado con los países
	* Created: 2021-10-27
	* Pseudo-Author: Juan Galarraga
	*/
	$campos_orden = ["nombre_pais"];
	$campos_busqueda = ["nombre_pais", "isonum", "iso2", "iso3"];

	require_once(DIR_model."listados".DS."class.listado.inc.php");
    $id = SecureInt($ws->GetParam('id'));
    if(!$id) return $ws->SendResponse(400, null, 10, "No se indicó ID del país");

	
	$sql = "SELECT * FROM ".SQLQuote(TBL_regiones)." WHERE `pais_id` = ".$id;
	$listado->SetSQL($sql);
	
	$db = new cModels();
	
	try {
		$ws->SendResponse(200, $listado->GetResult($db));
	} catch(Exception $e) {
		$ws->SendResponse(500, null, null,'Ocurrió un error al acceder a la base de datos.');
	} finally {
		$db->Disconnect();
	}