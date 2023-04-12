<?php
/**
 * Carga una plantilla o crea una nueva
 * @created 2021-11-01
 * @author Juan Galarraga
 */

require_once(DIR_model."permisos".DS."class.template.inc.php");

$plantilla = new cTemplate();
$post = CleanArray($_POST);
$id = SecureInt($post['templateID'] ?? null);
$template = [];

if($id){
  if(!$plantillaDatos = $plantilla->GetPlantilla($id)){
      cLogging::Write(__LINE__." No se pudo obtener el template $id");
      return cSidekick::ShowEmptySearchMessage();
  }
  $template = $plantillaDatos->plantilla ?? array();
} else {
  $template = $plantilla->GetTemplate();
}

ShowDie($template);
?>
  <div class="form-group">
      <label for="templateName">Nombre de la plantilla</label>
      <input type="text" name="templateName" id="templateName" class="form-control" value="<?php echo $plantillaDatos->nombre ?? ''?>">
  </div>
<?php
    require_once(DIR_ajax."permisos".DS."templates".DS."table.php");
?>
<div class="card-footer d-flex justify-content-between">
    <button type="button" title="Cancelar Accion" class="btn w-25 btn-danger" onclick="GotoContent('templateMain', 'templateWrapperList')"><i class="mx-3 fas fa-backspace"></i> Volver</button>
    <button type="button" title="Realizar acción" class="btn w-25 btn-success" onclick="CreateTemplate()"><i class="mx-3 fas fa-check-double"></i> Confirmar </button>
</div>

