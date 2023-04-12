<?php

$id = $ws->GetParam('id');

$db = new cModels;

$sql = "SELECT * FROM ".TBL_solicitudes_log." WHERE `solicitud_id` = ".$id." ORDER BY `fechahora` DESC;";

$salida = array();

if ($fila = $db->FirstQuery($sql)) {
	do {
		unset($fila->fechahora_txtshort);
		unset($fila->fechahora_txt);
		unset($fila->solicitud_id);
		if (!empty($fila->data)) {
			$fila->data = json_decode($fila->data);
		}
		$salida[] = $fila;
	} while($fila = $db->Next());
}

$ws->SendResponse(200, $salida);