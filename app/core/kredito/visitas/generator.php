<?php
    /**
     * 
     * Created: 2021-10-26 15:30:50
     * Author: Tom
     */


require_once(DIR_model."visitas".DS."class.visitas.inc.php");
$visitas = new cVisitas;

$reg = array();
$result = false;
for ($i=0; $i < 100; $i++) { 
    $reg['dominio'] = 'kredito.ar';
    $reg['visitas'] = rand(0,1000);
    $reg['pagina'] = 'inicio';
    $dias = intval($i/2);
    $reg['sys_fecha_alta'] = cFechas::Restar(cFechas::Hoy(),$dias);
    $result = $visitas->Create($reg);
}

return $ws->SendResponse(200, $result);

