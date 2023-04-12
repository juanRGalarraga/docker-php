<?php

require_once(DIR_model."class.fundation.inc.php");

class cTiposMesaIndicencias extends cModels {

	const tabla_tipo_incidencias = TBL_tipo_incidencias;
	public $qMainTable = TBL_tipo_incidencias;
	public $usuario_id = null;
	public function __construct() {
		parent::__construct();
		$this->mainTable = self::tabla_tipo_incidencias;
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
	*   Summary. Obtiene un registro dado su nombre
    *   @param int $nombre El nombre del registro a obtener
    *   @return object $result El registro obtenido
	*/
	public function GetByName($nombre,$site) {
		$result = false;
		try {
			
			if (empty($nombre)) { throw new Exception(" No se indico el nombre "); }
			
			if (empty($site)) { throw new Exception("No se indico el tipo "); }
			$this->sql = "SELECT * FROM ".$this->qMainTable." WHERE `nombre` = '%".$nombre."%' AND `sitio` = '".$site."'";

			if($fila = $this->FirstQuery($this->sql)) {
				$result = $this->Get($fila->id);
			}
		} catch(Exception $e) {
			$this->SetError($e);
			return false;
		}
		return $result;
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
    *   Summary. Agrega un nuevo registro a la tabla
    *   @param array $data Los datos a crear en el registro
    *   @return int $result El ID del registro insertado
    */
	public function Create($data) {
		$result = false;
		try {
			if(!CanUseArray($data)){ throw new Exception(" No hay datos para crear el tipo de incidencia "); }
            $campos = $this->GetColumnsNames($this->mainTable);
            foreach ($data as $key => $value) {
                if (isset($campos[$key])) {
                    $key = $this->RealEscape($key);
                    if(CanUsearray($value)){
                        $value = $this->RealEscapeArray($value);
                    }else{
                        $value = $this->RealEscape($value);
                    }
                    if ($key != 'id') { // Just a precaution.
                        $reg[$key] = $value;
                    }
                }
            }
            $reg['estado'] = 'HAB';
            $reg['sys_fecha_alta'] = cFechas::Ahora();
            $reg['sys_fecha_modif'] = cFechas::Ahora();
            $reg['sys_usuario_id'] = $this->usuario_id;
			if(!$result = $this->NewRecord($reg)){ 
                throw new Exception("No se pudo crear el tipo de incidencia");
            }
		} catch(Exception $e) {
			$this->SetError($e);
			return false;
		}
		return $result;
	}

	/**
    *   Summary. Modifica registro a la tabla
    *   @param array $data Los datos para modificar el registro
    *   @return int $result true en el caso de ser editado
    */
	public function Save($data) {
		$result = false;
		try {
			if(!CanUseArray($data)){ throw new Exception(" No hay datos para crear el tipo de incidencia "); }
            $campos = $this->GetColumnsNames($this->mainTable);
            foreach ($data as $key => $value) {
                if (isset($campos[$key])) {
                    $key = $this->RealEscape($key);
                    if(CanUsearray($value)){
                        $value = $this->RealEscapeArray($value);
                    }else{
                        $value = $this->RealEscape($value);
                    }
                    if ($key != 'id') { // Just a precaution.
                        $this->$key = $value;
                    }
                }
            }
            
			if(!$result = $this->Set()){ 
                throw new Exception("No se pudo actualizar la incidencia");
            }
		} catch(Exception $e) {
			$this->SetError($e);
			return false;
		}
		return $result;
	}
}