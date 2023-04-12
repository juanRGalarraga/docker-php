<?php
require_once(DIR_model."class.fundation.inc.php");
class cNegocios extends cModels {

	const tabla_negocios = TBL_negocios;
	public $qMainTable = '';
	
	public function __construct() {
		parent::__construct();
		$this->mainTable = self::tabla_negocios;
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
* Summary. Devuelve un listado simple apto para <select>
* @return array ['id'=>'nombre']
*/
	public function GetListSimple():?array {
		$result = array();
		$this->sql = "SELECT `id`, `nombre` FROM ".$this->qMainTable." WHERE `estado` = 'HAB' ORDER BY `nombre`;";
		if ($fila = $this->FirstQuery()) {
			do {
				$id = htmlspecialchars(strip_tags($fila->id));
				$nombre = htmlspecialchars(strip_tags($fila->nombre));
				$result[$id] = $nombre;
			} while($fila = $this->Next());
		}
		return $result;
	}

/**
* Summary. Guarda las modificaciones al objeto actual en la base de datos.
*/
	public function Set(string $where = null) {
		if (!$this->existe) { return null; }
		$this->data = $this->data; // <-- ¡Trucazo!
		return parent::Set($where);
	}

/**
* Summary. Agrega un nuevo registro a la tabla
*/
	public function New():?int {
		return parent::New();
	}
}