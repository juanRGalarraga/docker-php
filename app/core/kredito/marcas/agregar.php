<?php
/**
 * Realiza la creación de una marca
 * Created: 2021-11-19
 * Author: Gastón Fernandez
 */
	require_once(DIR_model."marcas".DS."class.marcas.inc.php");
	require_once(DIR_includes."class.proccessImages.inc.php");
	$marcas = new cMarcas;

	$reg = array();
	$nombre = trim($ws->CutParam(['nombre','name']));
	if(empty($nombre)){
		cLogging::Write(__FILE__." ".__LINE__." El nombre de la marca esta vacío");
		return $ws->SendResponse(400,["msgerr"=>"El nombre de la marca no puede estar vacío"],210);
	}
	
	if($marcas->GetByName($nombre)){
		cLogging::Write(__FILE__." ".__LINE__." El nombre de la marca ya existe");
		return $ws->SendResponse(406,["msgerr"=>"La marca ya existe"],216);
	}
	
	$reg['nombre'] = $nombre;

	$descripcion = trim($ws->CutParam(['descripcion','description']));
	if(!empty($descripcion)){
		$reg['descripcion'] = $descripcion;
	}

	$estado = strtoupper($ws->CutParam(['estado']));
	if(!empty($estado) AND is_string($estado) AND isset(ESTADOS_VALIDOS[$estado])){
		$reg['estado'] = $estado;
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

	if(!$marcas->Crear($reg)){
		cLogging::Write(__FILE__ ." ".__LINE__." No se pudo crear la marca");
		return $ws->SendResponse(500,null,211);
	}
	
	if(!empty($imagen_nombre) AND !empty($imagen_data)){
		$result = cImages::CreateImage($imagen_nombre,$imagen_data,"marcas/".$marcas->last_id);
		if(!CanUseArray($result)){
			cLogging::Write(__FILE__." ".__LINE__." No se pudo guardar la imagen: ".cImages::$msgErr."\nDataErr:".(CanUseArray(cImages::$dataErr))? print_r(cImages::$dataErr):"");
		}else{
			$reg['data']['imagen_file'] = $result['archivo'];
			$reg['data']['imagen_nombre'] = $result['nombre'];	
			$marcas->Get($marcas->last_id);
			$marcas->Editar(["data"=>$reg['data']]);
		}			
	}

	$ws->SendResponse(201,$marcas->last_id);