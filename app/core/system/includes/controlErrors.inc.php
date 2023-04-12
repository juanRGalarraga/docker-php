<?php
/*
	Control personalizado de errores.
	Created: 2021-05-29
	Author: DriverOp
*/

set_error_handler("controlDeErrores");


function controlDeErrores($nroerror, $msgerror, $errorfile = null, $errorline = null) {
	$list = array(1=>'E_ERROR',2=>'E_WARNING',4=>'E_PARSE',8=>'E_NOTICE',16=>'E_CORE_ERROR',32=>'E_CORE_WARNING',64=>'E_COMPILE_ERROR',128=>'E_COMPILE_WARNING',256=>'E_USER_ERROR',512=>'E_USER_WARNING',1024=>'E_USER_NOTICE',2048=>'E_STRICT',4096=>'E_RECOVERABLE_ERROR',8192=>'E_DEPRECATED',16384=>'E_USER_DEPRECATED',32767=>'E_ALL');
	$errorType = (isset($list[$nroerror]))?$list[$nroerror]:'UNKNOWN';
	$line = $errorfile." ".$errorline.": ".$errorType." ".$msgerror;
	cLogging::Write($line);
	$displayErrors = strtolower(ini_get("display_errors"));
	if (error_reporting() === 0 || $displayErrors) {
		return false;
	}
	return true;
}
