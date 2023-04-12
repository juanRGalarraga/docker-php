<?php
/*
	Pide una nueva cotización al core para la calculadora / simulador.
	Created: 2021-09-02
	Author: DriverOp
*/

$post = CleanArray($_POST);

if (!isset($post['cotiz'])) { return; } // Si no está cotiz, salimos inceremoniosamente.

if (!IsJsonEx($post['cotiz'])) { return; } // Si cotiz no es un JSON, salimos inceremoniosamente.

$cotiz = json_decode($post['cotiz']);

if (json_last_error() != JSON_ERROR_NONE) { return; }

$msgerr = array();

if (!isset($cotiz->numberMonto)) { $msgerr['numberMonto'] = 'No se indicó monto'; }
else { if (!CheckFloat($cotiz->numberMonto)) { $msgerr['numberMonto'] = 'Monto no es un número.'; } }
if (!isset($cotiz->numberPlazo)) { $msgerr['numberPlazo'] = 'No se indicó plazo'; }
else { if (!CheckInt($cotiz->numberPlazo)) { $msgerr['numberPlazo'] = 'Plazo no es un número.'; } }

if (CanUseArray($msgerr)) {
	return EmitJSON($msgerr);
}

require_once(DIR_model."calculadora".DS."class.calculadora.inc.php");
$calculadora = new cCalculadora();

$montoMin = $calculadora->GetAttr('Monto_Minimo');
$cotiz->numberMonto = ($cotiz->numberMonto < $montoMin)?$montoMin:$cotiz->numberMonto;

$montoMax = $calculadora->GetAttr('Monto_Maximo');
$cotiz->numberMonto = ($cotiz->numberMonto > $montoMax)?$montoMax:$cotiz->numberMonto;

$plazoMin = $calculadora->GetAttr('Plazo_Minimo');
$cotiz->numberPlazo = ($cotiz->numberPlazo < $plazoMin)?$plazoMin:$cotiz->numberPlazo;

$plazoMax = $calculadora->GetAttr('Plazo_Maximo');
$cotiz->numberPlazo = ($cotiz->numberPlazo > $plazoMax)?$plazoMax:$cotiz->numberPlazo;

$cotiz->Planid = $calculadora->GetAttr('Planid');

$respuesta = $calculadora->Get(['monto'=>$cotiz->numberMonto, 'plazo'=>$cotiz->numberPlazo, 'plan'=>$cotiz->Planid]);
if (empty($respuesta)) {
	return EmitJSON($calculadora->msgerr);
}

ResponseOk(['respuesta'=>$respuesta]);