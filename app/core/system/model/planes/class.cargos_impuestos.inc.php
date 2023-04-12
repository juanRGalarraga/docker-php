<?php
/*
	Clase para manejar los cargos e impuestos de los planes de préstamos.
	Created: 2021-10-31
	Author: DriverOp
*/

require_once(DIR_model."class.fundation.inc.php");

class cCargosImp extends cModels {

	const tabla_cargos_impuestos = TBL_cargos_impuestos;
	const tabla_cargos_planes = TBL_cargos_planes;
	
	public $qMainTable = null;
	public $todos = false; // Los cargos/impuestos no habilitados también se incluyen en las consultas.
	public $selectFields = '*';

	
	function __construct() {
		parent::__construct();
		$this->mainTable = self::tabla_cargos_impuestos;
		$this->qMainTable = SQLQuote(self::tabla_cargos_impuestos);
		$this->ResetInstance();
	}

/**
* Summary. Devuelve un único cargo o impuesto según el valor de propiedad ->id.
* @return object
*/
	public function Get():?object {
		$this->sql = "SELECT ".$this->selectFields." FROM ".$this->qMainTable." WHERE 1=1 AND `id` = ".$this->id." ";
		if (!$this->todos) {
			$this->sql .= "AND `estado` = 'HAB' ";
		}
		return parent::Get();
	}

/**
* Summary. Devuelve la lista de cargos o impuestos según el parámetro indicado o todos si no se indica.
* @param string $tipo El tipo de cargo/impuesto (CARGO o IMP)
* @return array
*/
	public function GetAll($tipo = null):?array {
		$result = [];
		$this->sql = "SELECT ".$this->selectFields." FROM ".$this->qMainTable." WHERE 1=1 ";
		if (!$this->todos) {
			$this->sql .= "AND `estado` = 'HAB' ";
		}
		if (!empty($tipo) and $this->In($tipo, 'tipo')) {
			$tipo = strtoupper($tipo);
			$this->sql .= "AND (`tipo` = '".$tipo."') ";
		}
		$this->sql .= "ORDER BY `tipo` ";
		if ($fila = $this->FirstQuery()) {
			do {
				$result[] = $fila;
			}while($fila = $this->Next());
		}
		return $result;
	}

/**
* Summary. Devuelve la lista de cargos o impuestos para un plan según el parámetro indicado o todos si no se indica.
* @param int $planId El ID del plan.
* @param string $tipo optional El tipo de cargo/impuesto (CARGO o IMP)
* @return array
*/
	public function GetAllByPlan(int $planId, $tipo = null):?array {
		$result = [];
		$this->sql = "SELECT ".$this->MakeSelect()." FROM ".$this->qMainTable.", ".self::tabla_cargos_planes." AS `cargos_planes` WHERE 1=1 AND `cargos_planes`.`plan_id` = ".$planId." AND `cargos_planes`.`cargo_id` = ".$this->qMainTable.".`id` ";
		if (!$this->todos) {
			$this->sql .= "AND ".$this->qMainTable.".`estado` = 'HAB' ";
		}
		if (!empty($tipo) and $this->In($tipo, 'tipo')) {
			$tipo = strtoupper($tipo);
			$this->sql .= "AND (".$this->qMainTable.".`tipo` = '".$tipo."') ";
		}
		$this->sql .= "ORDER BY ".$this->qMainTable.".`tipo` ";
		if ($fila = $this->FirstQuery()) {
			do {
				$result[] = $fila;
			}while($fila = $this->Next());
		}
		return $result;
	}


/**
* Summary. Devuelve la lista de cargos o impuestos para un plan agregando una marca cuando el cargo/impuesto está en la lista del plan indicado.
* @param int $planId El ID del plan.
* @param string $tipo optional El tipo de cargo/impuesto (CARGO o IMP)
* @return array
*/
	public function GetAllMarkByPlan(int $planId, $tipo = null):?array {
		$result = [];
		$this->sql = "SELECT ".$this->MakeSelect().", `cargos_planes`.`plan_id` , IF(`cargos_planes`.`plan_id` = ".$planId." AND `cargos_planes`.`estado` = 'HAB','SI','NO') AS `included`
		FROM ".$this->qMainTable."
		LEFT JOIN ".self::tabla_cargos_planes." AS `cargos_planes` ON `cargos_planes`.`cargo_id` = `cargos_impuestos`.`id` AND `cargos_planes`.`plan_id` = ".$planId."
		WHERE 1=1 ";
		if (!$this->todos) {
			$this->sql .= "AND ".$this->qMainTable.".`estado` = 'HAB' ";
		}
		if (!empty($tipo) and $this->In($tipo, 'tipo')) {
			$tipo = strtoupper($tipo);
			$this->sql .= "AND (".$this->qMainTable.".`tipo` = '".$tipo."') ";
		}
		$this->sql .= "ORDER BY ".$this->qMainTable.".`tipo` ";
		if ($fila = $this->FirstQuery()) {
			do {
				$result[] = $fila;
			}while($fila = $this->Next());
		}
		return $result;
	}

/**
* Summary. Eliminar todos los cargos/impuestos del plan.
*/
	public function Clear(int $plan_id = null) {
		if (!is_null($plan_id)) $this->Update(self::tabla_cargos_planes,['estado' => 'ELI'], "`plan_id` = ".$plan_id);
	}
/**
* Summary. Arma la lista de campos para la cláusula SELECT
* @return string.
*/
	private function MakeSelect() {
		if (trim($this->selectFields) == '*') { return '*'; }
		$fieldsList = explode(',',$this->selectFields);
		$fieldsList = array_map("trim",$fieldsList);
		$fieldsList = $this->qMainTable.".`".implode("`, ".$this->qMainTable.".`",$fieldsList)."`";
		return $fieldsList;
	}

}