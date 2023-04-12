<?php
require_once(DIR_model."class.fundation.inc.php");
class cDepruebas extends cModels {

	const tabla_depruebas = TBL_depruebas;
	public $qMainTable = '';
	
	public function __construct() {
		parent::__construct();
		$this->mainTable = self::tabla_depruebas;
		$this->qMainTable = SQLQuote($this->mainTable);
		$this->ResetInstance();
	}
	
	public function Get(int $id = null):?object {
		if (is_null($id)) { return null; }
		$this->sql = "SELECT * FROM ".$this->qMainTable." WHERE `id` = ".$id;
		return parent::Get();
	}
	
	public function GetList():?object {
		$result = null;
		$this->sql = "SELECT * FROM ".$this->qMainTable." WHERE `estado` = 'HAB';";
		$result = $this->FirstQuery();
		return $result;
	}

/**
* Summary. Guarda las modificaciones al objeto actual en la base de datos.
*/
	public function Set(string $where = null) {
		if (!$this->existe) { return null; }
		$this->data = $this->data; // <-- ¡Trucazo!
		$this->data->relleno_anterior = $this->relleno;
		unset($this->data->a);
		$this->relleno++;
		return parent::Set($where);
	}

/**
* Summary. Agrega un nuevo registro a la tabla
*/
	public function New():?int {
		if (isset($this->relleno)) {
			$this->data->relleno_anterior = SecureInt($this->relleno,0);
		}
		return parent::New();
	}
}