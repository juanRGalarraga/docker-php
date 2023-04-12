<?php

require_once(DIR_model."tools".DS."class.wsclient.inc.php");

$wsclient = new cWsClient;

if (!$result = $wsclient->GetRandomCBU()) {
	return EmitJSON('No se pudo generar CBU porque '.$wsclient->msgerr);
}
ResponseOk($result);

