<?php
/**
 * Crea una nueva carpeta para la bilbioteca de una persona
 * Created: 2021-11-09
 * Author: Gastón Fernandez
 */
	require_once(DIR_model."biblioteca".DS."class.biblioteca.inc.php");
	$biblioteca = new cBiblioteca;
	$reg = array();

	$id = FindParam("id");
	if(is_null(SecureInt($id))){
		cLogging::Write(__FILE__." ".__LINE__." No se indicó el Id de la persona");
		return EmitJSON(["swerr"=>"No se pudo crear la carpeta"]);
	}

	$newFolder = trim(FindParam("name"));
	if(empty($newFolder)){
		cLogging::Write(__FILE__." ".__LINE__." No se indicó el nombre de la carpeta a crear");
		return EmitJSON(["swerr"=>"No se pudo crear la carpeta"]);
	}


	$target = FindParam("folder");
	if(!empty($target)){
		$reg['dir'] = $target;
	}

	$reg['folder'] = $newFolder;
	if(!$result = $biblioteca->CreateFolder($id,$reg)){
		cLogging::Write(__FILE__." ".__LINE__." No se indicó el nombre de la carpeta a crear");
		return EmitJSON(["swerr"=>"No se pudo crear la carpeta"]);
	}

	if(isset($result->folder) AND $result->folder !== true){
		cLogging::Write(__FILE__." ".__LINE__." No se indicó el nombre de la carpeta a crear");
		return EmitJSON(["swerr"=>$result->folder]);
	}

	ResponseOk();