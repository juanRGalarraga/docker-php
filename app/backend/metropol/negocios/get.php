<?php
    /**
     * Obtiene un registro dado un ID númerico
     * Created: 2021-10-30
     * Author: DriverOp
     */

    require_once(DIR_model."negocios".DS."class.negocios.inc.php");
    $negocios = new cNegocios;
	
	$id = $ws->params['id'];
	if (empty($id)) {
		return $ws->SendResponse(400, "Negocio ID es requerido.");
	}
	
	$datos = $negocios->Get($id);
	if (empty($datos)) {
		return $ws->SendResponse(404, "Negocio no encontrado.");
	}

	$respuesta = new stdClass;
	
	$respuesta->id = $datos->id;
	$respuesta->nombre = $datos->nombre;
	
	$ws->SendResponse(200, $respuesta);