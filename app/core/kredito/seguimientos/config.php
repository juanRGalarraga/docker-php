<?php
/*
	Devolver al cliente los registro de acciones y todos los datos manualmente agregados en las solicitudes.
*/

$salida = array(
	"acciones"=>array()
);

require_once(DIR_model."seguimientos".DS."class.seguimientos.inc.php");
require_once(DIR_model."seguimientos".DS."class.acciones.inc.php");

$acciones = new cAccionesSeguimientos;
$seguimientos = new cSeguimientos;

$listaAcciones = $acciones->GetList();
$listaClaves = $seguimientos->GetDataFields();

$salida = array(
	'acciones'=>$listaAcciones,
	'data'=>$listaClaves
);

$ws->SendResponse(200, $salida);

