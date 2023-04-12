<?php
    /**
     * Obtiene un registro dado un ID númerico
     * Created: [date]
     * Author: api_creator
     */

require_once(DIR_model."cobros".DS."class.cobros.inc.php");
$cobros = new cCobros;

if(!$cobro_id = SecureInt($ws->GetParam('id'))){
    cLogging::Write(__FILE__ ." " . __LINE__ . " No se indico el id del cobro");
    $ws->SendResponse(404,NULL,'No se indico el cobro'); return;
}

if(!$cobros->Get($cobro_id)){
    cLogging::Write(__FILE__ ." " . __LINE__ . " No se encontró el cobro ");
    $ws->SendResponse(404,NULL,'No se indico el cobro'); return;
}
$ws->SendResponse(200,$cobros->actualRecord); return;



