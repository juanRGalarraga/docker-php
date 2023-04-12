<?php

$term = trim($ws->params['term']??null);
if (empty($term)) {
	return $ws->SendResponse(400, 'Falta término de búsqueda.');
}

$term = preg_replace('/\s\s+/',' ',mb_strtolower($term));
$borrowers = !empty($ws->GetParam(['borr','borrowers','pres','prestamo']));

$salida = array(
	'header'=>array(),
	'list'=>array()
);

$db = new cModels;

try {
	
	$select = "SELECT `id`, `nombre`, `apellido`, `nro_doc`";
	$from = "FROM ".SQLQuote(TBL_personas);
	$where = "WHERE 1=1";
	$orderby = "ORDER BY CONCAT_WS(' ',`nombre`,`apellido`) ASC";
	$limit = "LIMIT 30;";

	$buscar = "AND (REGEXP_LIKE(CONCAT_WS(' ',`nombre`,`apellido`),'(^|[[:space:]])".$term."','i'))";

	$sql = implode(" ",[$select, $from, $where, $buscar, $orderby, $limit]);

	$res = $db->Query($sql, true);
	$salida['header']['cant'] = (int)$db->cantidad;
	$salida['header']['currentSearch'] = $term;
	if ($fila = $db->First($res)) {
		do {
			if ($borrowers) {
				$fila->prestamo_id = null;
				$sql = "SELECT `id` FROM ".SQLQuote(TBL_prestamos)." WHERE `persona_id` = $fila->id AND `estado` != 'CANC' ORDER BY `fechahora_emision` DESC LIMIT 1;";
				if ($pres = $db->FirstQuery($sql)) {
					$fila->prestamo_id = $pres->id;
					$salida['list'][] = $fila;
				}
				
			} else {
				$salida['list'][] = $fila;
			}
		} while($fila = $db->Next($res));
	}
	$salida['header']['items'] = count($salida['list']);
	
} catch(Exception $e) {
	cLogging::Write(__FILE__ ." ".__LINE__ ." ".$e->GetMessage());
}



$ws->SendResponse(200, $salida);