<?php
/**
 * Crea un rol
 * @author Juan Galarraga
 * @created 2021-10-29
 */

require_once(DIR_model."permisos".DS."class.rol.inc.php");
require_once(DIR_model."permisos".DS."class.template.inc.php");

$rol = new cRol();
$objetoTemplate = new cTemplate();

$post = CleanArray($_POST);
$rolName = ArreglarMayusculas(trim($post['rolName'] ?? ''));
$templateID = SecureInt($post['templateID'] ?? null);
$plantilla = array();

if(empty($rolName)){
    cLogging::Write(__LINE__." El nombre del rol llegó vacío.");
    return EmitJSON(['msgerr' => 'El nombre del rol no puede estar vacío']);
}


if($templateID){
    if(!$objetoTemplate->GetPlantilla($templateID)){
        cLogging::Write(__LINE__." No se pudo obtener la plantilla $templateID");
        return EmitJSON(['msgerr' => "No se pudo crear el rol"]);
    }
    $plantilla = $objetoTemplate->plantilla;
} else {
    cLogging::Write(__LINE__." El ID de la plantilla llegó vacío. Se generará una plantilla por defecto.");
    $plantilla = $objetoTemplate->GetTemplate();
}

if(empty($plantilla)){
    cLogging::Write(__LINE__." No se pudo generar una plantilla para el rol");
    return EmitJSON(['msgerr' => "No se pudo crear el rol"]);
}


$record = [
    'nombre' => $rolName,
    'plantilla' => $plantilla,
    'sys_usuario_id' => $objeto_usuario->id
];

if(!$rol->NewRecord($record)){
    cLogging::Write(__LINE__." No se pudo crear el rol. ");
    return EmitJSON(['msgerr' => 'No se pudo crear el rol']);
}

ResponseOk(['msgok' => 'Rol actualizado']);