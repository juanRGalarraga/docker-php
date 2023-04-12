<?php
    /**
     * Escribe o registra un dato
     * Created: [date]
     * Author: api_creator
     */

    
    require_once(DIR_model."mesa_incidencia".DS."class.tipo_incidencias.inc.php");
    require_once(DIR_model."mesa_incidencia".DS."class.incidencias.inc.php");
    require_once(DIR_model."mesa_incidencia".DS."class.incidencias_mensajes.inc.php");
    require_once(DIR_model."personas".DS."class.personas.inc.php");
    require_once(DIR_model."biblioteca".DS."class.biblioteca.inc.php");
    $personas = new cPersonas;
    $biblioteca = new cBiblioteca;
    $tipos_incidencias = new cTiposMesaIndicencias;
    $mensajes_incidencias = new cIncidenciasMensajes;
    $incidencias = new cIncidencias;

    $reg = array();
    if(!$id = SecureInt($ws->GetParam("id"))){
        cLogging::Write(__FILE__." ".__LINE__." No se indico el id de la incidencia ");
        $ws->SendResponse(500,'No se indico el id de la incidencia'); return;
    }

    if(!$incidencias->Get($id)){
        cLogging::Write(__FILE__." ".__LINE__." No se encontro la persona indicada ");
        $ws->SendResponse(500,'No se encontro la persona indicada'); return;
    }

    if(!$biblioteca->GetByPersona($incidencias->persona_id)){
        cLogging::Write(__FILE__." ".__LINE__." No se encontro la persona indicada ");
        $ws->SendResponse(500,'No se encontro la persona indicada'); return;
    }

    if(!empty($ws->GetParam("prioridad"))){
        $prioridad = $ws->GetParam("prioridad");
        if(in_array($prioridad,PRIORIDADES_VALIDAS)){ 
            $reg['prioridad'] = $prioridad;
        }
    }

    if(!empty($ws->GetParam("estado"))){
        $estado = $ws->GetParam("estado");
        $reg['estado'] = $estado;
    }

    if(!empty($ws->GetParam("asunto"))){
        $asunto = $ws->GetParam("asunto");
        $reg['asunto'] = $asunto;
    }

    if(!empty($ws->GetParam("usuarios"))){
        $usuarios = $ws->GetParam("usuarios");
        $usuarios = explode(",",$usuarios);
        
        $reg['usuarios'] = $usuarios;
    }

    
    if(!empty($ws->GetParam("files"))){
        $files = $ws->GetParam("files");
        $files = AgarrarArchivos($files);
        if(!empty($files)){ 
            $files = array_merge($files,$incidencias->files);
            $reg['files'] = json_encode($files);
        }
    }
    
    if(!CanUseArray($reg)){ 
        cLogging::Write(__FILE__." ".__LINE__." No se indicaron elementos para editar ");
        $ws->SendResponse(500,' No se pudo crear la incidencia '); return;
    }


    if(!$result = $incidencias->Save($reg)) {
        cLogging::Write(__FILE__." ".__LINE__." No se pudo crear el tipo de indicencia ");
        $ws->SendResponse(500,' No se pudo crear la incidencia '); return;
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