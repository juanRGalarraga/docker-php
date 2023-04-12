<?php

$config = new stdClass;

$config->enabled = $sysParams->Get("smspin_enabled",true);
$config->retryNumber = $sysParams->Get("smspin_retryNumber",3);
$config->retryTimeout = $sysParams->Get("smspin_retryTimeout",5);
$config->pinFormat = $sysParams->Get("smspin_pinFormat","\d{5}");

$ws->SendResponse(200, $config);





