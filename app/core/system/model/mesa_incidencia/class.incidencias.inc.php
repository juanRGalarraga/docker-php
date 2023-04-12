<?php

require_once(DIR_model."class.fundation.inc.php");

class cIncidencias extends cModels {

	const tabla_incidencias = TBL_mesa_incidencia;
	public $qMainTable = TBL_mesa_incidencia;
	public $usuario_id = null;
	public function __construct() {
		parent::__construct();
		$this->mainTable = self::tabla_incidencias;
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
	public function GetByPersona($persona_id) {
		$result = false;
		try {
			if (!SecureInt($persona_id)) { throw new Exception(" No se indico el identificador de persona "); }
			$this->sql = "SELECT * FROM ".$this->qMainTable." WHERE `persona_id` = ".$persona_id;
			if($fila = $this->FirstQuery($this->sql)) {
				do {
					$result[] = $fila;
				} while ($fila = $this->Next());
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
			if(!CanUseArray($data)){ throw new Exception(" No hay datos para crear la incidencia "); }
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
                throw new Exception("No se pudo crear la incidencia");
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
			if(!CanUseArray($data)){ throw new Exception(" No hay datos para crear la incidencia "); }
            $campos = $this->GetColumnsNames($this->mainTable);
            foreach ($data as $key => $value) {
                if (isset($campos[$key])) {
                    $key = $this->RealEscape($key);
                    if(CanUsearray($value)){
                        $value = $this->RealEscapeArray($value);
                    }else{
						if(is_string($value) or is_numeric($value)){
							$value = $this->RealEscape($value);
						}
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