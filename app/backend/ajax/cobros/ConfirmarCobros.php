<?php
/**
 * 
 * Created: 
 * Author: 
 */
    require_once(DIR_model."cobros".DS."class.cobros.inc.php");

    $this_file = substr(__FILE__, strlen(DIR_BASE)) . " ";
    
    $acciones = ['confirm','decline'];

    $post = CleanArray($_POST);
    
    if(!isset($post['elementos'])){
        cLogging::Write($this_file." ".__LINE__." No se especificaron elementos.");
        EmitJSON("¡Oops!. Tuvimos un error, intenta recargar la página.");
        return;
    }

    if(!isset($post['accion'])){
        cLogging::Write($this_file." ".__LINE__." No se especificaron acciones.");
        EmitJSON("¡Oops!. Tuvimos un error, intenta recargar la página.");
        return;
    }
    $accion = $post['accion'];
    if(!in_array($accion, $acciones)){
        cLogging::Write($this_file." ".__LINE__." Acción no válida.");
        EmitJSON("¡Oops!. Ésta acción no es válida.");
        return;
    }
    
    $reg = array();
    $reg['cobros'] = explode(",",$post['elementos']);
    $reg['cobros'] = CleanArray($reg['cobros']);

    
    if(!CanUseArray($reg['cobros'])){
        cLogging::Write($this_file." ".__LINE__." No se seleccionaron cobros.");
        EmitJSON("No se seleccionaron cobros.");
        return;
    }

    $cobros = new cCobros();

    if($accion == 'confirm'){
        if(!$rs = $cobros->CobrosAcreditar($reg)){
            cLogging::Write($this_file." ".__LINE__." No se puedieron confirmar los cobros.");
            EmitJSON("No se pudieron confirmar los cobros.");
            return;
        }
    }else{
        if(!$rs = $cobros->CobrosRechazar($reg)){
            cLogging::Write($this_file." ".__LINE__." No se puedieron rechazar los cobros.");
            EmitJSON("No se pudieron rechazar los cobros.");
            return;
        }
    }

    
    ResponseOk();
?>