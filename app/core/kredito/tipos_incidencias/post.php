<?php
    /**
     * 
     * Created: [date]
     * Author: api_creator
     */

require_once(DIR_model."mesa_incidencia".DS."class.tipo_incidencias.inc.php");
$tipos_incidencias = new cTiposMesaIndicencias;


$nombre = $ws->GetParam("nombre");
if(empty($nombre)){
    cLogging::Write(__FILE__." ".__LINE__." No se indico el nombre de la incidencia ");
    $ws->SendResponse(500,'No se indico el nombre de la incidencia'); return;
}

$sitio = 'ALL';

if(!is_null($ws->GetParam("sitio"))){
    if(in_array($ws->GetParam("sitio"),SITIOS)){
        $sitio = $ws->GetParam("sitio");
    }
    cLogging::Write(__FILE__." ".__LINE__." No se indico el sitio , se establece 'ALL' por default ");
}

if($tipos_incidencias->GetByName($nombre,$sitio)){ 
    cLogging::Write(__FILE__." ".__LINE__." Ya existe incidencias con ese nombre ");
    $ws->SendResponse(404,' Ya existe una indicencia con ese nombre y ese sitio'); return;
}
$reg = array();

$reg[ 'nombre'] = $nombre;
$reg['sitio'] = $sitio;

if(!$result = $tipos_incidencias->Create($reg)) {
    cLogging::Write(__FILE__." ".__LINE__." No se pudo crear el tipo de indicencia ");
    $ws->SendResponse(500,' No se pudo crear el tipo de incidencia '); return;
}

$ws->SendResponse(200,$result); return;
