<?php
    /**
     * Obtiene un registro dado un ID númerico
     * Created: 2021-11-02 15:55:10
     * Author: api_creator
     */

    require_once(DIR_model."impuestos".DS."class.impuestos.inc.php");

    if(!$id = SecureInt($ws->GetParam("id"))){
        $ws->SendResponse(404,null,160,'No se indico el Impuesto');
        return;
    }   
    $impuestos = new cImpuestos;

    if(!$impuestos = $impuestos->Get($id)){
        $ws->SendResponse(404,'No existe el impuesto/cargo',160); 
        return;
    };

    $ws->SendResponse(200,$impuestos,0); return;
?>