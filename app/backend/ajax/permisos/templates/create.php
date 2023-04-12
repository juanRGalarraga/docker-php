<?php 
/**
 * Guarda una nueva plantilla
 * @author Juan Galarraga
 * @created 2021-11-02
 */

require_once(DIR_model."permisos".DS."class.template.inc.php");
$objPlantilla = new cTemplate();

$post = CleanArray($_POST);
$templateName = ArreglarMayusculas(trim($post['templateName']));
$plantilla = $post['plantilla'];

if(empty($templateName)){
    cLogging::Write(__LINE__." EL nombre de la plantilla llegó vacío");
    return EmitJSON(["msgerr" => "Debe indicar un nombre para la plantilla"]);
}

if(!CanUseArray($plantilla)){
    cLogging::Write(__LINE__." La plantilla llegó vacía");
    return EmitJSON(["msgerr" => "No se pudo crear la plantilla"]);
}

$record = [
    'nombre' => $templateName,
    'plantilla' => $plantilla,
    'sys_usuario_id' => $objeto_usuario->id
];

if(!$objPlantilla->NewPlantilla($record)){
    cLogging::Write(__LINE__." No se pudo guardar la plantilla en BD");
    return EmitJSON(["msgerr" => "No se pudo crear la plantilla"]);
}

ResponseOk(["msgok" => "Plantilla almacenada"]);