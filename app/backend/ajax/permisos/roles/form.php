<?php 
    require_once(DIR_model."permisos".DS."class.rol.inc.php");
    require_once(DIR_model."permisos".DS."class.template.inc.php");

    $rol = new cRol();
    $template = new cTemplate();
    $post = CleanArray($_POST);

    if ($post['action'] !== "new"){
        if(!$rol->GetRol($post['id'])){
            cLogging::Write(__LINE__." No se pudo obtener el rol ");
            return EmitJSON(["msgerr" => "No es posible editar el rol"]);
        }
        $rol->formTitle = 'Editar rol';
    }

    $templates = $template->All(["id", "nombre"]);
?>

<div class="card my-0">
    <div class="card-header">
        <h3><?php echo $rol->formTitle ?? 'Nuevo rol' ?></h3>
    </div>
    <div class="card-body">
        <form id="formRol" name="formRol">
            <div class="col-12 mb-3">
                <input type="hidden" id="rolID" value="<?php echo $rol->id ?? ''?>">
                <label for="rolName" class="form-label">Nombre del rol</label>
                <input type="text" class="form-control" id="rolName" name="rolName" placeholder="Nombre del rol..." value="<?php echo $rol->nombre ?? ''?>">
            </div>
<?php 
            if($rol->existe):
?>
            <div class="col-12 form-group">
                <label for="estado">Estado</label>
                <select class="form-select form-select-lg" name="estado" id="estado">
<?php 
                    foreach (ESTADOS_VALIDOS as $key => $value):
?>
                        <option value="<?php echo $key ?>"><?php echo $value ?></option>
<?php 
                    endforeach;
?>
                </select>
            </div>
<?php
            endif;
            if(CanUseArray($templates)):
?>
            <div class="col-12 form-group">
                <label for="plantilla">Plantilla</label>
                <select class="form-select form-select-lg" name="templateID" id="templateID">
<?php
                    if(isset($rol->plantilla) and !empty($rol->plantilla)):
?>
                        <option value="actual" selected>Plantilla actual</option>
<?php
                    endif;
                    foreach ($templates as $value):
?>
                        <option value="<?php echo $value['id'] ?>"><?php echo $value['nombre'] ?></option>
<?php 
                    endforeach;
?>
                </select>
            </div>
<?php
            else:
?>
                <h6 class="empty-template-msg">No hay plantillas definidas. El sistema cargará una por defecto.</h6>
<?php
            endif;

            if(isset($rol->id)):
?>
        <div class="form-group">
            <label for="applyToAllUsers"> Aplicar a todos los usuarios con este rol</label>
            <input type="checkbox" name="applyToAllUsers" id="applyToAllUsers" value="true"><br>
            <small>* La plantillas que tengan aplicados los usuarios se reemplazarán por la actual</small>
        </div>
<?php
            endif;
?>
        </form>
    </div>
    <div class="card-footer d-flex justify-content-between">
        <button type="button" title="Cancelar Accion" class="btn w-25 btn-danger"  data-bs-dismiss="modal"><i class="mx-3 fas fa-backspace"></i> Volver</button>
        <button type="button" title="Realizar acción" class="btn w-25 btn-success"  onclick="SendRolForm()"><i class="mx-3 fas fa-check-double"></i> Confirmar</button>
    </div>
</div>