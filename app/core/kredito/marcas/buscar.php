<?php
/**
 * Obtiene y devuelve las marcas coincidentes con un string dado
 * Created: 2021-11-24
 * Author: Gastón Fernandez
 */
	require_once(DIR_model."marcas".DS."class.marcas.inc.php");
	$marcas = new cMarcas;

	$buscar = trim($ws->CutParam(['buscar','searc','marca']));
	if(empty($buscar)){
		cLogging::Write(__FILE__ ." ".__LINE__ ." No se indicó la coincidencia a buscar");
		return $ws->SendResponse(400,null,10);
	}
	
	if(!$data = $marcas->BuscarCoincidencia($buscar)){
		cLogging::Write(__FILE__ ." ".__LINE__ ." No se encontraron marcas coincidentes con '".$buscar."'");
		return $ws->SendResponse(404,null,13);
	}
	
	$ws->SendResponse(200,$data);