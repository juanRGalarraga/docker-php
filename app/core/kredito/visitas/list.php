<?php
    /**
     * Obtiene un listado de los registros
     * Created: 2021-10-26 15:30:50
     * Author: api_creator
     */


require_once(DIR_model."visitas".DS."class.visitas.inc.php");
$class = new cVisitas;
    
$parametros = $ws->params;

if(empty($parametros['site'])){
    cLogging::Write(__FILE__ . " " . __LINE__ . " No se indico sobre que sitio se quiere obtener las visitas");
    return $ws->SendResponse(500, null, 0);
}

if(empty($parametros['desde'])){
    cLogging::Write(__FILE__ . " " . __LINE__ . " No se indico sobre que sitio se quiere obtener las visitas");
    return $ws->SendResponse(500, null, 0);
}

if(empty($parametros['hasta'])){
    cLogging::Write(__FILE__ . " " . __LINE__ . " No se indico sobre que sitio se quiere obtener las visitas");
    return $ws->SendResponse(500, null,  0);
}

if(!cCheckInput::Fecha($parametros['desde'])  or !cCheckInput::Fecha($parametros['hasta'])){
    cLogging::Write(__FILE__ . " " . __LINE__ . " No se indico sobre que sitio se quiere obtener las visitas");
    return $ws->SendResponse(500, null, 0);
}

if(!$response = $class->GetList($parametros['site'],$parametros['desde'],$parametros['hasta'])){
    cLogging::Write(__FILE__ . " " . __LINE__ . " No se encontraron visitas para el sitio indicado");
    return $ws->SendResponse(500, "No hay visitas", 0);
}
$result = array();

$labels = array();
foreach ($response as $key => $value) {
    $result[cFechas::SQLDate2Str($value->sys_fecha_alta,CDATE_SHORT+CDATE_IGNORETIME)] = $value->visitas;
    $labels[cFechas::SQLDate2Str($value->sys_fecha_alta,CDATE_SHORT+CDATE_IGNORETIME)] = $value->sys_fecha_alta;
}

$response = array(
    'labels'=>array_keys($labels),
    'data'=>$result,
);

cLogging::Write(__FILE__ . " " . __LINE__ . " Se obtuvieron visitas ");
return $ws->SendResponse(200, $response);

?>