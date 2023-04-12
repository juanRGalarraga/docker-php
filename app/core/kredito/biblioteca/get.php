<?php
/**
 * Dado el ID de un archivo lo busca en la biblioteca adecuada y devuelve un objeto con su base64, su mime type y su nombre
 * Created: 2021-11-09
 * Author: Gastón Fernandez
*/

	require_once(DIR_model."biblioteca".DS."class.biblioteca.inc.php");
	$biblioteca = new cBiblioteca;
  
	$id = SecureInt($ws->GetParam(["id"]));
	if(is_null($id)){
		cLogging::Write(__FILE__." ".__LINE__." No se indicó el ID del archivo");
		return $ws->SendResponse(400,null,10);
	}
	
	if(!$biblioteca->getDataArchivo($id)){
		cLogging::Write(__FILE__." ".__LINE__." El archivo con ID ".$id." no fue encontrado");
		return $ws->SendResponse(404,null,192);
	}
	
	$fileName = $biblioteca->nombre;
	$biblioteca_id = $biblioteca->biblioteca_id;
	if(!$biblioteca->Get($biblioteca_id)){
		cLogging::Write(__FILE__." ".__LINE__." La biblioteca ".$biblioteca_id." no fue encontrada para el archivo ".$id);
		return $ws->SendResponse(404,null,190);
	}
	
	$dir = $biblioteca->directorio ?? null;
	if(empty($dir)){
		cLogging::Write(__FILE__." ".__LINE__." La biblioteca encontrada no tiene directorio inidcadó");
		return $ws->SendResponse(404,null,191,"Biblioteca sin directorio de archivos");
	}
	
	$dir = DIR_biblioteca.$dir;
	if(!ExisteCarpeta($dir)){
		cLogging::Write(__FILE__." ".__LINE__." La biblioteca ".$biblioteca_id." no tiene un directorio físico");
		return $ws->SendResponse(404,null,191);
	}
	
	if(!$file = FindFile($dir,$fileName)){
		cLogging::Write(__FILE__." ".__LINE__." El archivo con ID ".$id." no pudo ser encontrado");
		return $ws->SendResponse(404,null,193);
	}
	
	
	if(!$fileInfo = file_get_contents($file)){
		cLogging::Write(__FILE__." ".__LINE__." El archivo con ID ".$id." no pudo ser encontrado");
		return $ws->SendResponse(500,null,194);
	}
	
	$fileData = array(
		'name' => $fileName,
		'mime' => mime_content_type($file),
		'data' => base64_encode($fileInfo)
	);
	$ws->SendResponse(200,$fileData);