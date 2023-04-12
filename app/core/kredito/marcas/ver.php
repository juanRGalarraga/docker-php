<?php
/**
 * Obtiene y devuelve los detalles de una marca
 * Created: 2021-11-19
 * Author: Gastón Fernandez
 */
	require_once(DIR_model."marcas".DS."class.marcas.inc.php");
	$marcas = new cMarcas;

	$id = $ws->CutParam(['id','marca','marca_id']);
	if(is_null(SecureInt($id))){
		cLogging::Write(__FILE__ ." ".__LINE__ ." No se indico el ID de la marca a buscar");
		return $ws->SendResponse(400,null,212);
	}

	if(!$data = $marcas->Get($id)){
		cLogging::Write(__FILE__ ." ".__LINE__ ." La marca con ID ".$id." no fue encontrada");
		return $ws->SendResponse(404,null,215);
	}
	
	$data->imagen = $marcas->GetImage();

	$ws->SendResponse(200,$data);