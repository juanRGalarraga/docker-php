<?php
/**
 * Realiza la creación de un archivo a la biblioteca de un cliente/persona
 * Created: 2021-11-10
 * Author: Gastón Fernandez
 */
	 
	require_once(DIR_model."personas".DS."class.personas.inc.php");
	require_once(DIR_model."biblioteca".DS."class.biblioteca.inc.php");
	$personas = new cPersonas;
	$biblioteca = new cBiblioteca;
  
	$persona_id = SecureInt($ws->GetParam(["id","pid","persona_id"]));
	if(is_null($persona_id)){
		cLogging::Write(__FILE__." ".__LINE__." No se indicó el ID de la persona");
		return $ws->SendResponse(400,null,10);
	}
	
	if(!$personas->Get($persona_id)){
		cLogging::Write(__FILE__." ".__LINE__." No se encontro a la persona con ID ".$persona_id);
		return $ws->SendResponse(404,null,160);
	}
	
	if(!$biblioteca->GetByPersona($persona_id)){
		cLogging::Write(__FILE__." ".__LINE__." No se encontro la biblioteca para la persona ".$persona_id);
		return $ws->SendResponse(404,null,190);
	}
	
	//Carpeta donde se subira el archivo...
	$targetFolder = DIR_biblioteca.$biblioteca->nombre.DS;
	$dir = $ws->GetParam(["ruta","carpeta","folder"]);
	if(!empty($dir)){
		$dir = ($dir[0] == "/" OR $dir[0] == "\\")? mb_substr($dir,1):$dir;
		$targetFolder .= $dir;
		$targetFolder = str_replace(["/","\\"],DS,$targetFolder);
	}
	$targetFolder = EnsureTailingSlash($targetFolder);
	
	//Obtenemos los datos del archivo.
	$nombre = $ws->GetParam(["nombre","name","filename"]);
	if(empty($nombre)){
		cLogging::Write(__FILE__." ".__LINE__." Debes indicar el nombre del archivo a subir");
		return $ws->SendResponse(400,null,200);
	}
	
	$nombre = str_replace([" ",":"],["_","-"],cFechas::Ahora())."_".$nombre;
	
	$data = $ws->GetParam(["file","data","filedata"]);
	if(empty($data)){
		cLogging::Write(__FILE__." ".__LINE__." La data del archivo a subir esta vacía");
		return $ws->SendResponse(400,null,201);
	}
	
	if(!ExisteCarpeta($targetFolder)){
		cLogging::Write(__FILE__." ".__LINE__." La carpeta donde se indicó la subida del archivo no existe");
		return $ws->SendResponse(404,null,198);
	}
	
	$data = base64_decode($data);
	if(!file_put_contents($targetFolder.$nombre,$data)){
		cLogging::Write(__FILE__." ".__LINE__." No se pudo realizar la creación del archivo");
		return $ws->SendResponse(500,null,199);
	}
	
	$biblioteca->AddFile($nombre);//Agregamos el archivo a la biblioteca, si no se puede realizar, realmente no importa.
	
	$ws->SendResponse(201,true);