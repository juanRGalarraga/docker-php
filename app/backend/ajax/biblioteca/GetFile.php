<?php
/**
 * Recibe el ID de un archivo y devuelve su base64, su tipo y su nombre
 * Created: 2021-11-09
 * Author: Gastón Fernandez
 */
	require_once(DIR_model."biblioteca".DS."class.biblioteca.inc.php");
	$biblioteca = new cBiblioteca;

	$id = SecureInt(FindParam("id,file_id,file"));
	if(is_null($id)){ 
		cLogging::Write(__FILE__." ".__LINE__." No se indicó el ID del archivo.");
		return EmitJSON(["swerr"=>"No se pudo obtener el archivo"]);
	}

	if(!$fileData = $biblioteca->GetFile($id)){ 
		cLogging::Write(__FILE__." ".__LINE__." No se pudo encontrar el archivo con ID ".$id);
		return EmitJSON(["swerr"=>"No se pudo obtener el archivo"]);
	}

	ResponseOk($fileData);