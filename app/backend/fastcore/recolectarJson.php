<?php
require_once("..\initialize.php");
require_once(DIR_includes."common.inc.php");
require_once(DIR_includes."class.logging.inc.php");
require_once(DIR_includes."controlErrors.inc.php");
require_once(DIR_model."wsv2".DS."class.wsv2Apis.inc.php");


$apis = new cApis();
$apis->GetRoutes('GET');



$uri = 'persona/23?miparametro=mivalor';
EchoLog('URI: '.$uri);
$salida = $apis->ParseURL($uri);

$uri = 'persona/100/extra';
EchoLog('URI: '.$uri);
$salida = $apis->ParseURL($uri);

$uri = 'persona/100/extra/telefonos';
EchoLog('URI: '.$uri);
$salida = $apis->ParseURL($uri);

