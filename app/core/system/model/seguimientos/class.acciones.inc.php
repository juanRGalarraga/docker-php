<?php
/*
	Clase para administrar las acciones de seguimientos.
	Created: 2021-11-19
	Author: DriverOp
*/

require_once(DIR_model."class.fundation.inc.php");


class cAccionesSeguimientos extends cModels {
	const tabla_seguimientos_acciones = TBL_seguimientos_acciones;
	public $qMainTable = TBL_seguimientos_acciones;

	public function __construct() {
		parent::__construct();
		$this->mainTable = self::tabla_seguimientos_acciones;
		$this->qMainTable = SQLQuote($this->mainTable);
		$this->ResetInstance();
	}
	
	public function Get(int $id = null):?object {
		if (is_null($id)) { return null; }
		$this->sql = "SELECT * FROM ".$this->qMainTable." WHERE `id` = ".$id;
		return parent::Get();
	}
/**
* Summary. Devuelve la lista ordenada de acciones que están habilitadas.
* return array of object.
*/
	public function GetList() {
		$this->sql = "SELECT * FROM ".$this->qMainTable." WHERE `estado` = 'HAB' ORDER BY `nombre`";
		$this->Query($this->sql);
		return $this->GetAllRecords();
	}

}





