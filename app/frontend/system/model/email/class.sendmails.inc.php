<?php
/*
	Clase para pedirle al core que envíe un pin y luego validar el pin ingresado por el usuario.
	
	Created: 2021-10-15
	Author: DriverOp
	
	
*/

require_once(DIR_includes."class.fechas.inc.php");
require_once(DIR_model."wsclient".DS."class.wsv2Client.inc.php");

defined("SES_sendmail") || define("SES_sendmail","sendmails");

class cSendMail extends cWsV2Client {
	
	public $sesname = SES_sendmail;
	
	public function __construct() {
		parent::__construct();
		if (!isset($_SESSION[$this->sesname])) {
			$_SESSION[$this->sesname] = [];
		}
	}

/**
* Summary. Pide al core que envíe un correo electrónico de aceptación a la dirección almacenada en la solicitud.
* @param int $solicitudid. El ID de la solicitud.
* @return bool.
*/
	public function SendAceptance(int $solicitudid = null):?bool {
		$result = false;
		if ($this->PostQuery('sendmail/acceptance/'.$solicitudid)) {
			$result = ($this->http_nroerr == 200);
		}
		return $result;
	}

/**
* Summary. Pide al core que verifique el código escrito por el solicitante para la solicitud actual.
* @param int $solicitudid. El ID de la solicitud.
* @param string $codigo_aceptación.
* @return bool.
*/
	public function CheckAceptance(int $solicitudid = null, string $codigo_aceptación = null):?bool {
		$result = false;
		if ($this->GetQuery('sendmail/acceptance/check/'.$codigo_aceptación.'/'.$solicitudid)) {
			$result = ($this->http_nroerr == 200);
		}
		return $result;
	}
}
