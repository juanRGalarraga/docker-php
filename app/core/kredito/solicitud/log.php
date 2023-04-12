<?php


	require_once(DIR_model."solicitudes". DS ."class.solicitudesLogs.inc.php");

	$solicitudLog = new cSolicitudesLogs;
	
	$id = $ws->CutParam(['id','solic_id','solicid']);
	unset($ws->params[BASE_VPATH]);

	$solicitudLog->solicitud_id = $id;
	
	$solicitudLog->paso = $ws->CutParam(['paso_alias','paso']);

	$solicitudLog->data = $ws->params;
	$solicitudLog->Crear([]);
