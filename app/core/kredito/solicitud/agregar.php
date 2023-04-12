<?php
/**
 * Crea una nueva solicitud
 * Created: 2021-09-07
 * Author: Gastón Fernandez
 */
	require_once(DIR_model."solicitudes". DS ."class.nuevaSolicitud.inc.php");
	require_once(DIR_model."personas". DS ."class.personas.inc.php");
	require_once("validador.php");
	require_once("cotizacion.php");
	
	$solicitud = new cNuevaSolicitud;
	$respuesta = new stdClass; // Esto es para responderle al cliente de la API.
	
	
	$solicitud->checkBanList = $sysParams->Get('use_ban_list',false);
	$solicitud->banListMoment = strtolower($sysParams->Get('moment_ban_list','pre'));
	
	$personas = new cPersonas;

	$reg = array();
	$reg['data'] = array();
	$persona_id = SecureInt($ws->CutParam(["persona_id"]));
    $parametros = $ws->params;
    unset($parametros['_virualpath_']);//No lo necesitamos

	if(!is_null($persona_id)){//Me vino el ID de la persona?
		//Busco si la persona existe
		if(!$personas->Get($persona_id)){
			cLogging::Write(__FILE__ . " " . __LINE__ . " La persona ".$persona_id." no fue encontrada");
			return $ws->SendResponse(404, null, 160);
		}
        $reg['persona_id'] = $persona_id;
	}
	
	$origen = $ws->CutParam(['origen','origin']);
	if (!empty($origen)) {
		$reg['origen'] = strtoupper(substr(trim($origen), 0, 32));
	}
	
	$reg['ws_usuario_id'] = $ws->usuario->id ?? null;
	$reg['negocio_id'] = $ws->usuario->negocio_id ?? null;
    
    //En este punto todos los parametros presentes en $parametros iran al data
    if(CanUseArray($parametros)){
        foreach($parametros as $key => $value){
            $reg['data'][$key] = $value;
        }
    }
    
	$data = $reg['data'];
	//Si esto falla quiere decir que falló la validación de algún dato, la respuesta ya fue enviada
	if(!ValidateData($data)){ return; }

	//Colocamos los datos en la solicitud
	if(!$solicitud->SetData($reg)){
		cLogging::Write(__FILE__ . " " . __LINE__ . " No se pudo guardar los datos en el objeto");
		return $ws->SendResponse(500, null, 140);
	}
	//Verifico si alguno de los datos datos se encuentra bloqueado, rechazo la solicitud
	if($solicitud->rechazar){
		$data = CanUseArray($solicitud->dataError)? $solicitud->dataError:null;
		cLogging::Write(__FILE__ . " " . __LINE__ . " La solicitud no pudo ser creada debido a que uno o más datos se encuentran en la lista de exclusiones: ".print_r($data,true));
		return $ws->SendResponse(403, $data, 60);
	}

	//Ahora comprobamos los datos con los que crearemos la solicitud
	if(!$solicitud->CheckData()){
		$data = CanUseArray($solicitud->dataError)? $solicitud->dataError:null;
		cLogging::Write(__FILE__ . " " . __LINE__ . " La solicitud no pudo ser creada debido a que uno o más datos no cumplen los requisitos: ".print_r($data,true));
		return $ws->SendResponse(406, $data, 152);
	}

	//Si llegamos aquí quiere decir que tenemos todo bien
	if(!$id = $solicitud->Crear()){
		cLogging::Write(__FILE__ . " " . __LINE__ . " No se pudo crear la solicitud ".$solicitud->errorMsg);
		return $ws->SendResponse(500, null, 140);
	}
	

	$respuesta->id = $id;
	$respuesta->cotizacion = ActualizarCotizacion();
	
	$ws->SendResponse(200,$respuesta);