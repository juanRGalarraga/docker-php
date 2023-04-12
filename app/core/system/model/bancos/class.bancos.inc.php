<?php
/*
	Manejador de la tabla de bancos (config_bancos, no las cuentas de bancos de los negocios).
	Created: 2021-01-04
	Author: DriverOp
*/

require_once(DIR_model."class.fundation.inc.php");

class cBancos extends cModels {
	
	const tabla_bancos = TBL_bancos;
	
	public $negocio_id = null;
	private $qMainTable = null;

	public function __construct() {
		parent::__construct();
		$this->mainTable = self::tabla_bancos;
		$this->qMainTable = SQLQuote($this->mainTable);
		$this->ResetInstance();
	}

	
	/**
	* Summary. Devuelve un banco según su ID que equivale al código de banco según el BCRA.
	*/
	public function Get(int $id = null):?object {
		$result = null;
		try {
			if (SecureInt($id,null) == null) { throw new Exception(__LINE__." ID debe ser un número."); }
			$this->sql = "SELECT * FROM ".$this->qMainTable." WHERE `id` = ".$id;
			return parent::Get();
		} catch(Exception $e) {
			$this->SetError(__METHOD__,$e->GetMessage());
		}
		return $result;
	}
	
	/**
	* Summary. Dado un CBU intenta encontrar su banco correspondiente
	* @param string $cbu El CBU que al que le vamos a buscar su banco
	* @return null|array
	*/
	public function GetByCbu(string $cbu):?object{
		try {
			if(empty($cbu)){ throw new Exception(__LINE__." El CBU no puede estar vacío."); }
			$cbu = $this->RealEscape($cbu);
			$this->sql = "SELECT * FROM ".$this->qMainTable." WHERE `id` = SUBSTRING('".$cbu."', 1, 5) OR `id` = SUBSTRING('".$cbu."', 1, 3) LIMIT 1";
			return parent::Get();
		} catch(Exception $e) {
			$this->SetError(__METHOD__,$e->GetMessage());
		}
		return null;
	}
	
	/**
	* Summary. Devuelve un banco al azar pero solo aquellos cuyo ID es de hasta tres cifras. Es para generar un CBU al azar.
	*
	*/
	public function GetRandom() {
		$result = false;
		try {
			$sql = "SELECT * FROM ".$this->qMainTable." WHERE `estado` = 'HAB' AND `id` <= 999 ORDER BY RAND() LIMIT 1;";
			return $this->FirstQuery($sql);
		} catch(Exception $e) {
			$this->SetError(__METHOD__,$e->GetMessage());
		}
		return $result;
	}
}
?>