<?php
/**
 * Modifica una solicitud existente
 * Created: 2021-09-08
 * Author: Gastón Fernandez
 */
    require_once("validador.php");
	require_once("cotizacion.php");
    require_once(DIR_model. "solicitudes". DS ."class.modifSolicitud.inc.php");

	$respuesta = new stdClass; // Esto es lo que se manda como respuesta al cliente de la API.
	$solicitud = new cModifSolicitud;

    $id = SecureInt($ws->GetParam("id"));
    if(is_null($id)){
        cLogging::Write(__FILE__ ." ".__LINE__ ." El ID de la solicitud no fue indicado");
		return $ws->SendResponse(400,"Debes indicar el ID de la solicitud",10);
    }

    if(!$data = $solicitud->Get($id)){
		cLogging::Write(__FILE__ ." ".__LINE__ ." La solicitud con ID ".$id." no fue encontrada");
		return $ws->SendResponse(404,"La solicitud indicada no fue encontrada",13);
	}
	
	$respuesta->id = $id;


    if($solicitud->estado_solicitud != 'PEND'){
        cLogging::Write(__FILE__ ." ".__LINE__ ." La solicitud con ID ".$id." ya ha sido procesada");
		return $ws->SendResponse(406,null,150);
    }

    $parametros = $ws->params;
    unset($parametros['id']);//No lo necesitamos
    unset($parametros['_virualpath_']);//No lo necesitamos
    if(!CanUseArray($parametros)){
        cLogging::Write(__FILE__ ." ".__LINE__ ." No llegaron datos a modificar para la solicitud ".$id);
		return $ws->SendResponse(400,null,149);
    }
    
    if(is_object($data)){
        $data = (array)$data;
    }
	
    
    $cambios = false;//Bandera para saber si hubo o no cambios
    //Asignación de los datos
    $datosNuevos = array();
    foreach($data as $key => $value){
        if($key == 'data'){ continue; }
        //Si existe en parametros y su valor es distinto, de lo contrario no me interesa nada más
        if(isset($parametros[$key])){
            if($value != $parametros[$key]){
                $datosNuevos[$key] = $parametros[$key];
                $cambios = true;
            }
            unset($parametros[$key]);//Removemos lo ya asignado...
        }
    }

    //No importa si ya existe o no el campo data, siempre lo voy a setear
    $extraData = (array)($data['data'] ?? array());
    foreach($parametros as $key => $value){
        $extraData[$key] = $parametros[$key];
        $cambios = true;
    }

    //Si no tengo nada para actualizar no sigo
    if(!$cambios){
        cLogging::Write(__FILE__ ." ".__LINE__ ." No llegaron datos a modificar para la solicitud ".$id);
		return $ws->SendResponse(200,true); // La respuesta debe ser indistinguible de éxtio.
    }
    
    //Ahora procedemos a validar los nuevos datos
    if(!ValidateData($extraData)){ return; }
    $datosNuevos['data'] = $extraData;
    
	
    //Ahora realizo la modificación a la solicitud
    if(!$solicitud->Modificar($datosNuevos)){
        cLogging::Write(__FILE__ ." ".__LINE__ ." No se pudo modificar la solicitud");
        return $ws->SendResponse(500,null,141); // Esto es un error interno.
    }
	
	
    $data = null;
    if(CanUseArray($solicitud->dataError)){
        $data = CanUseArray($solicitud->dataError)? $solicitud->dataError:null;
        cLogging::Write(__FILE__ ." ".__LINE__ ." Se modifico la solicitud pero hubo algunos datos con problemas: ".print_r($data,true));
        return $ws->SendResponse(400,$data,11,"La solicitud fue modificada, pero algunos datos no fueron validados correctamente"); return;
    }
    if($solicitud->rechazar){
        $data = CanUseArray($solicitud->dataError)? $solicitud->dataError:null;
        cLogging::Write(__FILE__ ." ".__LINE__ ." Se modifico la solicitud pero uno de los datos se encuentra en la lista de bloqueos: ".print_r($data,true));
        return $ws->SendResponse(403,$data,60);
    }
	$respuesta->cotizacion = ActualizarCotizacion();
	

    return $ws->SendResponse(200,$respuesta);