<?php
/**
	Esto es lo que lidia con InfoBip para reenviar el PIN.
	
	La entrada es $pinId y $telnumber
	La salida es $infobip_response
	
	Created: 2021-10-19
	Author: DriverOp
*/

$infobip_response = array();
if ($sysParams->Get('infobip_modo_test', false)) {
	// Si está en modo test, simular una salida válida y regresar.
	$infobip_response["response"] = [
		"pinId"=>$pinId,
		"to"=>$telnumber,
		"ncStatus"=>"NC_NOT_CONFIGURED",
		"smsStatus"=>"MESSAGE_SENT",
		"test"=>"on"
	];
	$continue = $ws->SendResponse(200, $infobip_response["response"]); return;
}

require_once(DIR_integraciones."infobip".DS."class.sms_client.inc.php");

$infobip = new cInfobipSms();

$infobip->modo_test = false;
$infobip->username = $sysParams->Get('infobip_username',null);
$infobip->password = $sysParams->Get('infobip_password',null);
$infobip->appid = $sysParams->Get('infobip_app_id',null);
$infobip->templateid = $sysParams->Get('infobip_template_id',null);
$infobip->base_url = $sysParams->Get('infobip_ws_url',null);

$infobip_response = $infobip->ReEnviarPin($pinId);
if (!$infobip_response) {
	cLogging::Write(__FILE__ ." ".__LINE__ ." cInfobipSms: ".$infobip->errmsg);
	$continue = $ws->SendResponse(500, 'PIN no fue reenviado');
	return;
}

$reg_log = array(
	'notificador_id' => 1,//$sms->id,
	'fechahora' => cFechas::Ahora(),
	'request_data' => json_encode($infobip_response["request"]),
	'request_method' => $infobip_response["method"],
	'response_code' => $infobip_response["http_code"],
	'response_data' => json_encode($infobip_response["response"]),
	'request_url' => $infobip_response["url"],
);
$objeto_db->Insert(TBL_notificador_logs, $reg_log);
if ($objeto_db->error) {
	cLogging::Write(__FILE__ ." ".__LINE__ ." DBerr: ".$objeto_db->errmsg);
}
