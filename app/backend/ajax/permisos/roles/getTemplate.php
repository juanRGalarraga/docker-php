<?php
require_once(DIR_model."permisos".DS."class.rol.inc.php");

$rol = new cRol();

$post = CleanArray($_POST);
$id = SecureInt($post['rolID'] ?? null);
$template = [];

if(!$id){
    cLogging::Write(__LINE__." El ID del rol llegó vacío ");
    return cSidekick::ShowEmptySearchMessage();
}
                                                        
if(!$rol->GetRol($id)){
    cLogging::Write(__LINE__." No se pudo obtener el rol $id");
    return cSidekick::ShowEmptySearchMessage();
}

$template = $rol->plantilla ?? array();
?>
<input type="hidden" id="rolID" name="rolID" value="<?php echo $rol->id?>">
<?php
require_once(DIR_ajax."permisos".DS."templates".DS."table.php");
?>
<div class="card-footer d-flex justify-content-between">
    <button type="button" title="Cancelar Accion" class="btn w-25 btn-danger" onclick="GotoContent('rolTemplateMain', 'rolesWrapperList');"><i class="mx-3 fas fa-backspace"></i> Volver</button>
    <button type="button" title="Realizar acción" class="btn w-25 btn-success" onclick="CreateRolTemplate()"><i class="mx-3 fas fa-check-double"></i> Aplicar plantilla </button>
</div>