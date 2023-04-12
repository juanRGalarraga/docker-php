<?php
    /**
     * Obtiene un seguimiento específico
     * Created: 2021-11-19
     * Author: api_creator
     */
	

$seguimiento_id = $ws->params['id'];



require_once(DIR_model."seguimientos".DS."class.seguimientos.inc.php");
$seguimientos = new cSeguimientos;

$salida = $seguimientos->Get($seguimiento_id);
if (empty($salida) or !$seguimientos->existe) {
	return $ws->SendResponse(404, "Seguimiento no encontrado.");
}


$ws->SendResponse(200, $salida);