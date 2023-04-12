<?php
/*
	Clase para obtener información sobre un CBU.
	Created: 2021-10-21
	Author: DriverOp
*/

require_once(DIR_model."wsclient".DS."class.wsv2Client.inc.php");


class cCBU extends cWsV2Client {
	
	
	public function __construct() {
		parent::__construct();
	}

/**
* Summary. Obtener información sobre un CBU.
* @param string $cbu El CBU de la persona buscada.
* @param int $solicitud_id Alternativamente se puede validar el CBU respecto de la solicitud indicada.
* @return object/null.
*/
	public function GetInfo(string $cbu = null, int $solicitud_id = null) {
		$result = null;
		try {
			if (empty($cbu)) { $cbu = $this->cbu; }
			if (empty($cbu)) { throw new Exception("No se estableció CBU."); }
			$cbu = substr(trim($cbu),0,22);
			$result = $this->GetQuery('infocbu/'.$cbu.((!empty($solicitud_id))?'/'.$solicitud_id:''));
			$this->CheckForErrors();
		} catch(Exception $e) {
			$this->SetLog($e);
		}
		return $result;
	}

/**
* Summary. Obtener una persona a partir de un CBU.
* @param string $cbu El CBU de la persona buscada.
* @return mixed.
* @note Hay un método en la clase cPersona que hace lo mismo que esto.
*/
	public function GetPersona(string $cbu = null) {
		$result = null;
		try {
			if (empty($cbu)) { $cbu = $this->cbu; }
			if (empty($cbu)) { throw new Exception("No se estableció CBU."); }
			$cbu = substr(trim($cbu),0,22);
			$result = $this->GetQuery('checkcbu/'.$cbu);
			$this->CheckForErrors();
		} catch(Exception $e) {
			$this->SetLog($e);
		}
		return $result;
	}
}

