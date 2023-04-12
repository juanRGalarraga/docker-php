<?php
/**
* Esto es simplemente para probar hacer un GET desde Lazarus.
*/

cLogging::Write(__FILE__ ." GET Data: ".print_r($ws->params, true));

$ws->SendResponse(200, 'Some response data');

