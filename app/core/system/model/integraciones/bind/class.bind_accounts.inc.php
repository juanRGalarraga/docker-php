<?php
/*
	Manejador de la tabla de cuentas de BIND
	Created: 2020-12-30
	Author: DriverOp
*/

require_once(DIR_model."class.fundation.inc.php");

class cBindAccounts extends cModels {
	
	const tabla_accounts = TBL_bind_accounts;
	public $negocio_id = null;
	public $qMainTable = null;
	

	public function __construct() {
		parent::__construct(null, null, null, null, null);
		$this->mainTable = self::tabla_accounts;
		$this->qMainTable = SQLQuote($this->mainTable);
		$this->ResetInstance();
	}
	
	public function Get(int $id = null):?object {
		try {
			if (SecureInt($id,null) == null) { throw new Exception(__LINE__." ID debe ser un número."); }
			$this->sql = "SELECT * FROM ".$this->qMainTable." WHERE `id` = ".$id;
			$data = parent::Get();
			if($data){ $this->SetData($data); }
			return $data;
		} catch(Exception $e) {
			$this->SetError(__METHOD__,$e->GetMessage());
		}
		return $result;
	}
	
	public function GetDefault($negocio_id = null) {
		$result = null;
		try {
			if (!is_null(SecureInt($negocio_id))) { $this->negocio_id = $negocio_id; }
			$sql = "SELECT * FROM ".$this->qMainTable." WHERE `negocio_id` = ".$this->negocio_id." AND `es_default` > 0;";
			return $this->FirstQuery($sql);
		} catch(Exception $e) {
			$this->SetError(__METHOD__,$e->GetMessage());
		}
		return $result;
	}
}
?>