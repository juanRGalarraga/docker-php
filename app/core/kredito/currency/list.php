<?php
    /**
     * Obtiene un listado de los registros
     * Created: [date]
     * Author: api_creator
     */

    require_once(DIR_model."divisas".DS."class.divisas.inc.php");
    $divisas = new cDivisas;

    if(!$cotizaciones = $divisas->GetCotizaciones()){
        cLogging::Write(__LINE__." ".__FILE__." No se encontraron cotizaciones actualmente");
        $ws->SendResponse(404,'No hay cotizaciones actualmente',0); return;
    }

    $ws->SendResponse(200,$cotizaciones); return;
