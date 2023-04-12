<?php 
    /*
        Actualiza un registro indicado
        Create: 2021-11-05
        Author: Gonza
    */
    require_once(DIR_model."impuestos".DS."class.impuestos.inc.php");
    $impuesto = new cImpuestos();

    // Verificar que este indicado el elemento a editar
    if(!$id = SecureInt($ws->GetParam("id"))){
        $ws->SendResponse(400,null,160,'No se índico el Impuesto/cargo');
        return;
    }
    // Controlo que existe el cargo/impuesto a buscara
    if (!$impuesto->Get($id)){
        $ws->SendResponse(404,null,160,'No se encontró Impuesto/cargo de');
        return;
    }
    
    $data = array();

    // Sacamos lo que viene por put
    $put = array();
    parse_str(file_get_contents('php://input'), $put);
    $put = array_change_key_case($put);

    // Controlamos que no este vacío y que este bien armado
    if (!CanUseArray($put)){
        $ws->SendResponse(400,null,160,'Los datos estan vacíos.');
        return;
    }

    // 
    if (!isset($put['alias']) OR empty($put['alias'])){
        $ws->SendResponse(400,null,160,' Falta indicar el Alias.');
        return;
    }
    $impuesto->alias= $put['alias'];

    // 
    if (!isset($put['aplicar']) OR !array_key_exists($put['aplicar'],VALOR_APLICAR)){
        $ws->SendResponse(400,null,160,' Falta indicar la aplicación.');
        return;
    }
    $impuesto->aplicar= $put['aplicar'];

    // 
    if (!isset($put['calculo']) OR !array_key_exists($put['calculo'],CALCULOS)){
        $ws->SendResponse(400,null,160,' Falta indicar el calculo.');
        return;
    }
    $impuesto->calculo= $put['calculo'];

    // 
    if (!isset($put['estado']) OR !array_key_exists($put['estado'],ESTADOS_VALIDOS)){
        $ws->SendResponse(400,null,160,' Falta indicar el estado.');
        return;
    }
    $impuesto->estado= $put['estado'];

    // 
    if (isset($put['iva']) AND !empty($put['iva'])){
        $impuesto->aplica_iva = 1;       
    }else{
        $impuesto->aplica_iva = 0;
    }

    if (!$impuesto->Set()){
        $ws->SendResponse(500,null,160,' No se pudo actualizar el registro.');
        return;
    }

    $ws->SendResponse(200,true);
    return;
?>