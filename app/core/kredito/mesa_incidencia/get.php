<?php
    /**
     * Obtiene un registro dado un ID númerico
     * Created: [date]
     * Author: api_creator
     */

    require_once(DIR_model."mesa_incidencia".DS."class.incidencias.inc.php");
    require_once(DIR_model."personas".DS."class.personas.inc.php");
    $personas = new cPersonas;
    $incidencias = new cIncidencias;

    if(!$id = SecureInt($ws->GetParam("id"))){
        cLogging::Write(__FILE__." ".__LINE__." No se indico el id de la incidencia ");
        $ws->SendResponse(500,'No se indico el id de la incidencia'); return;
    }

    if(!$incidencias->Get($id)){
        cLogging::Write(__FILE__." ".__LINE__." No se encontro la persona indicada ");
        $ws->SendResponse(500,'No se encontro la persona indicada'); return;
    }

    if(!$personas->Get($incidencias->persona_id)){
        cLogging::Write(__FILE__." ".__LINE__." No se encontro la persona indicada ");
        $ws->SendResponse(500,'No se encontro la persona indicada'); return;
    }
    $response = new stdClass;
    $response = $incidencias->actualRecord;
    $response->nombre_persona = new stdClass;
    $response->nombre_persona = $personas->nombre." ".$personas->apellido;
    $ws->SendResponse(200,$response); return;

    
