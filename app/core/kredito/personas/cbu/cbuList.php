<?php
/**
*	Obtiene la lista de cuentas CBU's y/o alias de una persona
*	Created: 2021-11-04
*	Author: Gastón Fernandez
*/
	require_once(DIR_model."personas".DS."class.personas.inc.php");
	require_once(DIR_model."personas".DS."class.personasData.inc.php");
	require_once(DIR_model."bancos".DS."class.bancos.inc.php");
	$persona = new cPersonas;
	$personaData = new cPersonasData;
	$bancos = new cBancos;
	
	$id = SecureInt($ws->CutParam(["id","persona_id"]));
	if(is_null($id)){
		cLogging::Write(__FILE__." ".__LINE__." El ID de la persona de la cual se obtendrán los datos no fue indicadó");
		return $ws->SendResponse(400,null,10);
	}
	
	if(!$persona->Get($id)){
		cLogging::Write(__FILE__." ".__LINE__." La persona con ID ".$id." no fue encontrada");
		return $ws->SendResponse(404,null,160);
	}
	
	$cbuList = $personaData->GetAll($id,"cbu");
	$cvuList = $personaData->GetAll($id,"cvu");
	$aliasList = $personaData->GetAll($id,"alias");
	$result = array_merge($cbuList,$cvuList,$aliasList);
	
	if(!CanUseArray($result)){
		cLogging::Write(__FILE__." ".__LINE__." No se pudieron conseguir resultados con los datos bancarios de la persona con ID ".$id);
		return $ws->SendResponse(404,null,161);
	}
	
	foreach($result as $key => $value){
		$cbu = ($value->tipo == 'CBU')? $value->valor:null;
		if($value->tipo != 'CBU'){ $cbu = (isset($value->extras->cbu) OR isset($value->extras->CBU))? $value->extras->cbu ?? $value->extras->CBU:null; }
		if(empty($cbu)){ continue; }
		if(!$bancos->GetByCbu($cbu)){ continue; }
		$result[$key]->banco = $bancos->nombre;
	}
	
	$ws->SendResponse(200,$result);