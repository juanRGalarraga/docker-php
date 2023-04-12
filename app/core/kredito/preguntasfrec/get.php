<?php
    /**
     * Obtiene un registro dado un ID númerico
     * Created: [date]
     * Author: api_creator
     */

    require_once(DIR_model."mesa_incidencia".DS."class.respuestas_rapidas.inc.php");
    $IncidenciasRespA = new cIncidenciasRespA;

    if(!$id = SecureInt($ws->GetParam("id"))){
        cLogging::Write(__FILE__." ".__LINE__." No se indico el id de la respuetas ");
        $ws->SendResponse(500,'No se indico el id de la respuesta'); return;
    }

    if(!$IncidenciasRespA->Get($id)){
        cLogging::Write(__FILE__." ".__LINE__." No se encontro la respuesta ");
        $ws->SendResponse(500,'No se encontro la respuesta'); return;
    }

    $ws->SendResponse(200,$IncidenciasRespA->actualRecord); return;


