<?php
    /**
     * Escribe o registra un dato
     * Created: [date]
     * Author: api_creator
     */
    require_once(DIR_model."mesa_incidencia".DS."class.incidencias.inc.php");
    require_once(DIR_model."mesa_incidencia".DS."class.incidencias_mensajes.inc.php");
    require_once(DIR_model."mesa_incidencia".DS."class.respuestas_rapidas.inc.php");
    require_once(DIR_model."personas".DS."class.personas.inc.php");
    require_once(DIR_model."biblioteca".DS."class.biblioteca.inc.php");

    $mensajes_incidencias = new cIncidenciasMensajes;
    $incidencias_ra = new cIncidenciasRespA;
    $incidencias = new cIncidencias;
    $personas = new cPersonas;
    $biblioteca = new cBiblioteca;

    $reg = array();

    $nombre_usuario = "";
    if(!$incidencia_id = SecureInt($ws->GetParam("incidencia_id"))){
        cLogging::Write(__FILE__." ".__LINE__." No se indico el id de la incidencia ");
        $ws->SendResponse(500,'No se indico el id de la incidencia'); return;
    }

    if(!$incidencias->Get($incidencia_id)){
        cLogging::Write(__FILE__." ".__LINE__." No existe la incidencia  ");
        $ws->SendResponse(500,'No existe la incidencia'); return;
    }

    if(!$personas->Get($incidencias->persona_id)){ 
        cLogging::Write(__FILE__." ".__LINE__." No existe la persona ");
        $ws->SendResponse(500,' Persona no existente '); return;
    }

    if(!$biblioteca->GetByPersona($incidencias->persona_id)){
        cLogging::Write(__FILE__." ".__LINE__." Persona sin carpeta predefinida, se intenta crear la misma ");
    }

    $desde = $ws->GetParam('desde');
    if(empty($desde) or !in_array($desde,PEOPLEMSG)){
        cLogging::Write(__FILE__." ".__LINE__." El destinatario al que intenta enviar el mensaje , no existe ");
        $msgerr['desde'] = " No se indico el campo u este es invalido";
    }

    $mensaje = $ws->GetParam('mensaje');
    if(empty($mensaje)){
        cLogging::Write(__FILE__." ".__LINE__." No se indico el mensaje a enviar ");
        $msgerr['mensaje'] = " No se indico el campo u este es invalido";
    }

    $nombre_usuario = $ws->GetParam('nombre_usuario');
    if(empty($nombre_usuario)){
        cLogging::Write(__FILE__." ".__LINE__." No se indico el nombre del usuario que envia el mensaje ");
        $msgerr['nombre_usuario'] = " No se indico el campo u este es invalido";
    }

    if(CanUseArray($msgerr)){
        $ws->SendResponse(500,$msgerr); return;
    }

    $files = $ws->GetParam('files');

    if(!empty($files)){
        
        if(isJsonEx($files)){ 
            $files = json_decode($files,true);
        }
        $files = AgarrarArchivos($files);
        $reg['files'] = $files;
    }

    $reg['incidencia_id'] = $incidencia_id;
    $reg['desde'] = $desde;
    $reg['mensaje'] = $mensaje;
    $reg['data_usuario'] = array("nombre_usuario" => $nombre_usuario);
    
    if(!$result = $mensajes_incidencias->Create($reg)){ 
        cLogging::Write(__FILE__." ".__LINE__." No se pudo crear el mensaje ");
        $ws->SendResponse(500," No se pudo crear el mensaje "); return;
    }
    if($desde == "P"){
        if($incidencias_ra->GetByTextoClave($mensaje,$incidencias->tipo_id)){
            $reg = array();
            $reg['incidencia_id'] = $incidencia_id;
            $reg['desde'] = "A";
            $reg['mensaje'] = $incidencias_ra->mensaje;
            $reg['data_usuario'] = array("nombre_usuario" => "Respuesta Automatica");
            if(!$result = $mensajes_incidencias->Create($reg)){ 
                cLogging::Write(__FILE__." ".__LINE__." No se pudo crear el mensaje de la respuesta Automatica ");
            }
        }
    }
    
    $ws->SendResponse(200,array("data"=>$incidencia_id)); 

    function AgarrarArchivos($files){
        global $biblioteca;
        $reg_names = array();
        if(!CanUseArray($files)){ return NULL; }
        
        $reg_files = array();
        $archivo_data = base64_decode($files['file_data']);
        $name = str_replace(array(" ",":"),array("_","-"),cFechas::Ahora()).$files['file_name'];
        $ruta = DIR_biblioteca.$biblioteca->nombre .DS."archivos";
        cSideKick::EnsureDirExists($ruta);
            
        file_put_contents($ruta.DS.$name,$archivo_data);
        $biblioteca->AddFile($name);
        $reg_names[] = $name;
        return $reg_names;
    }
