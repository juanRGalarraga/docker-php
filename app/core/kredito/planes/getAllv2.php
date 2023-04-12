<?php


require_once(DIR_model."planes".DS."class.planes.inc.php");
require_once(DIR_model."planes".DS."class.cargos_impuestos.inc.php");
Showvar("esto");
$planes = new cPlanes();

$salida = $planes->GetAll();
$ws->SendResponse(200, $salida);
