<?php 
/*
    Buscar y responder las visitas de las paginas
    Author: Tom
    Created : 26-10-2021

*/

require_once(DIR_model.'graficos'.DS.'class.graficos.inc.php');
$this_file = substr(__FILE__, strlen(DIR_BASE)) . " ";

$visitas = new cVisitas();

$response = array();
$data = array("site"=>"kredito.ar","desde"=>"2021-10-01","hasta"=>"2021-10-31");
if(!$response = $visitas->GetVisitas($data)){
    cLogging::Write(" No se obtuvieron visitas");
}

ResponseOk($response);


?>