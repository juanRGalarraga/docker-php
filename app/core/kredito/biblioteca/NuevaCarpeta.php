<?php
/**
*	Crea una nueva carpeta a una persona
*	Created: 2021-11-09
*	Author: Gastón Fernandez
*/
	require_once(DIR_model."personas".DS."class.personas.inc.php");
	require_once(DIR_model."biblioteca".DS."class.biblioteca.inc.php");
	$personas = new cPersonas;
	$biblioteca = new cBiblioteca;
	
	$persona_id = $ws->GetParam(["persona_id","id","pid"]);
	if(is_null($persona_id)){
		cLogging::Write(__FILE__." ".__LINE__." No se indicó el Id de la persona");
		return $ws->SendResponse(400,null,10);
	}
	
	if(!$personas->Get($persona_id)){
		cLogging::Write(__FILE__." ".__LINE__." La persona con ID ".$persona_id." no fue encontrada");
		return $ws->SendResponse(404,null,160);
	}
	
	if(!$biblioteca->GetByPersona($persona_id)){
		cLogging::Write(__FILE__." ".__LINE__." La biblioteca de la persona ".$persona_id." no fue encontrada");
		return $ws->SendResponse(404,null,190);
	}
	
	$newFolder = trim($ws->GetParam(["folder","carpeta","nueva"]));
	if(empty($newFolder)){
		cLogging::Write(__FILE__." ".__LINE__." No se indicó la carpeta a crear");
		return $ws->SendResponse(400,null,195);
	}
	
	EchoLog($newFolder);
	if(!preg_match("/^[a-z0-9_\-\s]+$/i",$newFolder)){
		cLogging::Write(__FILE__." ".__LINE__." El nombre de la carpeta ".$newFolder." no es válido");
		return $ws->SendResponse(406,["folder"=>"El nombre de la carpeta solo puede contene: números, letras, -, _ y espacios"],196);
	}
	$newFolder = preg_replace("/\s[\s]+/"," ",$newFolder);
	
	$targetFolder = EnsureTailingSlash(DIR_biblioteca).$biblioteca->nombre.DS;
	$dir = $ws->GetParam(["to","where","dir"]);
	if(!empty($dir)){
		$dir = ($dir[0] == "/" OR $dir[0] == "\\")? mb_substr($dir,1):$dir;
		$targetFolder .= $dir;
		$targetFolder = str_replace(["/","\\"],DS,$targetFolder);
	}
	$targetFolder = EnsureTailingSlash($targetFolder);
	if(!ExisteCarpeta($targetFolder)){
		cLogging::Write(__FILE__." ".__LINE__." No se encontro la carpeta ".$targetFolder." donde crear la nueva carpeta");
		return $ws->SendResponse(406,null,198);
	}
	
	if(!mkdir($targetFolder.$newFolder)){
		cLogging::Write(__FILE__." ".__LINE__." No se pudo crear la carpeta ".$newFolder." en el directorio ".$targetFolder);
		return $ws->SendResponse(406,null,197);
	}
	
	$ws->SendResponse(201,["folder"=>true]);