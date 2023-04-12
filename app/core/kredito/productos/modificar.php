<?php
/**
 * Edita un producto ya existente
 * Created: 2021-11-19
 * Author: Gastón Fernandez
 */
	require_once(DIR_model."productos".DS."class.productos.inc.php");
	require_once(DIR_model."modelos".DS."class.modelos.inc.php");
	require_once(DIR_includes."class.proccessImages.inc.php");
	$productos = new cProductos;
	$modelos = new cModelos;

	$reg = array();
	$id = $ws->CutParam(['id','producto','producto_id']);
	if(is_null(SecureInt($id))){
		cLogging::Write(__FILE__ ." ".__LINE__ ." No se indico el ID del producto a editar");
		return $ws->SendResponse(400,null,234);
	}

	if(!$productos->Get($id)){
		cLogging::Write(__FILE__ ." ".__LINE__ ." El producto con ID ".$id." no fue encontrado");
		return $ws->SendResponse(404,null,237);
	}

	$modelo = $ws->CutParam(['modelo','modelo_id']);
	if(!is_null(SecureInt($modelo))){
		if(!$modelos->Get($modelo)){
			cLogging::Write(__FILE__ ." ".__LINE__ ." El modelo con ID ".$modelo." no fue encontrado");
			return $ws->SendResponse(404,null,226);
		}
		$reg['modelo_id'] = $modelo;
	}


	$nombre = trim($ws->CutParam(['nombre','name']));
	if(!empty($nombre)){
		$reg['nombre'] = $nombre;
	}

	$precio = $ws->CutParam(['precio','price']);
	if(!empty($precio) AND SecureFloat($precio)){
		$reg['precio'] = $precio;
	}

	$estado = strtoupper($ws->CutParam(['estado']));
	if(!empty($estado) AND is_string($estado) AND isset(ESTADOS_VALIDOS[$estado])){
		$reg['estado'] = $estado;
	}
	
	$descripcion = trim($ws->CutParam(['descripcion','description']));
	if(!empty($descripcion)){
		$reg['descripcion'] = $descripcion;
	}
	
	$reg['data'] = (!empty($productos->data))? (array)$productos->data:array();
	//Obtenemos la imagen si es que la hay
	$imagen_nombre = $ws->CutParam(['imagen_nombre','image_name']);
	$imagen_data = $ws->CutParam(['imagen_data','image_data']);
	if(!empty($imagen_nombre) AND !empty($imagen_data)){
		$result = cImages::CreateImage($imagen_nombre,$imagen_data,"productos/".$productos->id);
		if(!CanUseArray($result)){
			cLogging::Write(__FILE__." ".__LINE__." No se pudo guardar la imagen: ".cImages::$msgErr."\nDataErr:".(CanUseArray(cImages::$dataErr))? print_r(cImages::$dataErr):"");
			return $ws->SendResponse(500,["msgerr"=>"No se pudo guardar la imagen"],216);
		}else{
			$reg['data']['imagen_file'] = $result['archivo'];
			$reg['data']['imagen_nombre'] = $result['nombre'];	
		}			
	}
	$data = $ws->CutParam(['data']);
	if(CanUseArray($data)){
		$reg['data'] = array_merge($reg['data'],$data);
	}
	$parametros = $ws->params;
	unset($parametros['_virualpath_']);//No lo necesitamos

	if(CanUseArray($parametros)){
		$reg['data'] = array_merge($reg['data'],$parametros);
	}

	if(!CanUseArray($reg)){
		cLogging::Write(__FILE__ ." ".__LINE__." No se indicaron datos con los que editar el producto");
		return $ws->SendResponse(400,null,235);
	}
	
	if(!$productos->Editar($reg)){
		cLogging::Write(__FILE__ ." ".__LINE__." No se pudo editar el producto con ID ".$id);
		return $ws->SendResponse(500,null,236);
	}

	$ws->SendResponse(201,true);