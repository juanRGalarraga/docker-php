<?php
require_once(DIR_model."class.fundation.inc.php");

class [className] extends cModels {

	private [classTable] = [tableName];
	public $qMainTable = '';
	
	public function __construct() {
		parent::__construct();
		$this->mainTable = self::[classTable];
		$this->qMainTable = SQLQuote($this->mainTable);
		$this->ResetInstance();
	}
	
	/**
	*   Summary. Obtiene un registro dado su ID en la base de datos
    *   @param int $id El ID del registro a obtener
    *   @return object $result El registro obtenido
	*/
	public function Get(int $id = null):?object {
		if (is_null($id)) { return null; }
		$this->sql = "SELECT * FROM ".$this->qMainTable." WHERE `id` = ".$id;
		return parent::Get();
	}
	
    /**
	*   Summary. Obtiene un listado de los registros almacenados en la base de datos
    *   @return object $result El registro obtenido
	*/
	public function GetList():?object {
		$result = null;
		$this->sql = "SELECT * FROM ".$this->qMainTable." WHERE `estado` = 'HAB';";
		$result = $this->FirstQuery();
		return $result;
	}

    /**
    *   Summary. Guarda las modificaciones al objeto actual en la base de datos.
    *   @param string $where Condición que se debe dar para aplicar los cambios
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
    *   Summary. Agrega un nuevo registro a la tabla
    *   @param array $data Los datos a crear en el registro
    *   @return int $result El ID del registro insertado
    */
	public function Create($data):?int {
        if(!CanUseArray($data)){
            return 0;
        }

        foreach($data as $key => $value){
            $this->$key = $value;
        }
		return parent::New();
	}
}