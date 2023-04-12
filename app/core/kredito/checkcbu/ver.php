<?php
    /**
     * Obtiene datos del titular de un CBU registrado en la base de datos.
     * Created: 2021-10-04
     * Author: DriverOp
     */

	require_once(DIR_model."personas".DS."class.personasData.inc.php");
	require_once(DIR_model."personas".DS."class.personas.inc.php");
	
	$cbu = $ws->GetParams(['cbu']);
	if (empty($cbu)) {
		$ws->SendResponse(400, 'No se indicó CBU'); return;
	}
	
	
	
	$personasData = new cPersonasData;
	
	if (!$salida = $personasData->GetByTipo('CBU', substr($cbu,0,22))) {
		$ws->SendResponse(404, 'CBU no encontrado.'); return;
	}
	
	if ($personasData->persona_id) {
		$persona = new cPersonas();
		try {
			$persona->Get($personasData->persona_id);
			
			$salida->nombre = $persona->nombre;
			$salida->apellido = $persona->apellido;
			$salida->tipo_doc = $persona->tipo_doc;
			$salida->nro_doc = $persona->nro_doc;
			$salida->email = $persona->email;
			$salida->fecha_nac = $persona->fecha_nac;
			$salida->fecha_nac = $persona->fecha_nac;
			$salida->dir = $persona->dir;
			$salida->data = $persona->data;
			
		} finally {
			$persona = null;
		}
	}
	
	
	
	
	$ws->SendResponse(200, $salida); return;