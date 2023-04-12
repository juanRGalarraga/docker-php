<?php
/*
	Manejo de brokers
	Created: 2021-11-08
	Author: Gastón Fernandez
*/

require_once(DIR_model."class.fundation.inc.php");
class cBrokers extends cModels {
	public $qMainTable = TBL_brokers;
    public $usuario_id = null;
    public $simulacion = false;
	public function __construct() {
		parent::__construct();
		$this->mainTable = $this->qMainTable;
		$this->qMainTable = SQLQuote($this->mainTable);
	}
	
	public function Get(int $id = null):?object {
		if (is_null($id)) { return null; }
		$this->sql = "SELECT * FROM ".$this->qMainTable." WHERE `id` = ".$id;
		return parent::Get();
	}
	
}