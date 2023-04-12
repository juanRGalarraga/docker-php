<?php
/**
*	Obtiene los datos asociados a una persona
*	Created: 2021-11-04
*	Author: Gastón Fernandez
*/

	require_once(DIR_model."personas".DS."class.personas.inc.php");
	require_once(DIR_model."personas".DS."class.personasData.inc.php");
	$persona = new cPersonas;
	$personaData = new cPersonasData;
	
	$id = SecureInt($ws->CutParam(["id","persona_id"]));
	if(is_null($id)){
		cLogging::Write(__FILE__." ".__LINE__." El ID de la persona de la cual se obtendrán los datos no fue indicadó");
		return $ws->SendResponse(400,null,10);
	}
	
	if(!$persona->Get($id)){
		cLogging::Write(__FILE__." ".__LINE__." La persona con ID ".$id." no fue encontrada");
		return $ws->SendResponse(404,null,160);
	}
	
	$tipo = $ws->CutParam(["type","tipo"]);
	
	if(!$result = $personaData->GetAll($id,$tipo)){
		cLogging::Write(__FILE__." ".__LINE__." No se pudieron conseguir resultados con los datos de la persona con ID ".$id);
		return $ws->SendResponse(404,null,161);
	}
	
	$ws->SendResponse(200,$result);