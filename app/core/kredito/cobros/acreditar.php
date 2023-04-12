<?php
/**
    * Escribe o registra un dato
    * Created: 
    * Author: api_creator
*/
require_once(DIR_model."cobros".DS."class.cobros.inc.php");
require_once(DIR_model."prestamos".DS."class.prestamos.inc.php");
$cobros = new cCobros;
$prestamos = new cPrestamos;
$msgerr = array();

$cobro_id = $ws->GetParam("cobros");
if(empty($cobro_id)){
    $ws->SendResponse(404,NULL,'No se indico el cobro'); return;
}

$errores = array();

if(CanUseArray($cobro_id)){
    foreach($cobro_id as $key => $value){
        if(!$cobros->Get($value)){
            $errores[] = $value;
        }

        if(!$response = $cobros->AcreditarCobro()){
            $errores[] = $value;
        }
    }
}else{
    if(!$cobros->Get($cobro_id)){
        $ws->SendResponse(404,NULL,' No se encontro el cobro indicado'); return;
    }

    if(!$response = $cobros->AcreditarCobro()){
        $ws->SendResponse(500,'No se pudo acreditar el cobro'); return;
    }
}


if(CanUseArray($errores) && !empty($errores)){
    $ws->SendResponse(404,$errores); return;
}
$ws->SendResponse(200,$response); return;