<?php
    /**
     * Obtiene un registro dado un ID númerico
     * Created: [date]
     * Author: api_creator
     */

    require_once(DIR_model."cuotas".DS."class.cuotas.inc.php");
    require_once(DIR_model."prestamos".DS."class.prestamos.inc.php");
    $prestamos = new cPrestamos;
    $cuotas = new cCuotas;
    $cuotas_hist = new cCuotasHist;
    


    if(!$id = SecureInt($ws->GetParam("prestamoid"))){
        $ws->SendResponse(404,'No se indico el prestamo',160); return;
    }   

    $estados = false;
    
    if(!$estados = $ws->GetParam("estados")){
        cLogging::Write(__FILE__ ." ".__LINE__ ." No se indicaron estados");
    }

    if(!$prestamo = $prestamos->Get($id)){
        $ws->SendResponse(404,'No existe el prestamo a buscar',160); return;
    }

    if(!$list_cuotas = $cuotas_hist->GetByPrestamo($id,$estados)){ $ws->SendResponse(404,'No hay cuotas para el prestamo a buscar',160); return; }

    $ws->SendResponse(200,$list_cuotas,0); return;