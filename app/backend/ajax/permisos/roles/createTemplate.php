<?php 
/**
 * Guarda una nueva plantilla para un rol
 * @author Juan Galarraga
 * @created 2021-11-02
 */

require_once(DIR_model."permisos".DS."class.rol.inc.php");

$rol = new cRol();

$post = CleanArray($_POST);
$rolID = SecureInt($post['rolID'] ?? null);
$plantilla = $post['plantilla'];

if(!$rolID){
    cLogging::Write(__LINE__." EL ID de la plantilla llegó vacío");
    return EmitJSON(["msgerr" => "No se pudo crear la plantilla"]);
}

if(!$rol->GetRol($rolID)){
    cLogging::Write(__LINE__." No se pudo obtener el rol $rolID");
    return EmitJSON(["msgerr" => "No se pudo crear la plantilla"]);
}

if(!CanUseArray($plantilla)){
    cLogging::Write(__LINE__." La plantilla llegó vacía");
    return EmitJSON(["msgerr" => "No se pudo crear la plantilla"]);
}

$rol->plantilla = $plantilla;
if(!$rol->Set()){
    cLogging::Write(__LINE__." No se pudo guardar la plantilla en BD");
    return EmitJSON(["msgerr" => "No se pudo crear la plantilla"]);
}


ResponseOk(["msgok" => "Plantilla almacenada"]);