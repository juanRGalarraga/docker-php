<?php
/*
	Devuelve un listado, no para tabla sino para select, de las ciudades que contienen en su nombre el lexema indicado, filtrado por región.
	Created: 2021-11-12
	Author: DriverOp
*/


$busqueda = $ws->GetParams(['str','busc','term','busqueda','nombre']);
$region_id = $ws->params['region']??null;

if (empty($busqueda)) { return $ws->SendResponse(200, null); }

require_once(DIR_model."geo".DS."class.geo.inc.php");

$geo = new cGeo();

$ws->SendResponse(200, $geo->GetCiudades($busqueda,$region_id));
