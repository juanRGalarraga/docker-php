<?php
    /**
     * Obtiene un listado de los registros
     * Created: [date]
     * Author: api_creator
    */
    require_once(DIR_model."mesa_incidencia".DS."class.incidencias.inc.php");
    require_once(DIR_model."mesa_incidencia".DS."class.incidencias_mensajes.inc.php");
    require_once(DIR_model."personas".DS."class.personas.inc.php");

    $mensajes_incidencias = new cIncidenciasMensajes;
    $incidencias = new cIncidencias;
    $personas = new cPersonas;

    
    if(!$incidencia_id = SecureInt($ws->GetParam("id"))){
        cLogging::Write(__FILE__." ".__LINE__." No se indico el id de la incidencia ");
        $ws->SendResponse(500,'No se indico el id de la incidencia'); return;
    }

    if(!$incidencias->Get($incidencia_id)){
        cLogging::Write(__FILE__." ".__LINE__." No existe la incidencia  ");
        $ws->SendResponse(500,'No existe la incidencia'); return;
    }

    if(!$personas->Get($incidencias->persona_id)){
        cLogging::Write(__FILE__." ".__LINE__." No existe la persona  ");
        $ws->SendResponse(500,'No existe la persona'); return;
    }

    if(!$mensajes = $mensajes_incidencias->GetByIncidencia($incidencia_id)){
        cLogging::Write(__FILE__." ".__LINE__." No hay mensajes para esta incidencia  ");
        $ws->SendResponse(500,'No hay mensajes para esta incidencia'); return;
    }
    $mensajes['nombre_persona'] = $personas->nombre." ".$personas->apellido;
    $ws->SendResponse(200,$mensajes);
