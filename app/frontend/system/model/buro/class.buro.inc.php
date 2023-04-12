<?php
/**
* Summary. Interfaz con el core para solicitar score crediticio.
* Created: 2021-10-15
* Author: DriverOp
*/

require_once(DIR_model."class.jwtbase.inc.php");
require_once(DIR_model."wsclient".DS."class.wsv2Client.inc.php");


class cBuro extends cWsV2Client {
	
	public $msgerr = [];
	public $origen = 'FRONT';

	public function __construct() {
		parent::__construct();
		$this->sent = false;
	}

/**
* Summary. Pide iniciar una nueva solicitud.
* @param mixed $data Los datos de inicialización de la solicitud.
* @return int El id de la solicitud devuelta por el core.
*/
	public function GetScore(string $dni = null):?object {
		$result = null;
		try {
			$data['origen'] = $this->origen;
			if ($salida = $this->GetQuery('buro/info/'.$dni)) {
				$result = $salida;
				if (!is_object($result)) {
					$result = json_decode(json_encode($result));
				}
			}
		} catch(Exception $e) {
			$this->SetLog($e);
		}
		return $result;
	} // GetScore
} // Class