<?php
    /**
     * Obtiene una región dado un ID númerico
     * Created: 2021-10-27
     * Author: Juan Galarraga
     */

require_once(DIR_model."geo".DS."class.geo.inc.php");

$id = SecureInt($ws->GetParam('id'));
if(!$id) return $ws->SendResponse(400, null, 10, "No se indicó ID de la región");

$geo = new cGeo();
if(!$region = $geo->GetRegion($id)) $ws->SendResponse(404, null, 13, "Región no encontrada");

$ws->SendResponse(200, $region);