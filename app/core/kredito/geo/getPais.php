<?php
    /**
     * Obtiene un país dado un ID númerico
     * Created: 2021-10-27
     * Author: Juan Galarraga
     */

require_once(DIR_model."geo".DS."class.geo.inc.php");

$id = SecureInt($ws->GetParam('id'));
if(!$id) return $ws->SendResponse(400, null, 10, "No se indicó ID del país");

$geo = new cGeo();
if(!$pais = $geo->GetPais($id)) $ws->SendResponse(404, null, 13, "País no encontrado");

$ws->SendResponse(200, $pais);