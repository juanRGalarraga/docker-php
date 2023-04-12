<?php
/**
 * Obtiene una marca dado su nombre
 * Created: 2021-11-25
 * Author: Gastón Fernandez
 */
	require_once(DIR_model."marcas".DS."class.marcas.inc.php");
	$marcas = new cMarcas;

	$marca = $ws->CutParam(['marca','nombre']);
	if(empty($marca)){
		cLogging::Write(__FILE__ ." ".__LINE__ ." No se indicó el nombre de la marca a buscar");
		return $ws->SendResponse(400,null,210);
	}

	if(!$data = $marcas->GetByName($marca)){
		cLogging::Write(__FILE__ ." ".__LINE__ ." La marca con nombre ".$marca." no fue encontrada");
		return $ws->SendResponse(404,null,215);
	}

	$ws->SendResponse(200,$data);