<?php
    /**
     * Obtiene un registro dado un ID númerico
     * Created: [date]
     * Author: api_creator
     */

$name = $ws->params['name']??null;

if (empty($name)) {
	return $ws->SendResponse(400,'No se indicó nombre de parámetro');
}
$reg = $sysParams->GetRegistro($name,null);
if (!$sysParams->existe) {
	return $ws->SendResponse(404,'Parámetro no encontrado');
}

$ws->SendResponse(200,$reg);
