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

    if(!$tipo_id = SecureInt($ws->GetParam("tipo_id"))){
        cLogging::Write(__FILE__." ".__LINE__." No se indico el id del tipo de incidencia ");
        $ws->SendResponse(500,'No se indico el id de la respuesta'); return;
    }

    if(!$tipos_incidencias->Get($tipo_id)){ 
        cLogging::Write(__FILE__." ".__LINE__." No se encontro el tipo de incidencia indicada ");
        $ws->SendResponse(500,'No se encontro el tipo de incidencia indicada'); return;
    }

    $texto_clave = $ws->GetParam("texto_clave");

    if(empty($texto_clave)){
        if($IncidenciasRespA->GetByTipo($tipo_id)){
            cLogging::Write(__FILE__." ".__LINE__." Ya existe la respuesta inicial para este tipo de incidencia");
            $ws->SendResponse(500,' Ya existe la respuesta inicial para este tipo de incidencia , texto vacio '); return;
        }
    }else{
        if($IncidenciasRespA->GetByTextoClave($texto_clave,$tipo_id)){
            cLogging::Write(__FILE__." ".__LINE__." Ya existe la respuesta inicial para este tipo de incidencia con este texto clave");
            $ws->SendResponse(500,' Ya existe la respuesta inicial para este tipo de incidencia con este texto clave'); return;
        }
    }

    $respuesta = $ws->GetParam("respuesta");
    if(empty($respuesta)){
        cLogging::Write(__FILE__." ".__LINE__." No se indico la respuesta ");
        $ws->SendResponse(500,' No se indico la respuesta '); return;
    }

    $reg = array();
    $reg['tipo_id'] = $tipo_id;
    if(!empty($texto_clave)){
        $reg['texto_clave'] = $texto_clave;
    }
    $reg['mensaje'] = $respuesta;
    $reg['estado'] = "HAB";

    if(!$result = $IncidenciasRespA->Create($reg)){ 
        cLogging::Write(__FILE__." ".__LINE__." No se pudo crear la respuesta rapida ");
        $ws->SendResponse(500,' No se pudo crear la respuesta rapida '); return;
    }

    $ws->SendResponse(200,$result); return;
    


    

    