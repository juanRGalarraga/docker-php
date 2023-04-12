<?php
    /**
     * Escribe o registra un dato
     * Created: [date]
     * Author: api_creator
     */

    require_once(DIR_model."mesa_incidencia".DS."class.respuestas_rapidas.inc.php");
    require_once(DIR_model."mesa_incidencia".DS."class.tipo_incidencias.inc.php");
    $IncidenciasRespA = new cIncidenciasRespA;
    $tipos_incidencias = new cTiposMesaIndicencias;
    
    $reg = array();

    if(!$id = SecureInt($ws->GetParam("id"))){
        cLogging::Write(__FILE__." ".__LINE__." No se indico el id del tipo de incidencia ");
        $ws->SendResponse(500,'No se indico el id de la respuesta'); return;
    }

    if(!$IncidenciasRespA->Get($id)){ 
        cLogging::Write(__FILE__." ".__LINE__." No se encontro la respuesta que esta buscando ");
        $ws->SendResponse(500,'No existe la respuesta indicada '); return;
    }
    

    if(!$tipos_incidencias->Get($IncidenciasRespA->tipo_id)){
        cLogging::Write(__FILE__." ".__LINE__." No se encontro el tipo de incidencia indicada ");
        $ws->SendResponse(500,'No se encontro el tipo de incidencia indicada'); return;
    }

    $texto_clave = $ws->GetParam("texto_clave");

    if(!empty($texto_clave)){
        if($IncidenciasRespA->GetByTextoClave($texto_clave,$IncidenciasRespA->tipo_id,$id)){
            cLogging::Write(__FILE__." ".__LINE__." Ya existe la respuesta inicial para este tipo de incidencia con este texto clave");
            $ws->SendResponse(500,' Ya existe la respuesta inicial para este tipo de incidencia con este texto clave'); return;
        }
        $IncidenciasRespA->Get($id);
        $reg['texto_clave'] = $texto_clave;
    }

    $respuesta = $ws->GetParam("respuesta");
    if(!empty($respuesta)){
        $reg['respuesta'] = $respuesta;
    }
    
    $estado = $ws->GetParam("estado");
    if(!empty($estado)){
        $reg['estado'] = $estado;
    }
    
    if(!$result = $IncidenciasRespA->Save($reg)){ 
        cLogging::Write(__FILE__." ".__LINE__." No se pudo crear la respuesta rapida ");
        $ws->SendResponse(500,' No se pudo mmodificar la respuesta rapida '); return;
    }

    $ws->SendResponse(200,$result); return;
        