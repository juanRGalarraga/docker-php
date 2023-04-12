<?php
/**
 * Edita un modelo ya existente
 * Created: 2021-11-19
 * Author: Gastón Fernandez
 */
	require_once(DIR_model."modelos".DS."class.modelos.inc.php");
	require_once(DIR_model."marcas".DS."class.marcas.inc.php");
	require_once(DIR_includes."class.proccessImages.inc.php");
	$modelos = new cModelos;
	$marcas = new cMarcas;

	$reg = array();
	$id = $ws->CutParam(['id','modelo','modelo_id']);
	if(is_null(SecureInt($id))){
		cLogging::Write(__FILE__ ." ".__LINE__ ." No se indico el ID del modelo a editar");
		return $ws->SendResponse(400,null,223);
	}

	if(!$modelos->Get($id)){
		cLogging::Write(__FILE__ ." ".__LINE__ ." El modelo con ID ".$id." no fue encontrado");
		return $ws->SendResponse(404,null,226);
	}

	$marca_id = $ws->CutParam(['id','marca','marca_id']);
	if(!is_null(SecureInt($marca_id))){
		if(!$marcas->Get($marca_id)){
			cLogging::Write(__FILE__ ." ".__LINE__ ." La marca con ID ".$marca_id." no fue encontrada");
			return $ws->SendResponse(404,null,215);
		}
		$reg['marca_id'] = $marca_id;
	}

	$nombre = trim($ws->CutParam(['nombre','name']));
	if(!empty($nombre)){
		$reg['nombre'] = $nombre;
	}

	$descripcion = trim($ws->CutParam(['descripcion','description']));
	if(!empty($descripcion)){
		$reg['descripcion'] = $descripcion;
	}

	$estado = strtoupper($ws->CutParam(['estado']));
	if(!empty($estado) AND is_string($estado) AND isset(ESTADOS_VALIDOS[$estado])){
		$reg['estado'] = $estado;
	}
	
	$reg['data'] = (!empty($modelos->data))? (array)$modelos->data:array();
	//Obtenemos la imagen si es que la hay
	$imagen_nombre = $ws->CutParam(['imagen_nombre','image_name']);
	$imagen_data = $ws->CutParam(['imagen_data','image_data']);//base64
	if(!empty($imagen_nombre) AND !empty($imagen_data)){
		$result = cImages::CreateImage($imagen_nombre,$imagen_data,"modelos/".$modelos->id);
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
		cLogging::Write(__FILE__ ." ".__LINE__." No se indicaron datos con los que editar el modelo");
		return $ws->SendResponse(400,null,224);
	}

	if(!$modelos->Editar($reg)){
		cLogging::Write(__FILE__ ." ".__LINE__." No se pudo editar el modelo con ID ".$id);
		return $ws->SendResponse(500,null,225);
	}
	
	$ws->SendResponse(201,true);