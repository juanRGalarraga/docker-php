<?php
    /**
     * Escribe o registra un dato
     * Created: [date]
     * Author: api_creator
     */

require_once(DIR_model."mesa_incidencia".DS."class.tipo_incidencias.inc.php");
$tipos_incidencias = new cTiposMesaIndicencias;

$reg = array();

if(!$id = SecureInt($ws->GetParam("id"))){ 
    cLogging::Write(__FILE__." ".__LINE__." No se indico el identificador de el tipo de incidencia ");
    $ws->SendResponse(500,' No se indico el identificador'); return;
}

if(!$tipos_incidencias->Get($id)){
    cLogging::Write(__FILE__." ".__LINE__." No encontro en base de datos la incidencia ");
    $ws->SendResponse(500,' No existe la indicencia '); return;
}
$nombre =$ws->GetParam("nombre");
if(!empty($nombre)){ 
    $reg['nombre'] = $nombre;
}

$sitio = 'ALL';
$sitio = $ws->GetParam("sitios");
if(!empty($sitio) && in_array($sitio,SITIOS)){
    $reg['sitio'] = $sitio;
}else{
    cLogging::Write(__FILE__." ".__LINE__." El sitio no pertenece a la lista de habilitados");
}

$estado = $ws->GetParam("estado");

if(!empty($estado)){
    $reg['estado'] = $estado;
}

if(!CanUseArray($reg)){ 
    $ws->SendResponse(500,' No hay campos a modificar '); return;
}

if(!$result = $tipos_incidencias->Save($reg)) {
    cLogging::Write(__FILE__." ".__LINE__." No se pudo crear el tipo de indicencia ");
    $ws->SendResponse(500,' No se pudo crear el tipo de incidencia '); return;
}

$ws->SendResponse(200,$result); return;
