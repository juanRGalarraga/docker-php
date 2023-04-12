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


    if(!$id = SecureInt($ws->GetParam("id"))){
        $ws->SendResponse(404,'No se indico el prestamo',160); return;
    }   

    if(!$prestamo = $prestamos->Get($id)){
        $ws->SendResponse(404,'No existe el prestamo a buscar',160); return;
    }

    if(!$list_cuotas = $cuotas->GetList($id)){ $ws->SendResponse(404,'No hay cuotas para el prestamo a buscar',160); return; }


     $ws->SendResponse(200,$list_cuotas,0); return;

?>