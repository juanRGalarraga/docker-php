<?php
    /**
     * Obtiene un registro dado un ID númerico
     * Created: [date]
     * Author: api_creator
     */

require_once(DIR_model."mesa_incidencia".DS."class.tipo_incidencias.inc.php");
$tipos_incidencias = new cTiposMesaIndicencias;

if(!$id = SecureInt($ws->GetParam("id"))){
    cLogging::Write(__FILE__." ".__LINE__." No se indico el identificador de la incidencia ");
    $ws->SendResponse(500,'No se indico el prestamo'); return;
}

if(!$tipos_incidencias->Get($id)){ 
    cLogging::Write(__FILE__." ".__LINE__." No se encontro la indicencia con id = ".$id);
    $ws->SendResponse(404,'No se encontro la incidencia indicada'); return;
}

$ws->SendResponse(200,$tipos_incidencias->actualRecord); return;
