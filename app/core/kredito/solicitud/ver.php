<?php
/**
 * Obtiene una solicitud y devuelve sus datos
 * Created: 2021-09-08
 * Author: Gastón Fernandez
 */

	require_once(DIR_model."solicitudes". DS ."class.solicitud.inc.php");
	$solicitud = new cSolicitudBase;

	$id = SecureInt($ws->GetParam("id"));
	if(is_null($id)){
		cLogging::Write(__FILE__." ".__LINE__." El ID de la solicitud no fue indicado");
		$continue = $ws->SendResponse(400,null,10,"Debes indicar el ID de la solicitud"); return;
	}

	if(!$data = $solicitud->Get($id)){
		cLogging::Write(__FILE__." ".__LINE__." La solicitud con ID ".$id." no fue encontrada");
		$continue = $ws->SendResponse(404,null,13,"La solicitud indicada no fue encontrada"); return;
	}

	$continue = $ws->SendResponse(200,$data);