<?php
/*
	Pide una nueva cotización al core para la calculadora / simulador.
	Created: 2021-11-12
	Author: DriverOp
*/

$post = CleanArray($_POST);

$data['plan'] = SecureInt($post['id'],null);
if (empty($data['plan'])) { return; }

$data['plazo'] = SecureInt($post['testPeriodo'],null);
if (empty($data['plazo'])) { return; }

$data['monto'] = SecureInt($post['testMonto'],null);
if (empty($data['monto'])) { return; }

require_once(DIR_model."calculadora".DS."class.calculadora.inc.php");
$calculadora = new cCalculadora();


$respuesta = $calculadora->Get();