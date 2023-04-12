<?php
/**
 * Realiza la creación de un modelo
 * Created: 2021-11-19
 * Author: Gastón Fernandez
 */
	require_once(DIR_model."modelos".DS."class.modelos.inc.php");
	require_once(DIR_model."marcas".DS."class.marcas.inc.php");
	require_once(DIR_includes."class.proccessImages.inc.php");
	$modelos = new cModelos;
	$marcas = new cMarcas;

	$reg = array();
	$nombre = trim($ws->CutParam(['nombre','name']));
	if(empty($nombre)){
		cLogging::Write(__FILE__." ".__LINE__." El nombre del modelo esta vacío");
		return $ws->SendResponse(400,null,220);
	}
	$reg['nombre'] = $nombre;

	$marca_id = $ws->CutParam(['id','marca','marca_id']);
	if(is_null(SecureInt($marca_id))){
		cLogging::Write(__FILE__ ." ".__LINE__ ." No se indico el ID de la marca");
		return $ws->SendResponse(400,null,221);
	}

	if(!$marcas->Get($marca_id)){
		cLogging::Write(__FILE__ ." ".__LINE__ ." La marca con ID ".$marca_id." no fue encontrada");
		return $ws->SendResponse(404,null,215);
	}
	$reg['marca_id'] = $marca_id;

	$descripcion = trim($ws->CutParam(['descripcion','description']));
	if(!empty($descripcion)){
		$reg['descripcion'] = $descripcion;
	}

	$estado = strtoupper($ws->CutParam(['estado']));
	if(!empty($estado) AND is_string($estado) AND isset(ESTADOS_VALIDOS[$estado])){
		$reg['estado'] = $estado;
	}
	
	//Obtenemos la imagen si es que la hay
	$imagen_nombre = $ws->CutParam(['imagen_nombre','image_name','imagen']);//Un nombre o una URL
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

	if(!$modelos->Crear($reg)){
		cLogging::Write(__FILE__ ." ".__LINE__." No se pudo crear el modelo");
		return $ws->SendResponse(500,null,222);
	}
	
	if(!empty($imagen_nombre) AND !empty($imagen_data)){
		$result = cImages::CreateImage($imagen_nombre,$imagen_data,"modelos/".$modelos->last_id);
		if(!CanUseArray($result)){
			cLogging::Write(__FILE__." ".__LINE__." No se pudo guardar la imagen: ".cImages::$msgErr."\nDataErr:".(CanUseArray(cImages::$dataErr))? print_r(cImages::$dataErr):"");
		}else{
			$reg['data']['imagen_file'] = $result['archivo'];
			$reg['data']['imagen_nombre'] = $result['nombre'];	
			$modelos->Get($modelos->last_id);
			$modelos->Editar(["data"=>$reg['data']]);
		}			
	}

	$ws->SendResponse(201,$modelos->last_id);