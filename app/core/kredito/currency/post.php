<?php
    /**
     * Escribe o registra un dato
     * Created: [date]
     * Author: api_creator
     */

    require_once(DIR_model."divisas".DS."class.divisas.inc.php");
    $divisas = new cDivisas;

    if(!$resultupdated = $divisas->ArmarArchivo()){
        cLogging::Write(__LINE__." ".__FILE__." No se pudo armar el archivo ");
        $ws->SendResponse(404,'No hay cotizaciones actualmente',0); return;
    }

    $ws->SendResponse(200,$resultupdated); return;