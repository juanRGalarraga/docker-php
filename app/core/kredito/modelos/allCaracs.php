<?php
/**
 * Obtiene y devuelve los detalles de un modelo
 * Created: 2021-11-19
 * Author: Gastón Fernandez
 */
	require_once(DIR_model."modelos".DS."class.modelos.inc.php");
	$modelos = new cModelos;

	if(!$data = $modelos->GetCaracs()){
		cLogging::Write(__FILE__." ".__LINE__." El listado de características disponibles no fue encontrado");
		return $ws->SendResponse(404,null,227);
	}

	$ws->SendResponse(200,$data);