<?php 
    require_once(DIR_model."geo".DS."class.geo.inc.php");
    $this_file = substr(__FILE__, strlen(DIR_BASE)) . " ";

    $post = CleanArray($_POST);
    $id = SecureInt($post['id']);

    if (empty($id)){
        cLogging::Write($this_file." No es posbiel realizar la acción");
        return;
    }

    $geo = new cGeo;
    $geo = $geo->ListProvinciasByPais($id);

    $prov = (isset($post['prov']) AND !empty($post['prov']))? $post['prov']:0;

    if (!CanUseArray($geo)){
?>
        <option value="">Intente nuevamente Seleccionar un País</option>
<?php
    }else{
        echo '<option value="">Seleccione una Provincia</option>';
        foreach ($geo as $value) {
?>
            <option value="<?php echo $value->id; ?>" <?php echo ($value->id == $prov)? "selected":'';?>><?php echo $value->nombre; ?></option>
<?php
        }
    }
?>