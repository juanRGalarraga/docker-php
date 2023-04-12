<?php
/**
 * Actualiza un rol
 * @author Juan Galarraga
 * @created 2021-10-29
 */

require_once(DIR_model."permisos".DS."class.rol.inc.php");
require_once(DIR_model."permisos".DS."class.template.inc.php");

$this_file = substr(__FILE__, strlen(DIR_BASE)) . " ";

$rol = new cRol();
$objetoTemplate = new cTemplate();

$post = CleanArray($_POST);
$rolName = ArreglarMayusculas(trim($post['rolName'] ?? ''));
$rolID = SecureInt($post['rolID'] ?? null);
$rolEstado = $post['estado'] ?? 'HAB';
$templateID = SecureInt($post['templateID'] ?? null);
$applyToAllUsers = $post['applyToAllUser'] ?? false;
$plantilla = array();

if(!$rolID){
    cLogging::Write(__LINE__." El ID del rol llegó vacío.");
    return EmitJSON(['msgerr' => 'No se pudo actualizar el rol']);
}

if(!$rol->GetRol($rolID)){
    cLogging::Write(__LINE__." No se pudo obtener el rol ");
    return EmitJSON(['msgerr' => 'No se pudo actualizar el rol']);
}

if(empty($rolName)){
    cLogging::Write(__LINE__." El ID del rol llegó vacío.");
    return EmitJSON(['msgerr' => 'El nombre del rol no puede estar vacío.']);
}

//Si llega un ID es porque se seleccionó un template
if($templateID){
    //Si el usuario no quiere modificar la plantilla actual no la modifico
    if($templateID !== "actual"){
        if(!$objetoTemplate->GetPlantilla($templateID)){
            cLogging::Write(__LINE__." No se pudo obtener la plantilla $templateID");
            return EmitJSON(['msgerr' => "No se pudo crear el rol"]);
        }
        $rol->plantilla = $objetoTemplate->plantilla;
    } 
} else {
    cLogging::Write(__LINE__." El ID de la plantilla llegó vacío. Se generará una plantilla por defecto.");
    $rol->plantilla = $objetoTemplate->GetTemplate();
}

if(empty($rol->plantilla)){
    cLogging::Write(__LINE__." No se pudo generar una plantilla para el rol");
    return EmitJSON(['msgerr' => "No se pudo crear el rol"]);
}

if(!cCheckInput::estado_is_valid($rolEstado)){
    cLogging::Write(__LINE__." El estado [$rolEstado] no es válido.");
    return EmitJSON(['msgerr' => 'No se pudo actualizar el rol']);
}

$rol->nombre = $rolName;
if(!$rol->Set()){
    cLogging::Write(__LINE__." No se pudo obtener el rol ");
    return EmitJSON(['msgerr' => 'No se pudo actualizar el rol']);
}

//Si el usuario seleccionó la opción para aplicar la plantilla a todos los usuarios 
//con el rol actual
if($applyToAllUsers){
    require_once(DIR_model."permisos".DS."class.user.inc.php");
    $user = new cUser();
    //Busco el listado de todos los usuarios y me traigo solo los IDs
    $allUsers = $user->All(["id"]);
    try {
        foreach (new ArrayIterator($allUsers) as $user) {
            //Si el usuario ya tiene una plantilla, modificarla
            if($user->GetTemplateByUser($user['id'])){
                $user->Set("id = $user[id]");
            } else {
                //Si no tiene una plantilla se le crea una
                $user->NewRecord([
                    "usuario_id" => $user['id'],
                    "plantilla" => $plantilla
                ]);
            }
        }
    } catch (Exception $e) {
        cLogging::Write( $this_file . $e->getMessage() );
    }
}

ResponseOk(['msgok' => 'Rol actualizado']);