<?php
/**
 * Obtiene una persona dado un ID y devuelve sus datos
 * Created: 2021-09-13
 * Author: Gastón Fernandez
 */
	require_once(DIR_model."personas".DS."class.personas.inc.php");
	$personas = new cPersonas;

	$id = SecureInt($ws->GetParam("id"));

	if(!empty($id)){
		if($data = $personas->Get($id)){
			$data = TranslateField($data);
			$ws->SendResponse(200,$data); return;
		} else {
			cLogging::Write(__FILE__ ." ".__LINE__ ." La persona con ID ".$id." no fue encontrada");
			$ws->SendResponse(404,'Persona no encontrada',160); return;
			
		}
	}
	
	$nro_doc = $ws->GetParam(['nro_doc','nrodoc','dni']);
	if(!empty($nro_doc)){
		if($data = $personas->GetByDoc($nro_doc)){
			$data = TranslateField($data);
			$ws->SendResponse(200,$data); return;
		} else {
			cLogging::Write(__FILE__ ." ".__LINE__ ." La persona con número de documento ".$nro_doc." no fue encontrada");
			$ws->SendResponse(404,'Persona no encontrada',160); return;
			
		}
	}

	cLogging::Write(__FILE__ ." ".__LINE__ ." El Identificador de persona no fue indicado");
	$ws->SendResponse(400,"Debes Indicar el ID de la persona a buscar",10);




	


	