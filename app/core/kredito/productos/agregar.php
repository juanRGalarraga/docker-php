<?php
/**
 * Realiza la creación de un producto
 * Created: 2021-11-19
 * Author: Gastón Fernandez
 */
	require_once(DIR_model."productos".DS."class.productos.inc.php");
	require_once(DIR_model."modelos".DS."class.modelos.inc.php");
	require_once(DIR_includes."class.proccessImages.inc.php");
	$productos = new cProductos;
	$modelos = new cModelos;

	$reg = array();
	$nombre = trim($ws->CutParam(['nombre','name']));
	if(empty($nombre)){
		cLogging::Write(__FILE__." ".__LINE__." El nombre del producto esta vacío");
		return $ws->SendResponse(400,null,230);
	}
	$reg['nombre'] = $nombre;

	$modelo = $ws->CutParam(['modelo','modelo_id']);
	if(is_null(SecureInt($modelo))){
		cLogging::Write(__FILE__." ".__LINE__." El modelo del producto esta vacío");
		return $ws->SendResponse(400,null,231);
	}
	$reg['modelo_id'] = $modelo;

	if(!$modelos->Get($modelo)){
		cLogging::Write(__FILE__ ." ".__LINE__ ." El modelo con ID ".$modelo." no fue encontrado");
		return $ws->SendResponse(404,null,226);
	}

	$precio = $ws->CutParam(['precio','price']);
	if(is_null(SecureFloat($precio))){
		cLogging::Write(__FILE__." ".__LINE__." El precio del producto esta vacío");
		return $ws->SendResponse(400,null,232);
	}
	$reg['precio'] = $precio;

	$estado = strtoupper($ws->CutParam(['estado']));
	if(!empty($estado) AND is_string($estado) AND isset(ESTADOS_VALIDOS[$estado])){
		$reg['estado'] = $estado;
	}
	
	$descripcion = trim($ws->CutParam(['descripcion','description']));
	if(!empty($descripcion)){
		$reg['descripcion'] = $descripcion;
	}
	
	//Obtenemos la imagen si es que la hay
	$imagen_nombre = $ws->CutParam(['imagen_nombre','image_name']);
	$imagen_data = $ws->CutParam(['imagen_data','image_data']);//base64

	$reg['data'] = array();
	$data = $ws->CutParam(['data']);
	if(CanUseArray($data)){
		$reg['data'] = $data;
	}
	$parametros = $ws->params;
	unset($parametros['_virualpath_']);//No lo necesitamos

	if(CanUseArray($parametros)){
		$reg['data'] = array_merge($reg['data'],$parametros);
	}

	if(!$productos->Crear($reg)){
		cLogging::Write(__FILE__ ." ".__LINE__." No se pudo crear el producto");
		return $ws->SendResponse(500,null,233);
	}
	
	
	if(!empty($imagen_nombre) AND !empty($imagen_data)){
		$result = cImages::CreateImage($imagen_nombre,$imagen_data,"productos/".$productos->last_id);
		if(!CanUseArray($result)){
			cLogging::Write(__FILE__." ".__LINE__." No se pudo guardar la imagen: ".cImages::$msgErr."\nDataErr:".(CanUseArray(cImages::$dataErr))? print_r(cImages::$dataErr):"");
		}else{
			$reg['data']['imagen_file'] = $result['archivo'];
			$reg['data']['imagen_nombre'] = $result['nombre'];	
			$productos->Get($productos->last_id);
			$productos->Editar(["data"=>$reg['data']]);
		}			
	}
	$ws->SendResponse(201,$productos->last_id);