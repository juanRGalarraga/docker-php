<?php
/*
	Para comunicarse con el fakeburo.
	Created: 2021-10-14
	Author: DriverOp
*/

require_once(DIR_includes.'class.fechas.inc.php');
require_once(DIR_model."class.sysparams.inc.php");
require_once(DIR_integraciones."fakeburo".DS.'class.fakeburo_base.inc.php');

class cFakeburo extends cFakeburoBase {
    
    public $reintentos = 5;
    public $challenge = null;
	public $negocio_id = null;
	public $account_id = null;
	public $view_id = 'owner';



    // Se setea el usuario y la contraseña
    function __construct($username = null, $password = null) {
		global $sysParams;
		parent::__construct($username, $password);
		$this->referer = $sysParams->Get('fakeburo_referer',null);
    }
    
    public function Ejecutar($tipo = NULL, $data = NULL, $checktoken = true) {
		try {
			$result = false;
			$c = $this->reintentos;
			$tipo = (empty($tipo))?$this->method:$tipo;
			do {
				if ($c < 3) { $this->SetLog(__METHOD__." Está tardando demasiado...".$c);}
				if (parent::Commit($tipo, $data, $checktoken)) {
					$result = $this->parsed_response;
					cLogging::Write(__METHOD__." ".print_r($result,true));
				}
				$c--;
			} while(($this->curl_nroerr == 28) and ($c > 0));
			if ($c < 1) { throw new Exception(__LINE__." Demasiados reintentos después de detectar TIMEOUT. Me doy por vencido."); }
		} catch(Exception $e) {
			$this->SetError(__FILE__,__METHOD__,$e->GetMessage());
		}
		return $result;
    }
	
	public function GetScore($dni) {
		$this->url = 'get/'.urlencode($dni);
		if ($result = $this->Ejecutar('GET')) {
			$result = json_decode(json_encode($result['data']??null));
		}
		return $result;
	}
}