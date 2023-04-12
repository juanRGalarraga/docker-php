<?php
/**
 * Obtiene y devuelve los detalles de un producto
 * Created: 2021-11-19
 * Author: Gastón Fernandez
 */
	require_once(DIR_model."productos".DS."class.productos.inc.php");
	$productos = new cProductos;

	$reg = array();
	$id = $ws->CutParam(['id','producto','producto_id']);
	if(is_null(SecureInt($id))){
		cLogging::Write(__FILE__ ." ".__LINE__ ." No se indico el ID del producto a buscar");
		return $ws->SendResponse(400,null,234);
	}

	if(!$data = $productos->Get($id)){
		cLogging::Write(__FILE__ ." ".__LINE__ ." El producto con ID ".$id." no fue encontrado");
		return $ws->SendResponse(404,null,237);
	}
	
	$data->imagen = $productos->GetImage();

	$ws->SendResponse(200,$data);