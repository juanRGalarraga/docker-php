<?php

require_once(DIR_model."class.fundation.inc.php");

class cIncidenciasRespA extends cModels {

	const tabla_ra_incidencias = TBL_RA_incidencias;
	public $qMainTable = TBL_RA_incidencias;
	public $usuario_id = null;
	public function __construct() {
		parent::__construct();
		$this->mainTable = self::tabla_ra_incidencias;
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
		$this->sql = "SELECT * FROM ".$this->qMainTable." WHERE `id` = ".$id." AND `estado` = 'HAB'";
		return parent::Get();
	}

	
	/**
	*   Summary. Obtiene un registro dado su tipo id
    *   @param int $nombre El nombre del registro a obtener
    *   @return object $result El registro obtenido
	*/
	public function GetByTipo($tipo_id) {
		$result = false;
		try {
			if (!SecureInt($tipo_id)) { throw new Exception(" No se indico el identificador "); }
			$this->sql = "SELECT * FROM ".$this->qMainTable." WHERE `tipo_id` = ".$tipo_id." AND `estado` = 'HAB' AND `texto_clave` IS NULL";
			if($fila = $this->FirstQuery($this->sql)) {
				$result = $this->Get($fila->id);
			}
		} catch(Exception $e) {
			$this->SetError($e);
			return false;
		}
		return $result;
	}

	public function GetByTextoClave($texto,$tipo_id,$id = false) {
		$result = false;
		try {
			if (empty($texto)) { throw new Exception(" No se indico el texto clave "); }
			if (!SecureInt($tipo_id)) { throw new Exception(" No se indico tipo que se busca "); }
			$this->sql = "SELECT * FROM ".$this->qMainTable." WHERE `texto_clave` LIKE '".$texto."' AND `tipo_id` = ".$tipo_id." AND `estado` = 'HAB' ";
			if($id && SecureInt($id)){
				$this->sql .= " AND `id` != ".$id;
			}
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