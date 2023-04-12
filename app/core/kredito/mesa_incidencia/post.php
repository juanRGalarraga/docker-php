<?php
    /**
     * Escribe o registra un dato
     * Created: [date]
     * Author: api_creator
     */

    
    require_once(DIR_model."mesa_incidencia".DS."class.tipo_incidencias.inc.php");
    require_once(DIR_model."mesa_incidencia".DS."class.incidencias.inc.php");
    require_once(DIR_model."mesa_incidencia".DS."class.incidencias_mensajes.inc.php");
    require_once(DIR_model."mesa_incidencia".DS."class.respuestas_rapidas.inc.php");

    require_once(DIR_model."personas".DS."class.personas.inc.php");
    require_once(DIR_model."biblioteca".DS."class.biblioteca.inc.php");
    
    $personas = new cPersonas;
    $biblioteca = new cBiblioteca;
    $tipos_incidencias = new cTiposMesaIndicencias;
    $mensajes_incidencias = new cIncidenciasMensajes;
    $incidencias_ra = new cIncidenciasRespA;
    $incidencias = new cIncidencias;

    $reg_incidencia = array();
    $reg_mensajes = array();
    if(!$persona_id = SecureInt($ws->GetParam("persona_id"))){
        cLogging::Write(__FILE__." ".__LINE__." No se indico el persona_id de la incidencia ");
        $ws->SendResponse(500,'No se indico el persona_id de la incidencia'); return;
    }

    if(!$personas->Get($persona_id)){
        cLogging::Write(__FILE__." ".__LINE__." No se encontro la persona indicada ");
        $ws->SendResponse(500,'No se encontro la persona indicada'); return;
    }

    if(!$biblioteca->GetByPersona($persona_id)){
        cLogging::Write(__FILE__." ".__LINE__." No se encontro la persona indicada ");
        $ws->SendResponse(500,'No se encontro la persona indicada'); return;
    }

    
    if(!$tipo_id = SecureInt($ws->GetParam("tipo_id"))){
        cLogging::Write(__FILE__." ".__LINE__." No se indico el tipo_id de la incidencia ");
        $ws->SendResponse(500,'No se indico el tipo_id de la incidencia'); return;
    }

    if(!$tipos_incidencias->Get($tipo_id)){
        cLogging::Write(__FILE__." ".__LINE__." No se encontro la incidencia indicada ");
        $ws->SendResponse(500,'No se encontro la incidencia indicada'); return;
    }

    $prioridad = "NUEVA";
    $estado = "NUEVA";

    $asunto = $tipos_incidencias->nombre;

    if(!empty($ws->GetParam("asunto"))){
        $asunto = $ws->GetParam("asunto");
    }
    

    // Datos mensaje
    $desde = $ws->GetParam("desde");
    if(empty($desde)){
        cLogging::Write(__FILE__." ".__LINE__." No se indico el desde de la incidencia ");
        $ws->SendResponse(500,'No se indico el desde de la incidencia'); return;
    }

    
    $mensaje = $ws->GetParam("mensaje");
    if(empty($mensaje)){
        cLogging::Write(__FILE__." ".__LINE__." No se indico el mensaje de la incidencia ");
        $ws->SendResponse(500,'No se indico el mensaje de la incidencia'); return;
    }

    $nombre_usuario = $ws->GetParam('nombre_usuario');
    if(empty($nombre_usuario)){
        cLogging::Write(__FILE__." ".__LINE__." No se indico el nombre del usuario que envia el mensaje ");
        $msgerr['nombre_usuario'] = " No se indico el campo u este es invalido";
    }
    
    
    
    $reg_incidencia['persona_id'] = $persona_id;
    $reg_incidencia['tipo_id'] = $tipo_id;
    $reg_incidencia['prioridad'] = $prioridad;
    $reg_incidencia['estado'] = $estado;
    $reg_incidencia['asunto'] = $asunto;
    
    if(!$result = $incidencias->Create($reg_incidencia)) {
        cLogging::Write(__FILE__." ".__LINE__." No se pudo crear el tipo de indicencia ");
        $ws->SendResponse(500,' No se pudo crear la incidencia '); return;
    }

    $reg_mensajes['incidencia_id'] = $result;
    $reg_mensajes['desde'] = $desde;
    $reg_mensajes['mensaje'] = $mensaje;
    $reg_mensajes['data_usuario'] = array("nombre_usuario" => $nombre_usuario);
    $files = "";
    $files = AgarrarArchivos($ws->GetParam("files"));
    if(!empty($files)){
        $reg_mensajes['files'] = $files;
    }
    
    if(!$mensajes_incidencias->Create($reg_mensajes)){
        cLogging::Write(__FILE__." ".__LINE__." No se pudo crear el mensaje ");
        $ws->SendResponse(500,' No se pudo crear la incidencia '); return;
    }
    if($desde == "P"){
        if($incidencias_ra->GetByTipo($tipo_id)){ 
            $reg_mensajes = array();
            $reg_mensajes['incidencia_id'] = $result;
            $reg_mensajes['desde'] = "A";
            $reg_mensajes['mensaje'] = $incidencias_ra->mensaje;
            $reg_mensajes['data_usuario'] = array("nombre_usuario" => "Respuesta Automatica");
            if(!$mensajes_incidencias->Create($reg_mensajes)){
                cLogging::Write(__FILE__." ".__LINE__." No se pudo crear el mensaje de Respueta Automatica ");
            }
        }
    }

    $ws->SendResponse(200,$result); return;
    

    function AgarrarArchivos($files){
        global $biblioteca;
        $reg_names = array();
        if(!CanUseArray($files)){ return NULL; }

        foreach($files as $key => $value){
            if(empty($value['file_data']) or empty($value['file_name'])){ continue; }
            $reg_files = array();
            $archivo_data = base64_decode($value['file_data']);
            $ruta = DIR_biblioteca.$biblioteca->nombre .DS."archivos";
            cSideKick::EnsureDirExists($ruta);
            
            $biblioteca->AddFile($value['file_name']);
            file_put_contents($ruta.$value['file_name'],$value['file_name']);
            $reg_names[] = $value['file_name'];
        }
        return json_encode($reg_names);
    }