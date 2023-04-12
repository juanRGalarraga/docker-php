<?php
/*
	Clase para pedirle al core que envíe un pin y luego validar el pin ingresado por el usuario.
	
	Created: 2021-10-15
	Author: DriverOp
	
	
*/

require_once(DIR_includes."class.fechas.inc.php");
require_once(DIR_model."wsclient".DS."class.wsv2Client.inc.php");

defined("SES_smspin") || define("SES_smspin","smspin");
defined("SMSPIN_config_file") || define("SMSPIN_config_file","smsconfig_file.json");

class cSmsPin extends cWsV2Client {
	
	public $sesname = SES_smspin;
	private $config_file = SMSPIN_config_file;
	
	public function __construct() {
		parent::__construct();
		if (!isset($_SESSION[$this->sesname])) {
			$_SESSION[$this->sesname] = [];
		}
		$this->config_file = LoadConfig(SMSPIN_config_file); // Esta función está en initialize.php
		$this->GetConfig();
	}

/**
* Summary. Obtener los datos de configuración desde el core.
*/
	public function GetConfig() {
		if ($this->ReadConfig()) { return; }
		if ($data = $this->GetQuery('pin/config')) {
			$this->WriteConfig($data);
			$this->ReadConfig();
		}
	}
/**
* Summary. Pide al core que envíe un PIN al número de teléfono indicado.
* @param string $tel. El número de teléfono.
* @return string El PINID que es el ID del pin que luego se usa para validar el PIN enviado.
*/
	public function SendPin(string $tel = null):?string {
		$result = null;
		if ($this->PostQuery('pin/'.$tel)) {
			if (!empty($this->theData->pinId) and !empty($this->theData->smsStatus)) {
				if (strtoupper($this->theData->smsStatus) == 'MESSAGE_SENT') {
					$_SESSION[$this->sesname] = $this->theData;
					$result = $this->theData->pinId;
				}
			}
		}
		return $result;
	}
/**
* Summary. Pide al core que envíe un PIN al número de teléfono en la solicitud indicada.
* @param int $solicitud_id. El ID de la solicitud que contiene el número de teléfono.
* @return string El PINID que es el ID del pin que luego se usa para validar el PIN enviado.
*/
	public function SendPinRequest(int $solicitud_id = null):?string {
		$result = null;
		if ($this->PostQuery('pin/request/'.$solicitud_id)) {
			if (!empty($this->theData->pinId) and !empty($this->theData->smsStatus)) {
				if (strtoupper($this->theData->smsStatus) == 'MESSAGE_SENT') {
					$_SESSION[$this->sesname] = $this->theData;
					$result = $this->theData->pinId;
				}
			}
		}
		return $result;
	}
/**
* Summary. Pide al core que envíe de nuevo el PIN al PinID en la solicitud indicada.
* @param int $solicitud_id. El ID de la solicitud que contiene el número de teléfono.
* @return string El PINID que es el ID del pin que luego se usa para validar el PIN enviado.
*/
	public function ResendPinRequest(int $solicitud_id = null):?string {
		$result = null;
		if ($this->PostQuery('pin/request/resend/'.$solicitud_id)) {
			if (!empty($this->theData->pinId) and !empty($this->theData->smsStatus)) {
				if (strtoupper($this->theData->smsStatus) == 'MESSAGE_SENT') {
					$_SESSION[$this->sesname] = $this->theData;
					$result = $this->theData->pinId;
				}
			}
		}
		return $result;
	}
/**
* Summary. Dado un pin y un pinid le pide al core que verifique su validez.
* @param string $pin El pin escrito por el visitante.
* @param string $pinid El id del pin correspondiente.
* @return bool.
*/
	public function CheckPin(string $pin, string $pinid) {
		if (empty($pin)) { return false;}
		if (empty($pinid)) { return false;}
		$this->GetQuery('pin/'.$pinid.'/'.$pin);
		if ($this->http_nroerr == 200 and $this->core_nroerr == 0) {
			return true;
		}
		return false;
	}
/**
* Summary. Verificar la validez de un PIN desde el ID de solicitud (del core).
* @param string $pin El pin escrito por el visitante.
* @param string $solicitud_id El id de la solicitud remota.
* @return bool.
*/
	public function CheckPinRequest(string $pin, string $solicitud_id) {
		if (empty($pin)) { return false;}
		if (empty($solicitud_id)) { return false;}
		$this->GetQuery('pin/request/'.$solicitud_id.'/'.$pin);
		if ($this->http_nroerr == 200 and $this->core_nroerr == 0) {
			return true;
		}
		return false;
	}
/**
* Summary. Escribe la configuración para ser usada más tarde.
* @param object $data La confioguración a ser guardada
* @return bool
*/
	private function WriteConfig(object $data = null):?bool {
		$data->lastmodif = cFechas::Ahora();
		if (file_put_contents($this->config_file, json_encode($data))) {
			return true;
		} else {
			return false;
		}
	}
/**
* Summary. Lee la configuración desde el archivo correspondiente.
* @return object/null.
*/
	private function ReadConfig():?object {
		$result = null;
		if (!ExisteArchivo($this->config_file)) { return null; }
		$aux = file_get_contents($this->config_file);
		if (empty($aux)) { return null; }
		$result = json_decode($aux);
		if (json_last_error() != JSON_ERROR_NONE) { return null; }
		if (empty($result->lastmodif)) { return null; }
		$dif = cFechas::SegundosEntreFechas($result->lastmodif, date('Y-m-d H:i:s'))/60/60;
		if ($dif >= 24) { return null; }
		foreach($result as $key => $value) {
			$this->$key = $value;
		}
		return $result;
	}
}
