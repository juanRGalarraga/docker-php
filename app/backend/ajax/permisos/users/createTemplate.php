<?php 
/**
 * Guarda una nueva plantilla para un usuario
 * @author Juan Galarraga
 * @created 2021-11-03
 */

require_once(DIR_model."permisos".DS."class.user.inc.php");
$user = new cUser();

$post = CleanArray($_POST);
$plantilla = $post['plantilla'];
$userID = SecureInt($post['userID'] ?? null);
if(!$userID){
    cLogging::Write(__LINE__." El ID del usuario llegó vacío");
    return EmitJSON(["msgerr" => "No se pudo crear la plantilla"]);
}

if(!$user->UserExists($userID)){
    cLogging::Write(__LINE__." El usuario [$userId] no existe o está deshabilitado");
    return EmitJSON(["msgerr" => "No se pudo crear la plantilla"]);
}

if(!CanUseArray($plantilla)){
    cLogging::Write(__LINE__." La plantilla llegó vacía");
    return EmitJSON(["msgerr" => "No se pudo crear la plantilla"]);
}

if($user->GetTemplateByUser($userID)){
    $user->plantilla = $plantilla;
    if(!$user->Set()){
        cLogging::Write(__LINE__." No se pudo actualizar la plantilla en BD");
        return EmitJSON(["msgerr" => "No se pudo actualizar la plantilla"]);
    }
} else {
    $record = [
        'usuario_id' => $userID,
        'plantilla' => $plantilla,
        'sys_usuario_id' => $objeto_usuario->id
    ];
    
    if(!$user->NewTemplate($record)){
        cLogging::Write(__LINE__." No se pudo crear la plantilla en BD");
        return EmitJSON(["msgerr" => "No se pudo crear la plantilla"]);
    }
}

ResponseOk(["msgok" => "Plantilla almacenada"]);