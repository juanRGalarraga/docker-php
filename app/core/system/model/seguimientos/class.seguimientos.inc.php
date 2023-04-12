<?php
/*
	Clase para manejar los seguimientos de gestión de cobranzas.
	Created: 2021-11-19
	Author: DriverOp
	
	Updated: 2021-11-23
	Author: DriverOp
	Desc:
		Agregada tabla gestion_acciones para obtener el nombre de la acción.
*/

require_once(DIR_model."class.fundation.inc.php");


class cSeguimientos extends cModels {
	const tabla_seguimientos = TBL_seguimientos;
	const tabla_acciones = TBL_seguimientos_acciones;
	public $qMainTable = TBL_seguimientos;
	public $qAccionesTable = TBL_seguimientos_acciones;

	public function __construct() {
		parent::__construct();
		$this->mainTable = self::tabla_seguimientos;
		$this->qMainTable = SQLQuote($this->mainTable);
		$this->qAccionesTable = SQLQuote(self::tabla_acciones);
		$this->ResetInstance();
	}
/**
* Summary. Obtener un registro por el id (de seguimiento).
* @param int $id El ID del registro a buscar.
* @return object/null.
*/
	public function Get(int $id = null):?object {
		if (is_null($id)) { return null; }
		$this->sql = "SELECT `seguimientos`.*, `acciones`.`nombre` AS `nombre_accion` FROM $this->qMainTable AS `seguimientos` LEFT JOIN $this->qAccionesTable AS `acciones` ON `acciones`.`id` = `seguimientos`.`accion_id` WHERE `seguimientos`.`id` = ".$id;
		return parent::Get();
	}
	
/**
* Summary. Devuelve la lista de claves usadas en el campo data sin repetición
* @return array.
*/
	public function GetDataFields() {
		$result = [];
		$this->sql = "SELECT DISTINCT `claves` FROM $this->qMainTable, JSON_TABLE(JSON_KEYS(`data`),'$[*]' COLUMNS(`claves` JSON PATH '$')) AS `temp`;";
		$this->Query($this->sql);
		if ($salida = $this->GetAllRecords()) {
			foreach($salida as $value) {
				$result[] = trim($value->claves);
			}
		}
		return $result;
	}
}


