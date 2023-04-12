<?php
    /*
    
    
    */
    
    require_once(DIR_model."impuestos".DS."class.impuestos.inc.php");
    $this_file = substr(__FILE__, strlen(DIR_BASE)) . " ";

    $post = CleanArray($_POST);
    $type = (empty($post['tipo'])? "IMP":$post['tipo']);
    $id = SecureInt(@$post['id']);

    if (empty($id)){
        cLogging::Write($this_file." El identificador esta vacío o no es numerico.");
        ResponseOk(['error'=>true]);
        return;
    }
    if (!in_array($type,['IMP','CARGO'])){
        cLogging::Write($this_file." El identificador esta vacío o no es numerico.");
        ResponseOk(['error'=>true]);
        return;
    }

    $impuestos = new cImpuestos;

    if (!$impuestos = $impuestos->Get($id)){
        cLogging::Write($this_file." No se encontro ningun registro con el id: $id.");
        ResponseOk(['error'=>true]);
        return;
    }
?>
<div class="card-header">
    <h4>Editar <?php echo ($type == "IMP")? 'Impuesto': 'Cargo';?></h4>
</div>
<div class="card-body">
    <form name="formImpuestos" id="formImpuestos">
        <input type="hidden" name="action" value="<?php echo isset($id);?>">
        <div class="row">
            <div class="col-12 col-lg-4 form-group">
                <label for="inpNom">Nombre</label>
                <input type="text" class="form-control" id="inpNom" name="inpNom" value="<?php echo (isset($impuestos->nombre))? $impuestos->nombre:"" ?>">
            </div>
            <div class="col-12 col-lg-4 form-group">
                <label for="inpAlias">Alias</label>
                <input type="text" class="form-control" id="inpAlias" name="inpAlias" value="<?php echo (isset($impuestos->alias))? $impuestos->alias:"" ?>">
            </div>
            <div class="col-12 col-lg-4 form-group">
                <label for="inpValor">Valor</label>
                <input type="number" class="form-control" id="inpValor" name="inpValor" value="<?php echo (isset($impuestos->valor))? $impuestos->valor:"" ?>">
            </div>
        </div>
        <div class="row">
            <div class="col-12 col-lg-4 form-group">
                <label for="selcCal">Tipo cálculo</label>
                <select name="selcCal" id="selcCal" class="form-select form-select-lg">
                    <?php foreach (CALCULOS as $key => $value) {   ?>
                        <option value="<?php echo $key; ?>" <?php echo (isset($impuestos->calculo) AND ($impuestos->calculo == $key))? "selected":""; ?>><?php echo $value; ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="col-12 col-lg-4 form-group">
                <label for="selAplic">Valor a Aplicar</label>
                <select name="selAplic" id="selAplic" class="form-select form-select-lg">
                    <?php foreach (VALOR_APLICAR as $key => $value) {   ?>
                        <option value="<?php echo $key; ?>" <?php echo (isset($impuestos->aplicar) AND ($impuestos->aplicar == $key))? "selected":"" ?>><?php echo $value; ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="col-12 col-lg-4 form-group">
                <label for="selState">Estado</label>
                <select name="selState" id="selState" class="form-select form-select-lg">
                    <?php foreach (ESTADOS_VALIDOS as $key => $value) {   ?>
                        <option value="<?php echo $key; ?>" <?php echo (isset($impuestos->estado) AND ($impuestos->estado == $key))? "selected":"" ?>><?php echo $value; ?></option>
                    <?php } ?>
                </select>
            </div>
        </div>
        <div class="row">
            <div class="col-12 d-flex justify-content-center">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="aplic_IVA" name="aplic_IVA" style="width: 55px;" <?php echo (isset($impuestos->aplica_iva) AND ($impuestos->estado == 1))? "checked":"" ?>>
                    <label class="form-check-label ms-2 mt-2" for="aplic_IVA">Aplicar IVA</label>
                </div>
            </div>
        </div>
    </form>
</div>

