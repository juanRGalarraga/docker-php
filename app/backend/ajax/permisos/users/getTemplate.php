<?php
require_once(DIR_model."permisos".DS."class.user.inc.php");
require_once(DIR_model."permisos".DS."class.template.inc.php");
require_once(DIR_model."permisos".DS."class.rol.inc.php");

$user = new cUser();
$userTemplate = new cTemplate();
$rol = new cRol();

$post = CleanArray($_POST);
$id = SecureInt($post['userID'] ?? null);
$template = [];

if(!$id){
    cLogging::Write(__LINE__." El ID del usuario llegó vacío ");
    return cSidekick::ShowEmptySearchMessage();
}

//Si el usuario no tiene una plantilla se le setea una por defecto
if( $user->GetTemplateByUser($id) ) {
    $template = $user->plantilla;
} else {
    $template = $userTemplate->GetTemplate();
}

// ShowDie($template);
require_once(DIR_ajax."permisos".DS."templates".DS."table.php");

?>
<input type="hidden" name="userID" id="userID" value="<?php echo $id?>">
<div class="card-footer d-flex justify-content-between">
    <button type="button" title="Cancelar Accion" class="btn w-25 btn-danger" onclick="GotoContent('userTemplateMain', 'usersWrapperList');"><i class="mx-3 fas fa-backspace"></i> Volver</button>
    <button type="button" title="Realizar acción" class="btn w-25 btn-success" onclick="CreateUserTemplate()"><i class="mx-3 fas fa-check-double"></i> Confirmar </button>
</div>