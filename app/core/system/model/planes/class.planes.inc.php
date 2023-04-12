<?php
/*
	Clase para administrar los planes de préstamo.
	
	Created: 2021-08-30
	Author: DriverOp
	
*/

require_once(DIR_model."class.fundation.inc.php");
require_once(__DIR__.DS."class.cargos_impuestos.inc.php");

class cPlanes extends cModels {
	
	const tabla_planes = TBL_planes;
	const tabla_cargos_impuestos = TBL_cargos_impuestos;
	const tabla_cargos_planes = TBL_cargos_planes;
	
	private $qMainTable = null;
	private $qCargosImp = null;
	private $qCargosPlanes = null;
	private $cargosImpList = null;
	
	public function __construct() {
		parent::__construct();
		$this->mainTable = self::tabla_planes;
		$this->qMainTable = SQLQuote(self::tabla_planes);
		$this->qCargosImp = SQLQuote(self::tabla_cargos_impuestos);
		$this->qCargosPlanes = SQLQuote(self::tabla_cargos_planes);
		$this->ResetInstance();
		
	}
	
	public function Get(int $id = null):?object {
		try {
			if (empty($id)) { throw new Exception("No se indicó ID."); }
			$this->sql = "SELECT * FROM ".$this->qMainTable." WHERE `id` = ".$id;
			return parent::Get();
		} catch(Exception $e) {
			$this->SetError($e);
		}
		return null;
	}

/**
* Summary. Devuelve el plan por omisión del negocio indicado.
* @param int $negocio_id El id del negocio buscado.
* @return object/null
*/
	public function GetDefault(int $negocio_id = null):?object {
		
		if (empty($negocio_id)) { $negocio_id = 1; }

		$this->sql = "SELECT * FROM ".$this->qMainTable." WHERE `negocio_id` = ".$negocio_id." AND (`esdefault` > 0 AND `esdefault` IS NOT NULL) ORDER BY `sys_fecha_modif` DESC LIMIT 1;";

		return parent::Get();
	}
/**
* Summary Devuelve la lista de todos los cargos con sus detalles que este plan tiene.
* @param string $estados default HAB Filtrar por estado.
* @return array/null
*/
	public function GetFullListCargos($estados = 'HAB'):?array {
		$result = null;
		try {
			if ($this->existe) {
				$sql = "SELECT ".$this->qCargosImp.".* FROM ".$this->qCargosImp.", ".$this->qCargosPlanes." WHERE ".$this->qCargosPlanes.".`plan_id` = ".$this->id."
  AND ".$this->qCargosImp.".`id` = ".$this->qCargosPlanes.".`cargo_id` AND ".$this->qCargosPlanes.".`estado` != 'ELI'";
				if (CanUseArray($estados)) {
					$sql .= "AND ".$this->qCargosPlanes.".`estado` IN ('".implode("','",$estados)."') ";
				} else {
					$sql .= "AND ".$this->qCargosPlanes.".`estado` = '".$estados."' ";
				}
				$this->Query($sql);
				if ($fila = $this->First()) {
					$result = array();
					do {
						$result[] = $fila;
					}while($fila = $this->Next());
				}
			}
		} catch(Exception $e) {
			$this->SetError($e);
		}
		return $result;
	}
/**
* Summary. Establece los cargos o impuestos del plan.
* @param array $cargsimp Array con los IDs de los cargos/impuestos a editar. Si está vacío, elimina las relaciones existentes.
* @param string $tipo A quién de los dos asignarlo
* @return bool
* @note No controla que los IDs del array $cargsimp existan en la tabla correspondiente!
*/
	public function SetCargosImp(array $cargsimp = null):bool {
		$result = false;
		try {
			if (!$this->existe) { throw new Exception("No hay un plan seleccionado, usá ->Get() antes de llamarme."); return $result; }
			cDb::Update(self::tabla_cargos_planes, ['estado'=>'ELI', 'sys_fecha_modif'=>cFechas::Ahora()],"`plan_id` = ".$this->id." AND `estado` = 'HAB'");
			if ($cargsimp and (count($cargsimp) > 0)) {
				foreach($cargsimp as $cargo_id) {
					if (!CheckInt($cargo_id)) { continue; }
					$reg = [
						'estado'=>'HAB',
						'sys_fecha_modif'=>cFechas::Ahora()
					];
					$res = cDb::Query("SELECT `id` FROM ".$this->qCargosPlanes." WHERE `plan_id` = ".$this->id." AND `cargo_id` = ".$cargo_id);
					if ($existe = cDb::First($res)) {
						cDb::Update(self::tabla_cargos_planes,$reg,"`id` = ".$existe->id);
					} else {
						$reg['plan_id'] = $this->id;
						$reg['cargo_id'] = $cargo_id;
						$reg['sys_fecha_alta'] = cFechas::Ahora();
						cDb::Insert(self::tabla_cargos_planes,$reg);
					}
				}
			}
			$result = true;
		} catch(Exception $e) {
			$this->SetError($e);
		}
		return $result;
	} // SetCargosImp

}