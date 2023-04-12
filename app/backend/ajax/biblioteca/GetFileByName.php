<?php
/**
 * Obtiene un archivo para descargar a partir del ID del cliente/persona, nombre del archivo y si se indica el nombre de la carpeta, descarga 1 o más archivos
 * Created: 2021-11-09
 * Author: Gastón Fernandez
 */
	require_once(DIR_model."biblioteca".DS."class.biblioteca.inc.php");
	$biblioteca = new cBiblioteca;
	$reg = array();

	$id = SecureInt(FindParam("id,pid,persona_id"));
	if(is_null($id)){ 
		cLogging::Write(__FILE__." ".__LINE__." No se indicó el ID de la persona.");
		return EmitJSON(["swerr"=>"No se pudo obtener el archivo"]);
	}

	$archivos = FindParam("archivos,files");
	if(empty($archivos)){
		cLogging::Write(__FILE__." ".__LINE__." No se indicaron los archivos a descargar");
		return EmitJSON(["swerr"=>"No se pudo obtener el archivo"]);
	}

	$archivos = explode(",",$archivos);
	if(!CanUseArray($archivos)){
		cLogging::Write(__FILE__." ".__LINE__." No se indicaron los archivos a descargar (array)");
		return EmitJSON(["swerr"=>"No se pudo obtener el archivo"]);
	}

	$ruta = $archivos[0];
	$ruta = explode("/",$ruta);
	array_pop($ruta);
	$ruta = implode("/",$ruta);

	$archivos = array_map(function($val){
		global $ruta;
		$val = str_replace([$ruta,"/"],"",$val);
		return $val;
	},$archivos);

	$reg['files'] = $archivos;
	if(!empty($ruta)){
		$reg['dir']	= $ruta;
	}

	if(!$filesData = $biblioteca->GetFileByName($id,$reg) OR !is_array($filesData)){
		cLogging::Write(__FILE__." ".__LINE__." No se pudo obtener el archivo indicadó de la persona ".$id);
		return EmitJSON(["swerr"=>"No se pudo obtener el archivo"]);
	}

	ResponseOk(["files"=>$filesData]);