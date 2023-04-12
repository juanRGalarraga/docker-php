<?php
/**
 * Subida de archivos en la biblioteca de personas
 * Created: 2021-11-10
 * Author: Gastón Fernandez
 */
	define('KB', 1024);
	define('MB', 1048576);
	define('GB', 1073741824);
	define('TB', 1099511627776);
	require_once(DIR_model."biblioteca".DS."class.biblioteca.inc.php");
	$biblioteca = new cBiblioteca;

	$id = SecureInt(FindParam("pid,persona_id,id"));
	if(is_null($id)){
		cLogging::Write(__FILE__." ".__LINE__." El ID de la persona no es un número entero válido");
		return EmitJSON(["swerr"=>"No se pudo realizar la subida del archivo"]);
	}

	$folder = FindParam("folder,ruta,carpeta");

	if(!isset($_FILES['uploadFile'])){
		cLogging::Write(__FILE__." ".__LINE__." Key no seteada en array de archivos");
		return EmitJSON(["swerr"=>"No se pudo realizar la subida del archivo"]);
	}

	if(!CanUseArray($_FILES['uploadFile'])){
		cLogging::Write(__FILE__." ".__LINE__." Datos de archivo vacíos");
		return EmitJSON(["swerr"=>"No se pudo realizar la subida del archivo"]);
	}

	$nombre = $_FILES['uploadFile']['name'] ?? null;
	if(empty($nombre)){
		cLogging::Write(__FILE__." ".__LINE__." El nombre del archivo esta vacío");
		return EmitJSON(["swerr"=>"El nombre del archivo no puede estar vacío"]);
	}

	$size = $_FILES['uploadFile']['size'] ?? null;
	if(empty($size)){
		cLogging::Write(__FILE__." ".__LINE__." El tamaño del archivo no fue encontrado");
		return EmitJSON(["swerr"=>"No pudimos determinar el tamaño del archivo"]);
	}

	if($size > 3*MB){
		cLogging::Write(__FILE__." ".__LINE__." El tamaño del archivo supera el máximo permitido con ".($size/MB));
		return EmitJSON(["swerr"=>"El tamaño máximo de archivo permitido es de 3 MB"]);
	}

	$data = $_FILES['uploadFile']['tmp_name'] ?? null;
	if(empty($nombre)){
		cLogging::Write(__FILE__." ".__LINE__." No se encontro la ubicación del archivo temporal");
		return EmitJSON(["swerr"=>"No se pudo realizar la subida del archivo"]);
	}

	if(!$data = file_get_contents($data)){
		cLogging::Write(__FILE__." ".__LINE__." No se pudo leer el archivo a subir");
		return EmitJSON(["swerr"=>"No se pudo leer el archivo dado"]);
	}

	$data = base64_encode($data);
	$archivo = array(
		"ruta" => $folder,
		"nombre" => $nombre,
		"data" => $data
	);

	if(!$biblioteca->UploadFile($id,$archivo)){
		cLogging::Write(__FILE__." ".__LINE__." No se pudo subir el archivo");
		return EmitJSON(["swerr"=>"No se pudo realizar la subida del archivo"]);
	}

	ResponseOk(["id"=>$id,"folder"=>$folder]);