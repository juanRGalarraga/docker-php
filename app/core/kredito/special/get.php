<?php
    /**
     * Obtiene un registro dado un ID númerico
     * Created: 
     * Author: 
     */

    require_once(DIR_model."special".DS."class.special.inc.php");
    $special = new cSpecial();
    $reg_response = array();
    // $class = new [className];
    $reg_response = $special->GetEstadistiscasInicio();
    $ws->SendResponse(200, $reg_response);
