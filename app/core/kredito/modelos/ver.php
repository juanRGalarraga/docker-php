<?php
/**
 * Obtiene y devuelve los detalles de un modelo
 * Created: 2021-11-19
 * Author: Gastón Fernandez
 */
	require_once(DIR_model."modelos".DS."class.modelos.inc.php");
	$modelos = new cModelos;

	$reg = array();
	$id = $ws->CutParam(['id','modelo','modelo_id']);
	if(is_null(SecureInt($id))){
		cLogging::Write(__FILE__ ." ".__LINE__ ." No se indico el ID del modelo a editar");
		return $ws->SendResponse(400,null,222);
	}

	if(!$data = $modelos->Get($id)){
		cLogging::Write(__FILE__ ." ".__LINE__ ." El modelo con ID ".$id." no fue encontrado");
		return $ws->SendResponse(404,null,225);
	}
	
	$data->imagen = $modelos->GetImage();

	$ws->SendResponse(200,$data);