<?php
    /**
     * Escribe o registra un dato
     * Created: [date]
     * Author: api_creator
    */
    require_once(DIR_model."mesa_incidencia".DS."class.incidencias.inc.php");
    require_once(DIR_model."mesa_incidencia".DS."class.incidencias_mensajes.inc.php");
    require_once(DIR_model."personas".DS."class.personas.inc.php");
    require_once(DIR_model."biblioteca".DS."class.biblioteca.inc.php");

    $mensajes_incidencias = new cIncidenciasMensajes;
    $incidencias = new cIncidencias;
    $personas = new cPersonas;
    $biblioteca = new cBiblioteca;

    $reg = array();

    $nombre_usuario = "";
    if(!$id = SecureInt($ws->GetParam("id"))){
        cLogging::Write(__FILE__." ".__LINE__." No se indico el id del mensaje ");
        $ws->SendResponse(500,'No se indico el id del mensaje'); return;
    }

    if(!$mensajes_incidencias->Get($id)){
        cLogging::Write(__FILE__." ".__LINE__." No existe el mensaje ");
        $ws->SendResponse(500,'No existe el mensaje'); return;
    }

    $estado = $ws->GetParam('estado');
    if(empty($estado) or !isset(ESTADOS_VALIDOS[$estado])){ 
        cLogging::Write(__FILE__." ".__LINE__." El estado que se quiere indicar es invalido  ");
        $ws->SendResponse(500,'El estado que se quiere indicar es invalido '); return;
    }

    $reg['estado'] = $estado;
    
    if(!$result = $mensajes_incidencias->Save($reg)){ 
        cLogging::Write(__FILE__." ".__LINE__." No se pudo modificar el mensaje ");
        $ws->SendResponse(500," No se pudo modificar el mensaje "); return;
    }
    
    $ws->SendResponse(200,$result); 
