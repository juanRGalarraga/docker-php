<?php
    /**
     * Obtiene un registro dado un ID númerico
     * Created: [date]
     * Author: api_creator
     */

    require_once(DIR_model."prestamos".DS."class.prestamos.inc.php");
    $prestamos = new cPrestamos;

	
    if(!$id = SecureInt($ws->GetParam("id"))){
        $ws->SendResponse(404,'No se indico el prestamo',160); return;
    }   

    if(!$prestamo = $prestamos->Get($id)){
        $ws->SendResponse(404,'No existe el prestamo a buscar',160); return;
    };

    $ws->SendResponse(200,$prestamo,0); return;
    

?>