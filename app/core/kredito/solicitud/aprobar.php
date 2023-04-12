<?php
/**
 * Realiza la aprobación de una solicitud
 * Created: 2021-09-08
 * Author: Gastón Fernandez
 */
	require_once(DIR_model."solicitudes". DS ."class.aprSolicitud.inc.php");
	$solicitud = new cAprSolicitud;

	$id = SecureInt($ws->GetParam("id"));
	if(is_null($id)){
        cLogging::Write(__FILE__ ." ".__LINE__ ." El ID de la solicitud no fue indicado");
		$ws->SendResponse(400,"Debes indicar el ID de la solicitud",10); return;
    }

	if(!$solicitud->Get($id)){
		cLogging::Write(__FILE__ ." ".__LINE__ ." La solicitud con ID ".$id." no fue encontrada");
		$ws->SendResponse(404,"La solicitud indicada no fue encontrada",13); return;
	}

	if($solicitud->estado_solicitud != 'PEND'){
		cLogging::Write(__FILE__ ." ".__LINE__ ." La solicitud con ID ".$id." ya ha sido procesada");
		$ws->SendResponse(406,null,150); return;
	}

	//Ahora comprobamos que haya campos suficientes ya sea para crear una persona, o 
	//para asociar esta solicitud con una persona
	if(!$solicitud->VerifyData()){
		$data = (CanUseArray($solicitud->dataError))? $solicitud->dataError:null;
        if($solicitud->rechazar){ // Si fue por rechazo, paramos acá.
			cLogging::Write(__FILE__ ." ".__LINE__ ." La solicitud ".$id." fue rechazada debido a que uno o más de sus datos estaban en la lista de exclusiones: ".print_r($data,true));
			$ws->SendResponse(403,$data,60); return;
		}
        cLogging::Write(__FILE__ ." ".__LINE__ ." No se pudo procesar la solicitud ".$id);
        $ws->SendResponse(406,$data,142); return;
	}

	//Ahora el paso siguiente es crear el cliente, si es que se lo necesita
    if($solicitud->CrearPersona){
        if(!$solicitud->CrearPersona()){
            cLogging::Write(__FILE__ ." ".__LINE__ ." No se pudo realizar la creación de la persona para la solicitud ID ".$id);
            $ws->SendResponse(500,null,151); return;
        }
    }

	if(!$solicitud->Aprobar()){
		cLogging::Write(__FILE__ ." ".__LINE__ ." No se pudo realizar la aprobación de la solicitud ID ".$id);
		$ws->SendResponse(500,null,142); return;
	}

	if (!$sysParams->Get("transfer_auto", false)) {
		// Transferencia automática desactivada. Paramos acá.
		return $ws->SendResponse(200, $solicitud->prestamo_id, 'La solicitud fue aprobada con exito');
	}
	
	require_once(DIR_model."transferencia".DS."class.transferencias.inc.php");
	$transferencia = new cTransferencia;

	if (!$transferencia->SetSolicitud($solicitud)) {
		return $ws->SendResponse(500, $solicitud->prestamo_id, 'Precondiciones no se cumplieron');
	}
	if (!$transferencia->SetTransfData($sysParams->Get("desc_transfer", "Transferencia de prestamo"))) {
		return $ws->SendResponse(500, $solicitud->prestamo_id, 'Transferencia incompleta');
	}
	if (!$transferencia->Execute()) {
		return $ws->SendResponse(500, $solicitud->prestamo_id, 'Transferencia no ejecutada');
	}
	$ws->SendResponse(200, $solicitud->prestamo_id, 'Transferencia completada');

