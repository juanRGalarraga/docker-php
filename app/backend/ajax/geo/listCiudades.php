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
    $geo = $geo->ListCiudadesByProvincias($id);
    
    $city = (isset($post['city']) AND !empty($post['city']))? $post['city']:0;

    if (!CanUseArray($geo)){
?>
        <option value="">Intente nuevamente Seleccionar un Provincia</option>
<?php
    }else{
        echo '<option value="">Seleccione una Ciudad</option>';
        foreach ($geo as $value) {
?>
            <option value="<?php echo $value->id; ?>" data-set="<?php echo $value->cp;?>" <?php echo ($value->id == $city)? "selected":'';?> ><?php echo $value->nombre; ?></option>
<?php
        }
    }
?>