<?php
    /*
        Maneja el alta y el update de un registro para impuesto o cargo
        Create: 2021-11-03
        Auhtor: Gonza
    */
    require_once(DIR_model."impuestos".DS."class.impuestos.inc.php");
    $this_file = substr(__FILE__, strlen(DIR_BASE))." ";
    
    $post = CleanArray($_POST);
    $msgerr = array();

    $inpNom = $post['inpNom'];
    $inpValor = $post['inpValor'];

    if (empty($inpNom)){
        $msgerr['inpNom'] ="El campo no puede estar vacío";
    }
    if (empty($inpValor)){
        $msgerr['inpValor'] ="El campo no puede estar vacío";
    }

    $data = array(
        "nombre"=> $inpNom,
        "valor"=> $inpValor
    );

    if (!isset($post['action'])){
        // 
        $inpTipo = $post['inpTipo'];
        if (empty($inpTipo) OR !in_array($inpTipo,['CARGO','IMP'])){
            $msgerr['inpTipo'] ="El campo no puede estar vacío";
        }
        $date["tipo"]= $inpTipo;

    }else{
        // 
        $inpAlias = $post['inpAlias'];
        if (empty($inpAlias)){
            $msgerr['inpAlias'] ="El campo no puede estar vacío";
        }
        $date["alias"]= $inpAlias;

        // 
        $selcCal = $post['selcCal'];
        if (empty($selcCal) OR !array_key_exists($selcCal,CALCULOS)){
            $msgerr['selcCal'] ="El campo no puede estar vacío";
        }
        $date["calculo"]= $selcCal;

        // 
        $selAplic = $post['selAplic'];
        if (empty($selAplic) OR !array_key_exists($selAplic,VALOR_APLICAR)){
            $msgerr['selAplic'] ="El campo no puede estar vacío";
        }
        $date["aplicar"]= $selAplic;

        // 
        $selState = $post['selState'];
        if (empty($selState) OR !array_key_exists($selState,ESTADOS_VALIDOS)){
            $msgerr['selState'] ="El campo no puede estar vacío";
        }
        $date["estado"]= $selState;

        // 
        $iva = (isset($post['aplic_IVA']))? true:false;
        $date["iva"]= $iva;
    }
    
    if (CanUseArray($msgerr)) {
        EmitJSON($msgerr);
        return;
    }

    $impuesto = new cImpuestos();
    if (!isset($post['action'])){
        if (!$impuesto->Create($date)){
            cLogging::Write($this_file." No fue posible crear el impuesto/cargo deseado.");
            ResponseOk(['error'=>"No fue posible realizar la accion deseada."]);
            return;
        }

        ResponseOk(["okok"=>"Registro Creado."]);
    }else{
        if (!$impuesto->Update($post['action'],$date)){
            cLogging::Write($this_file." No fue posible editar el impuesto/cargo deseado.");
            ResponseOk(['error'=>"No fue posible realizar la accion deseada."]);
            return;
        }

        ResponseOk(["okok"=>"Registro Actualizado."]);
    }
?>