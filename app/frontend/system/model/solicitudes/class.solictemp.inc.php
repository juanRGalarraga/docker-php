<?php
/**
* Clase para el manejo de las solicitudes locales.
* Las solicitudes locales son aquellas iniciadas por los visitantes. En la tabla correspondiente (frontend_solicitudes) se almacenan los datos que el visitante proporciona, sin validación alguna y al solo efecto de tener persistencia en la sesión del visitante.
* Created: 2021-09-28
* Author: DriverOp
*/

require_once(DIR_includes."class.fechas.inc.php");
require_once(DIR_model."class.fundation.inc.php");

const estados_sesion_temporal_end = array('ENDOK', 'ENDFAIL');
const estados_sesion_temporal = array('INIT', 'HOLD')+estados_sesion_temporal_end;

class cSolicTemp extends cModels {
	
	const tabla_solicitudes = TBL_solicitudes;
	
	public $id = null;
	public $qMainTable = null;
	public $hace = 0;

	public function __construct() {
		parent::__construct();
		if (defined("DEVELOPE")) {
			$this->DebugOutput = DEVELOPE;
		}
		$this->actual_file = __FILE__;
		$this->mainTable = self::tabla_solicitudes;
		$this->qMainTable = SQLQuote($this->mainTable);
		$this->ResetInstance();
		$this->SetLog(__METHOD__, func_get_args());
	}

/**
* Summary. Obtener una solicitud previamente almacenada.
* @param int $id el id de la soliciud.
* @return object/null
*/
	public function GetSolic(int $id = null):?object {
		$this->SetLog(__METHOD__, func_get_args());
		$result = null;
		if (!is_null($id)) { $this->id = $id; }
		if (is_null($this->id)) { throw new Exception("Propiedad ->id no tiene valor."); }
		
		$this->sql = "SELECT * FROM $this->qMainTable AS `solicitud` WHERE `solicitud`.`id` = $this->id";
		if ($result = $this->Get()) {
			$this->hace = cFechas::SegundosEntreFechas($this->sys_fecha_modif, cFechas::Ahora());
			$result->hace = $this->hace;
		}
		return $result;
	}

/**
* Summary. Modificar una solicitud existente. Esto solamente actualiza el campo `data`.
* @param int $id el id de la soliciud.
* @return object/null
*/
	public function SetSolic(int $id = null):?object {
		$this->SetLog(__METHOD__, func_get_args());
		$result = null;
		if (!is_null($id)) { $this->id = $id; }
		if (is_null($this->id)) { throw new Exception("No hay solicitud activa, usar ->GetSolic()"); }
		$this->data = json_encode($this->data, JSON_FORCE_OBJECT + JSON_BIGINT_AS_STRING + JSON_PRESERVE_ZERO_FRACTION + JSON_UNESCAPED_UNICODE);
		if ($this->Set("`id` = ".$this->id)) {
			$result = $this->GetSolic();
		}
		return $result;
	}

/**
* Summary. Crear una solicitud temporal.
* @param 
* @return object/null.
*/
	public function Create() {
		$this->SetLog(__METHOD__, func_get_args());
		$this->user_agent = $_SERVER['HTTP_USER_AGENT']??$_SERVER['USERDOMAIN'];
		$this->ip = GetIP();
		$this->sys_fecha_modif = cFechas::Ahora();
		$this->sys_fecha_alta = cFechas::Ahora();
		$this->data = (!empty($this->data))?json_encode($this->data, JSON_FORCE_OBJECT + JSON_NUMERIC_CHECK + JSON_BIGINT_AS_STRING + JSON_PRESERVE_ZERO_FRACTION + JSON_UNESCAPED_UNICODE):null;
		if ($id = $this->New()) {
			return $this->GetSolic($id);
		} else {
			return null;
		}
		
	}
/**
* Summary. Cambiar estado a la sesión temporal.
* @param string $estado El estado al cual cambiar.
*/
	public function SetEstado(string $estado = 'INIT') {
		$this->SetLog(__METHOD__, func_get_args());
		$estado = strtoupper(substr($estado,0,10));
		if (in_array($estado, estados_sesion_temporal)) {
			if ($this->GetSolic()) {
				$this->estado = $estado;
				$this->Set("`id` = ".$this->id);
			}
		}
	}

	private function SetLog($method, $args) {
		cLogging::SetPostfix("onboarding");
		cLogging::Write($method. ": ".print_r($args, true));
	}
}
