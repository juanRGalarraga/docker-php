<?php
/**
 * Dado el ID de un archivo lo busca en la biblioteca adecuada y devuelve un objeto con su base64, su mime type y su nombre
 * Created: 2021-11-09
 * Author: Gastón Fernandez
*/

	require_once(DIR_model."biblioteca".DS."class.biblioteca.inc.php");
	$biblioteca = new cBiblioteca;
  
	$persona_id = SecureInt($ws->GetParam(["id"]));
	if(is_null($persona_id)){
		cLogging::Write(__FILE__." ".__LINE__." No se indicó el ID de la persona");
		return $ws->SendResponse(400,null,10);
	}
	
	if(!$biblioteca->GetByPersona($persona_id)){
		cLogging::Write(__FILE__." ".__LINE__." No se encontro la biblioteca de la persona ".$persona_id);
		return $ws->SendResponse(404,null,190);
	}
	
	$archivo = $ws->GetParam(["archivo","file","archivos","files"]);
	if(empty($archivo)){
		cLogging::Write(__FILE__." ".__LINE__." No se indicó el archivo que se buscara");
		return $ws->SendResponse(400,null,10);
	}
	
	$targetFolder = EnsureTailingSlash(DIR_biblioteca).$biblioteca->nombre.DS;
	$dir = $ws->GetParam(["carpeta","dir"]);
	if(!empty($dir)){
		$dir = ($dir[0] == "/" OR $dir[0] == "\\")? mb_substr($dir,1):$dir;
		$targetFolder .= $dir;
		$targetFolder = str_replace(["/","\\"],DS,$targetFolder);
	}
	
	if(!is_array($archivo)){
		$archivo = array($archivo);
	}
	
	$fileData = array();
	$zipName = str_replace([" ",":"],["_","-"],cFechas::Ahora());
	$zipName .= "_files.zip";
	$zipFolder = $targetFolder.DS.$zipName;
	$zip = new ZipArchive;
	$zipCreated = $zip->open($zipFolder, ZipArchive::CREATE);
	
	foreach($archivo as $value){
		if(!$file = FindFile($targetFolder,$value)){
			cLogging::Write(__FILE__." ".__LINE__." No se pudo encontrar el archivo indicadó");
			return $ws->SendResponse(400,null,192);
		}
		
		if(count($archivo) > 1 AND $zipCreated){
			//Multiples archivos.. genero un zip
			$zip->addFile($file,$value);
			continue;
		}

		if(!$fileInfo = file_get_contents($file)){
			cLogging::Write(__FILE__." ".__LINE__." El archivo con ID ".$id." no pudo ser encontrado");
			return $ws->SendResponse(500,null,194);
		}

		$fileData[] = array(
			'name' => $value,
			'mime' => mime_content_type($file),
			'data' => base64_encode($fileInfo)
		);
	}

	if($zipCreated){
		$zip->close();
		if(ExisteArchivo($zipFolder)){
			chmod($zipFolder,0777);
			$fileInfo = file_get_contents($zipFolder);
			$fileData[] = array(
				'name' => $zipName,
				'mime' => mime_content_type($zipFolder),
				'data' => base64_encode($fileInfo)
			);
			unlink($zipFolder);			
		}
	}
	$ws->SendResponse(200,$fileData);