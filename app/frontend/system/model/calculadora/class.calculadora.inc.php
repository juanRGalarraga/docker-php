<?php
/*
	Pide al core y administra las simulaciones de préstamos para la calculadora.

	Created: 2021-08-30
	Author: DriverOp

*/

require_once(DIR_model."wsclient".DS."class.wsv2Client.inc.php");

defined("SES_onboarding") || define("SES_onboarding", "onboarding");

class cCalculadora extends cWsV2Client {
	
	private $sesname = SES_onboarding;
	public $plan = null;
	public $solicitud = null;
	public $respuesta = null;
	
	
	public function __construct() {
		parent::__construct();
		if (!isset($_SESSION[$this->sesname])) {
			$_SESSION[$this->sesname] = [];
			$_SESSION[$this->sesname]['cotizacion'] = new stdClass;
		}
		$this->solicitud = $_SESSION[$this->sesname]['solicitud']??null;
		  $this->transId = $_SESSION[$this->sesname]['transId']??null;
		$this->respuesta = $_SESSION[$this->sesname]['cotizacion']->respuesta??null;
	}
	
/**
* Summary. Obtener una cotización. El core devuelve una cotización por omisión cuando no se le pasa monto ni plazo (ni siquiera valiendo cero).
* @param array $data Un Array con el monto y el plazo a cotizar.
* @return object/null.
*/
	public function Get(array $data = null):?object {
		$result = null;
		try {
			
			if (isset($data['monto'])) { $data['monto'] = number_format($data['monto'],2,'.',''); }
			else {
				if ($this->GetSes('Capital')) { $data['monto'] = number_format($this->GetSes('Capital'),2,'.',''); }
			}

			if (isset($data['plazo'])) { $data['plazo'] = number_format($data['plazo'],0,'.',''); }
			else {
				if ($this->GetSes('Periodo')) {	$data['plazo'] = number_format($this->GetSes('Periodo'),0,'.',''); }
			}
			
			if (!isset($data['solic_id'])) {
				$data['solic_id'] = $_SESSION[$this->sesname]['solic_id']??null;
			}
			$data['paso'] = $_SESSION[$this->sesname]['alias']??null;
			
			$data['ip'] = GetIP();
			if (isset($this->solicitud) and !empty($this->solicitud->id)) {
				$data['solic_id'] = $this->solicitud->id;
			}
			$this->GetQuery('simular/', $data);
			if ($this->error) {
				$this->msgerr = ((DEVELOPE)?$this->msgerr.'<br />':'').'No podemos ofrecer una cotización en este momento';
				return null;
			}
			$result = $this->theData;
			$this->SetRespuesta($result);
		} catch(Exception $e) {
			$this->SetErrorEx($e);
		}
		return $result;
	}
/**
* Summary. Devuelve el valor de una variable de sesión o null en caso que no exista.
* @param string $value El nombre de la variable.
* @return mixed/null
*/
	public function GetSes(string $value) {
		if (!is_null($this->respuesta) and is_object($this->respuesta)) {
			if (isset($this->respuesta->$value)) {
				return $this->respuesta->$value;
			}
		}
		return null;
	}
/**
* Summary. Establecer los datos de la respuesta.
* @param object $respuesta.
*/
	public function SetRespuesta(object $respuesta = null) {
		$this->respuesta = $respuesta;
		if (empty($_SESSION[$this->sesname]['cotizacion'])) { $_SESSION[$this->sesname]['cotizacion'] = new stdClass; }
		if (empty($_SESSION[$this->sesname]['cotizacion']->respuesta)) { $_SESSION[$this->sesname]['cotizacion']->respuesta = new stdClass; }
		$_SESSION[$this->sesname]['cotizacion']->respuesta = $respuesta;
	}
/**
* Summary. Obtener un plan de préstamos desde el core.
* @param int $plan_id default null El ID del plan buscado.
* @return bool.
* ********************** NO SE ESTÄ USANDO ******************************
*/
	public function GetPlan(int $plan_id = null):bool {
		$result = false;
		try {
			$this->GetQuery('planes/'.$plan_id);
			if (!$this->error) {
				$this->plan = $this->theData;
				if (!isset($_SESSION[$this->sesname]['cotizacion'])) { $_SESSION[$this->sesname]['cotizacion'] = new stdClass; }
				$_SESSION[$this->sesname]['cotizacion']->respuesta = $this->theData;
				$result = true;
			}
		} catch(Exception $e) {
			$this->SetErrorEx($e);
		}
		return $result;
	}

/**
* Summary. Devuelve un 'atributo' de la cotización si existe en la sesión. No la pide al core.
* @param string $attr El nombre del atributo.
* @return mixed
*/
	public function GetAttr(string $attr = null) {
		$result = null;
		if (!is_null($this->respuesta) and is_object($this->respuesta) and !empty($attr)) {
			if (!empty($this->respuesta->$attr)) { $result = $this->respuesta->$attr; }
		}
		return $result;
	}
}