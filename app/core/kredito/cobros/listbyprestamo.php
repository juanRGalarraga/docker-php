<?php
    /**
     * Obtiene un registro dado un ID númerico
     * Created: [date]
     * Author: api_creator
     */

require_once(DIR_model."cobros".DS."class.cobros.inc.php");
$cobros = new cCobros;
$result = false;
if(!$prestamo_id = SecureInt($ws->GetParam('prestamo_id'))){
    cLogging::Write(__FILE__ ." " . __LINE__ . " No se envio el identificador de prestamo ");
    $ws->SendResponse(404,NULL,'No se envio el identificador de prestamo'); return;
}

if(!$result = $cobros->GetListByPrestamo($prestamo_id)){
    cLogging::Write(__FILE__ ." " . __LINE__ . " No se encontró el cobro ");
    $ws->SendResponse(404,NULL,'No se encontró el cobro'); return;
}
$ws->SendResponse(200,$result); return;



