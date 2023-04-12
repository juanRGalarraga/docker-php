<?php
/**
* Esto es simplemente para probar hacer un POST desde Lazarus.
*/

//cLogging::Write(__FILE__ ." POST Data: ".print_r($ws->params, true));

$linea = $ws->GetParam(['linea']);

$data = explode('¦',$linea);
$data = array_filter($data,"trim");

$ws->SendResponse(200,['cdp'=>$data[0],'msg'=>$data[1]]);


