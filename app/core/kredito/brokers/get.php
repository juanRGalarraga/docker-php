<?php
/**
*	Obtiene un broker dado su ID
*	Created: 2021-11-08
*	Author: Gastón Fernandez
*/
	require_once(DIR_model."brokers".DS."class.brokers.inc.php");
	$brokers = new cBrokers;
	
	if(!$id = SecureInt($ws->GetParam("id"))){
		cLogging::Write(__FILE__." ".__LINE__." No se indico el ID del broker a buscar");
        return $ws->SendResponse(400,'No se indico el broker a buscar',10);
    }   

    if(!$info = $brokers->Get($id)){
		cLogging::Write(__FILE__." ".__LINE__." El broker con ID ".$id." no fue encontrado");
        return $ws->SendResponse(404,'El broker indicado no fue encontrado',14);
    };

    $ws->SendResponse(200,$info);