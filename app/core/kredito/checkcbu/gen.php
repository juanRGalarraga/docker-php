<?php
/*
	Genera un CBU al azar.
	No incluir esta API en la documentación!
	
	Created: 2021-01-04
	Author: DriverOp
*/
require_once(DIR_model."bancos".DS."class.bancos.inc.php");

$banco = new cBancos();
$banco->GetRandom();

$respuesta = new stdClass();

$respuesta->cbu = calcular(str_pad($banco->id,3,'0',STR_PAD_LEFT), sucursal(), cuenta());
$respuesta->banco = titleCase($banco->nombre);

$ws->SendResponse(200, $respuesta);
$continue = false;

function sucursal() {
	return str_pad(rand(0,9999),4,'0',STR_PAD_LEFT);
}

function cuenta() {
	return '111'.str_pad(rand(0,9999999),10,'0',STR_PAD_LEFT);
}

function calcular($banco, $sucursal, $cuenta) {
  $verificador1 = 
	($banco[0] * 7) +
	($banco[1] * 1) +
	($banco[2] * 3) +
	($sucursal[0] * 9) +
	($sucursal[1] * 7) +
	($sucursal[2] * 1) +
	($sucursal[3] * 3);
	$verificador1 = (10 - $verificador1 % 10) % 10;
  $verificador2 =
	($cuenta[0] * 3) +
	($cuenta[1] * 9) +
	($cuenta[2] * 7) +
	($cuenta[3] * 1) +
	($cuenta[4] * 3) +
	($cuenta[5] * 9) +
	($cuenta[6] * 7) +
	($cuenta[7] * 1) +
	($cuenta[8] * 3) +
	($cuenta[9] * 9) +
	($cuenta[10] * 7) +
	($cuenta[11] * 1) +
	($cuenta[12] * 3);
	$verificador2 = (10 - $verificador2 % 10) % 10;
	return $banco . $sucursal . $verificador1 . $cuenta . $verificador2;
}
?>