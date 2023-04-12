<?php
    /**
     * Escribe o registra un dato
     * Created: 2021-11-02 15:55:10
     * Author: api_creator
    */
    $this_file = substr(__FILE__, strlen(DIR_BASE))." ";
    $post = cleanArray($_POST);
    $data = array();
    
    if (!isset($post['nombre']) OR empty($post['nombre']) ){
        cLogging::Write($this_file." El campo nombre se encuntra vacío o no fue instanciado");
        $ws->SendResponse(404, null, 10, "No se encontro el parametro nombre.");
        return;
    }
    $data['nombre']= $post['nombre'];

    if (!isset($post['valor']) OR empty($post['valor']) ){
        cLogging::Write($this_file." El campo valor se encuntra vacío o no fue instanciado");
        $ws->SendResponse(404, null, 10, "No se encontro el parametro valor.");
        return;
    }
    $data['valor']= $post['valor'];

    if (!isset($post['tipo']) OR empty($post['tipo']) OR !in_array($post['tipo'],["CARGO","IMP"])){
        cLogging::Write($this_file." El campo tipo se encuntra vacío o no es un tipo valido");
        $ws->SendResponse(404, null, 10, "No se encontro el parametro tipo.");
        return;
    }
    $data['tipo']= $post['tipo'];
    
    require_once(DIR_model."impuestos".DS."class.impuestos.inc.php");

    $impuestos = new cImpuestos;

    if (!$impuestos->Create($data)){
        cLogging::Write($this_file." No fue posible crear el Impuesto/Cargo");
        $ws->SendResponse(400, null, 10, "No fue posible generar el registro.");
        return;
    }

    $ws->SendResponse(200, true);
    return;
?>