<?php 
    require_once(DIR_model."geo".DS."class.geo.inc.php");
    $this_file = substr(__FILE__, strlen(DIR_BASE)) . " ";

    $post = CleanArray($_POST);

    $geo = new cGeo;
    $geo = $geo->ListPaises();

    $pais = (isset($post['pais']) AND !empty($post['pais']))? $post['pais']:0;
    
    if (!CanUseArray($geo)){
?>
        <option value="">Intente recargar la Pagina</option>
<?php
    }else{
        echo '<option value="">Seleccione un País</option>';
        foreach ($geo as $value) {
?>
            <option value="<?php echo $value->id; ?>" <?php echo ($value->id == $pais)? "selected":'';?> ><?php echo $value->nombre_pais; ?></option>
<?php
        }
    }
?>