<?php
/**
*	Devuelve los filtros de mora disponibles para utilizar
*	Created: 2021-11-05
*	Author: Gastón Fernandez
*/
	require_once(DIR_model."prestamos".DS."class.filtrosMora.inc.php");
	$filtros = new cFiltrosMoras;
	
	if(!$data = $filtros->GetAllFilters()){
		cLogging::Write(__FILE__." ".__LINE__." No se encontraron los filtros de mora");
		return $ws->SendResponse(404,null,13);
	}
	
	$result = array();
	foreach($data as $key => $value){
		$result[] = array(
			'id' => $value->id,
			'nombre' => $value->nombre
		);
	}
	$ws->SendResponse(200,$result);