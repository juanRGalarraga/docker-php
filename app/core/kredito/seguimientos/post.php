<?php
    /**
     * Añade un nuevo seguimiento
     * Created: 2021-11-19
     * Author: api_creator
     */


$prestamo_id = $ws->params['prestamo'];

require_once(DIR_model."prestamos".DS."class.prestamos.inc.php");

$objPrestamo = new cPrestamos;
$prestamo = $objPrestamo->Get($prestamo_id);
if (!$objPrestamo->existe) {
	return $ws->SendResponse(404, "Préstamo no encontrado.", 13);
}

require_once(DIR_model."seguimientos".DS."class.seguimientos.inc.php");
require_once(DIR_model."seguimientos".DS."class.acciones.inc.php");

$acciones = new cAccionesSeguimientos;
$seguimientos = new cSeguimientos;

$accion_id = SecureInt($ws->GetParams(['accion','action']),null);
if (empty($accion_id)) {
	return $ws->SendResponse(400, 'Acción no indicada o es inválida.');
}

if (!$acciones->Get($accion_id)) {
	return $ws->SendResponse(404, 'Acción no es válida.');
}

$proximo_contacto = $ws->GetParams(['proximo_contacto','next','next_contact']);
if (!empty($proximo_contacto)) {
	$proximo_contacto = str_replace('T',' ',$proximo_contacto);
	if (!cFechas::LooksLikeISODateTime($proximo_contacto)) {
		return $ws->SendResponse(400, 'Fecha y hora formato incorrecto.');
	}
}

$notas = ArreglarMayusculas(mb_substr(strip_tags($ws->GetParams(['notas','notes']??null)),0,2048));

$data = '{}';
$aux = $ws->GetParams(['data']);
if (!empty($aux)) {
	if (is_array($aux)) {
		$data = array();
		foreach($aux as $key => $value) {
			$key = toAlias(mb_substr(strtolower(strip_tags(trim($key))),0,64));
			if ($key != 'null') {
				$data[$key] = mb_substr(strip_tags(trim($value)),0,512);
			}
		}
		$data = json_encode($data);
	} else {
		return $ws->SendResponse(400, 'Formato data desconocido, debe ser un array.');
	}
}

$seguimientos->prestamo_id = $prestamo_id;
$seguimientos->persona_id = $prestamo->persona_id;
$seguimientos->accion_id = $accion_id;
$seguimientos->data = $data;
$seguimientos->proximo_contacto = (!empty($proximo_contacto))?$proximo_contacto:null;
if (!empty($notas)) {
	$seguimientos->notas = $notas;
}
$seguimientos->sys_usuario_id = $ws->backendUserId;
$seguimientos->sys_fecha_alta = cFechas::Ahora();
$seguimientos->sys_fecha_modif = $seguimientos->sys_fecha_alta;

if ($seguimientos->New()) {
	$ws->SendResponse(200, $seguimientos->last_id);
} else {
	$ws->SendResponse(500, "No se pudo almacenar nuevo seguimiento.");
}

