<?php
/*
	Pide al core y administra las simulaciones de préstamos para la calculadora.

	Created: 2021-08-30
	Author: DriverOp

*/

require_once(DIR_wsclient."class.wsv2Client.inc.php");

class cCalculadora extends cWsV2Client {
	
	public function __construct() {
		parent::__construct();
	}
	
/**
* Summary. Obtener una cotización. El core devuelve una cotización por omisión cuando no se le pasa monto ni plazo (ni siquiera valiendo cero).
* @param array $data Un Array con el monto, el plazo a cotizar y el id del plan
* @return object/null.
*/
	public function Get(array $data = null):?object {
		$result = null;
		try {
			
			$this->GetQuery('simular/', $data);
			if ($this->error) {
				$this->msgerr = ((DEVELOPE)?$this->msgerr.'<br />':'').'No podemos ofrecer una cotización en este momento';
				return null;
			}
			$result = $this->theData;
		} catch(Exception $e) {
			$this->SetErrorEx($e);
		}
		return $result;
	}
}