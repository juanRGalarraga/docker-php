<?php
/*
		Clase fundacional de los modelos de entidades de la plataforma
		Created: 2021-08-15
		Author: Rebrit SRL.
		
	La intensión de esta clase es que sirva como clase fundacional para todas las clases que abstraen las entidades principales de la plataforma con acceso a la base de datos.

*/


require_once(DIR_includes."common.inc.php"); // Donde está declarado SecureInt().
require_once(DIR_includes."class.logging.inc.php");
require_once(DIR_includes."class.fechas.inc.php");
require_once(DIR_model."class.dbutil.3.0.inc.php");


class cModels extends cDb {
	
	public $mainTable = null;
	public $sql = null;
	public $raw_record = null;
	public $existe = false;
	public $DebugOutput = false;
	public $formatCurrency = true; // Establece que los campos tipo currency (decimal 13,4) tengan campo paralelo convertido a moneda (cur_<nombre_de_campo>).
	public $aliasConvertir = 'ARS';
	public $fieldsName = []; // La lista de nombres de campo de la tabla principal.
	public $usuario = null; // El usuario (del backend) que realiza la acción.
	protected $actualRecord = null;


	private $res = null; // Puntero al recurso local.

	
	public function __construct($dbhost = null, $dbname = null, $dbuser = null, $dbpass = null, $dbport = null) {
		parent::__construct();
		$this->SetRowAs('object');
		$this->throwableErrors = true;
		if (defined("DBHOST")) { $this->dbhost = DBHOST; }
		$this->dbhost = $dbhost??$this->dbhost;
		if (defined("DBNAME")) { $this->dbname = DBNAME; }
		$this->dbname = $dbname??$this->dbname;
		if (defined("DBUSER")) { $this->dbuser = DBUSER; }
		$this->dbuser = $dbuser??$this->dbuser;
		if (defined("DBPASS")) { $this->dbpass = DBPASS; }
		$this->dbpass = $dbpass??$this->dbpass;
		if (defined("DBPORT")) { $this->dbport = DBPORT; }
		$this->dbport = $dbport??$this->dbport;
		$this->Connect();
		array_push($this->fieldProperties,'flags'); // Además de los tres tipos por omisión, quiero 'flags'.
		return $this->IsConnected();
		$this->actualRecord = new stdClass;
	}
	

	public function ResetInstance() {
		if (empty($this->mainTable)) { return null; }
		$this->existe = false;
		$this->fieldsName = $this->GetColumnsNames();
		$this->actualRecord = new stdClass;
		if (count($this->fieldsName)>0) {
			foreach($this->fieldsName as $f) {
				$name = $f['name'];
				$this->$name = null;
			}
		}
	}
	public function SetError($e) {
		$trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
		$f = array_pop($trace);
		$line = sprintf('%s:%s %s%s', cLogging::TrimBaseFile($f['file']), $f['line'], ((isset($f['class']))?$f['class'].'->':null), $f['function']).' '.$e->GetMessage().PHP_EOL;
		for ($i=count($trace)-1; $i>=0; $i--) {
			$f = $trace[$i];
			$line .= sprintf("\t%s:%s %s%s", cLogging::TrimBaseFile($f['file']), $f['line'], ((isset($f['class']))?$f['class'].'->':null), $f['function']).PHP_EOL;
		}
		if (DEVELOPE) { EchoLog($line); }
		cLogging::Write(trim($line));
	}
	
	public function Query($sql, $contar = false, $getFieldTypes = false) {
		$this->res = parent::Query($sql, $contar, $getFieldTypes or $this->formatCurrency);
		return $this->res;
	}

	public function FirstQuery($sql = null, $contar = false) {
		$result = null;
		$this->existe = false;
		try {
			if (empty($sql)) { $sql = $this->sql; }
			if (empty($sql)) { throw new DBException("No se indicó consulta."); }
			$this->res = parent::Query($sql, $contar, $this->formatCurrency);
			$this->existe = false;
			if ($result = parent::First($this->res)) {
				$this->existe = true;
				$this->actualRecord = $this->transformFields($result);
			}
		} catch(DBException $d) {
			$this->SetError($d);
		}
		return $result;
	}

	function First($res = NULL) {
		$result = null;
		try {
			if ($res == NULL) { $res = $this->res; }
			$result = parent::First($res);
			if ($this->error) { throw new Exception(__LINE__." DBErr: ".$this->errmsg); }
			$this->actualRecord = $this->transformFields($result);
		} catch(DBException $d) {
			$this->SetError($d);
		}
		return $result;
	}

	function Next($res = NULL) {
		if ($res == NULL) { $res = $this->res; }
		$result = parent::Next($res);
		if ($this->error) { throw new Exception(__LINE__." DBErr: ".$this->errmsg); }
		$this->actualRecord = $this->transformFields($result);
		return $result;
	}

/* *** */
/**
* Métodos escritores
*/
	function Update($tabla, array $lista, $where = "") {
		$result = null;
		try {
			$lista = $this->RealEscapeArray($this->FilterFields($lista,$tabla));
			$result = parent::Update($tabla, $lista, $where);
			if ($this->error) { throw new Exception(__LINE__." DBErr: ".$this->errmsg); }
		} catch(DBException $d) {
			$this->SetError($d);
		}
		return $result;
	}
	
	function Insert($tabla, $lista) {
		$result = null;
		try {
			$lista = $this->RealEscapeArray($this->FilterFields($lista,$tabla));
			$result = parent::Insert($tabla, $lista);
			if ($this->error) { throw new Exception(__LINE__." DBErr: ".$this->errmsg); }
		} catch(DBException $d) {
			$this->SetError($d);
		}
		return $result;
	}
/* *** */
	function Delete($tabla, $where) {
		$result = null;
		try {
			$result = parent::Delete($tabla, $where);
			if ($this->error) { throw new Exception(__LINE__." DBErr: ".$this->errmsg); }
		} catch(DBException $d) {
			$this->SetError($d);
		}
		return $result;
	}

/**
* Summary. Obtener un registro a partir de una consulta.
* @note Este puede que sea el método más importante de la clase.
*/
	public function Get():?object {
		$this->existe = false;
		$result = null;
		try {
			if (empty($this->sql)) { return null; }
			$this->res = $this->Query($this->sql);
			if ($result = $this->First($this->res)) {
				$this->existe = true;
			}
		} catch(Exception $e) {
			$this->SetError($e);
		}
		return $result;
	}

/**
* Summary. Escribir las modificaciones al registro leído con Get.
* @param string $where default null La cláusula WHERE de la sentencia UPDATE.
* @return bool.
*/
	public function Set(string $where = null) {
		$result = false;
		try {
			if (!$this->existe) { throw new DBException("No hay registro activo."); }
			
			$updatedRecord = $this->getChangedValues();
			
			if (property_exists($this->actualRecord,'sys_fecha_modif')) {
				$updatedRecord['sys_fecha_modif'] = cFechas::Ahora();
			}
			if (property_exists($this->actualRecord,'sys_usuario_id') and isset($this->usuario) and is_object($this->usuario) and isset($this->usuario->id)) {
				$updatedRecord['sys_usuario_id'] = $this->usuario->id;
			}
			if (empty($where)) {
				if (isset($this->actualRecord->id) and is_numeric($this->actualRecord->id)) {
					$where = "`id` = ".$this->actualRecord->id;
				}
			}
			$result = $this->Update($this->mainTable, $updatedRecord, $where);
		} catch(Exception $e) {
			$this->SetError($e);
		}
		return $result;
	}
	
/**
* Summary. Escribir un nuevo y único registro a partir de las propiedades del objeto actual.
* @return int El id del nuevo registro.
*/
	public function New():?int {
		$result = null;
		try {
			
			$updatedRecord = $this->getChangedValues();
			
			if (empty($updatedRecord['sys_fecha_alta']) and isset($this->fieldsName['sys_fecha_alta'])) {
				$updatedRecord['sys_fecha_alta'] = cFechas::Ahora();
			}

			if (empty($updatedRecord['sys_usuario_id']) and isset($this->fieldsName['sys_usuario_id'])) {
				if (isset($this->usuario) and is_object($this->usuario) and isset($this->usuario->id)) {
					$updatedRecord['sys_usuario_id'] = $this->usuario->id;
				}
			}
			if ($this->Insert($this->mainTable, $updatedRecord)) {
				$result = $this->last_id;
			}
		} catch(Exception $e) {
			$this->SetError($e);
		}
		return $result;
	}

/**
* Summary. Escribir un nuevo registro a partir de un array que se pasa como parámetros. Filtra los índices que no son nombre de campo.
* @param array $record. El registro a ser escrito.
* @return int El id del nuevo registro o null en caso de error.
*/
	public function NewRecord(array $record = [], $otro = null):?int {
		$result = null;
		try {
			if (empty($this->fieldsName)) {
				$this->ResetInstance();
			}
			if (empty($this->fieldsName)) { // Si aún no se obtienen los nombre de la tabla...
				return $this->Insert($this->mainTable, $record); // Hacer un insert ciego.
			}
			$reg = $this->FilterFields($record);
			if (count($reg)>0) {
				if (isset($this->fieldsName['sys_fecha_alta'])) {
					$reg['sys_fecha_alta'] = cFechas::Ahora();
				}
				if (isset($this->fieldsName['sys_fecha_modif'])) {
					$reg['sys_fecha_modif'] = cFechas::Ahora();
				}
				if (isset($this->fieldsName['sys_usuario_id'])) {
					if (isset($this->usuario)) {
						if (is_object($this->usuario) and isset($this->usuario->id)) {
							$reg['sys_usuario_id'] = $this->usuario->id;
						}
						if (is_numeric($this->usuario)) {
							$reg['sys_usuario_id'] = $this->usuario;
						}
					}
				}
				if ($this->Insert($this->mainTable, $reg)) {
					$result = $this->last_id;
				}
			} else {
				cLogging::Write(__FILE__ ." ".__LINE__ ." Ningún elemento del array es campo de la tabla.");
			}
		} catch(Exception $e) {
			$this->SetError($e);
		}
		return $result;
	}

	public function GetColumnsNames($table = null) {
		$result = array();
		$db_actual = $this->dbname;
		try {
			$table = substr(trim($table),0,64);
			//Cambio realizado para identificar cual es la base de datos actual, en la cual vamos a buscar las columnas
			if(mb_strpos($table,".")){
				$table = explode(".",$table);
				$db_actual = $table[0];
				$table = end($table);
			}
			if(empty($table)){ $table = $this->mainTable; }
			if (empty($table)) { throw new DBException("Table no puede ser nulo o vacío.",2); }
			$sql = "SELECT COLUMN_NAME, COLUMN_TYPE, DATA_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE `table_schema` = '".$db_actual."' AND `table_name` = '".$this->RealEscape($table)."'";
			$this->res = parent::Query($sql);
			while($row = parent::Next($this->res)){
				
				$type = null;
				if(strpos($row->COLUMN_TYPE,"(") !== false){
					$tmp = strstr($row->COLUMN_TYPE,"(");
					$type = mb_substr($tmp,1,strlen($tmp)-2);
				}
				$fieldName = strtolower($row->COLUMN_NAME);
				$result[$fieldName] = [
					'name'=>$row->COLUMN_NAME, 
					'type'=>$type,
					'data'=>$row->DATA_TYPE
				];
			}
			return $result;
		}
		catch(Exception $e) {
			trigger_error('DBErr: '.$e->getMessage(), E_USER_ERROR);
		}
		return $result;
	}

/**
* Summary. Compara los índices del array que se pasa como parámetros contra la lista de campos de la tabla principal y deja pasar solo aquellos que coinciden en nombre.
* @param array $arr El array de entrada.
* @return array
*/
	public function FilterFields($arr,$tabla = null):?array {
		$result = [];
		try {
			if (count($this->fieldsName) == 0 OR $tabla != null) { $this->fieldsName = $this->GetColumnsNames($tabla); }
			if (empty($arr) or !is_array($arr)) { return []; }
			$arr = array_change_key_case($arr);
			foreach($arr as $key => $value) {
				if (!isset($this->fieldsName[$key])) { continue; }
				$result[$key] = $value;
				if (!isset($this->fieldsName[$key]['data'])) { continue; } // 'data' contiene el tipo de campo, y 'type' el ancho o valores admitidos para ese campo.
				$type = $this->fieldsName[$key]['data'];
				if (in_array($type,['varchar','enum'])) {
					$size = $this->fieldsName[$key]['type']; // contiene el tamaño para este tipo de campos.
					if ($type == 'varchar') { $value = mb_substr($value, 0, $size); }
					else {
						if (strpos("'".strtolower(trim($value))."'", $type)) {
							$result[$key] = $value;
						}
					}
					continue;
				}
				if ($type == 'int') { $result[$key] = SecureInt($value, null); continue; }
				if ($type == 'bigint') { $result[$key] = SecureBigInt($value, null); continue; }
				if (in_array($type,['float','decimal','double','real'])) { $result[$key] = SecureBigFloat($value, null); continue; }
				if ($type == 'json') { $result[$key] = (!is_string($value))?json_encode($value):$value; }
			} // foreach
		} catch(Exception $e) {
			$this->SetError($e);
		}
		return $result;
	}
/**
* Summary. Transforma los campos de tipo 246, tamaño 13 y decimales 4 en moneda. Agrega un nuevo campo 'cur_<nombre del campo>', transforma los campos de tipo 245 (text,blob), sub tipo 144 (JSON) en objetos.
* @param object $result Array con el registro leído.
* @param bool $updateInstance Indica si la instancia actual de $this debe ser actualizada también.
* @return object Los campos del registro leído con los campos correspondientes agregados.
*/
	private function transformFields($result, $updateInstance = true) {
		if (!is_object($result) or !is_array($this->fieldTypes)) {
			return $result;
		}
		foreach($result as $fieldName => $fieldValue) {
			if (!isset($this->fieldTypes[$fieldName])) { continue; }
			$workField = $this->fieldTypes[$fieldName];
			if (!isset($workField['type'])) { continue; }
			if ($workField['type'] == 246) { // Si es tipo decimal
				if ($this->formatCurrency) {
					$moneda = $this->aliasConvertir;
					if(!empty(($result->tipo_moneda))){
						$moneda = $result->tipo_moneda;
						//Echolog('Aquí');
						if (isset($workField['length']) and ($workField['length'] >= 13)) {
							if (isset($workField['decimals']) and ($workField['decimals'] == 4)) {
								$curname = 'cur_'.$fieldName;
								$result->$curname = '$'.number_format($fieldValue, 2, ',','.'); // cSidekick::ConvertMoneyTo($this->aliasConvertir,$moneda,$fieldValue);
							}
						}
					}
				} // if
				continue;
			} // if formatCurrency
			if ($workField['type'] == 245) { // Si el campo es tipo 'text/blob'
				if (isset($workField['flags']) and ($workField['flags'] == 144)) { // Si el campo está marcado como JSON... esto es experimental.
					$result->$fieldName = new stdClass;
					if (!is_null($fieldValue) or !empty($fieldValue)) {
						$result->$fieldName = json_decode($fieldValue);
						if (json_last_error() != JSON_ERROR_NONE) {
							// Si hubo un error de parseo, restaurar el valor original
							$result->$fieldName = $fieldValue;
						}
					}
					continue;
				}
				// Intentemos parsear el campo de todas formas, a ver qué sale...
				if (is_string($fieldValue)) {
					$result->$fieldName = new stdClass;
					$result->$fieldName = json_decode($fieldValue);
					if (json_last_error() != JSON_ERROR_NONE) {
						// Si hubo un error de parseo, restaurar el valor original
						$result->$fieldName = $fieldValue;
					}
				} else {
					$result->$fieldName = $fieldValue;
				}
				continue;
			} // if text/blob
			if (in_array($workField['type'],[7,10,11,12])) { // timestamp, date, time, datetime.
				$shortname = $fieldName.'_txtshort';
				$longname = $fieldName.'_txt';
				switch($workField['type']) {
					case 7:
						$result->$shortname = cFechas::SQLTimeStampToStr($fieldValue, CDATE_SHORT+CDATE_IGNORE_TIME);
						$result->$longname = cFechas::SQLTimeStampToStr($fieldValue, CDATE_IGNORE_TIME);
						break;
					case 10: 
						$result->$shortname = cFechas::SQLDate2Str($fieldValue, CDATE_SHORT+CDATE_IGNORE_TIME);
						$result->$longname = cFechas::SQLDate2Str($fieldValue, CDATE_IGNORE_TIME);
						break;
					case 11:
						break;
					case 12:	
						$result->$shortname = cFechas::SQLDate2Str($fieldValue, CDATE_SHORT);
						$result->$longname = cFechas::SQLDate2Str($fieldValue);
						break;
				}
			}
		} // foreach
		if ($updateInstance) {
			$aux = get_object_vars($result);
			if (count($aux)>0) {
				$reflex = new ReflectionClass(get_class());
				$propiedades_default = $reflex->getDefaultProperties();
				foreach($aux as $key => $value) {
					if (!isset($propiedades_default[$key])) $this->$key = $value;
				}
				$reflex = null;
			}
		}
		return $result;
	}

/**
* Summary. Devuelve los campos del registro actual que fueron cambiados respecto de la lectura que se hizo de la tabla.
* @return array.
*/
	private function getChangedValues() {
		$result = array();
		$reflex = new ReflectionClass(get_class());
		$propiedades_default = $reflex->getDefaultProperties(); // Devuelve los nombres de la pripiedades por omisión del objeto actual.
		$aux = get_object_vars($this->actualRecord); // Devuelve los nombres de las propiedades (que es lo mismo que el nombre de los campos) del registro leído.
		if (count($aux)>0) {
			foreach($aux as $key => $value) {
				// Esto básicamente determina que los nombres de los campos del registro leído no sean iguales a propiedades por omisión del objeto actual y que su valor actual sea distinto del valor leído de la tabla.
				if (property_exists($this, $key) and !isset($propiedades_default[$key]) and ($this->$key != $value)) {
					$result[$key] = $this->$key;
				}
			}
		}
		$reflex = null;
		return $result;
	}

/**
* Summary. Rastrea del array que se pasa como parámetros valores de tipo array y object y los convierte a string JSON. Se usa en los métodos escritores.
* @param array $arr. El array que contiene los nombres de campo y valores.
* @return array $arr. El array procesado.
*/
	private function convertToJSON($arr) {
		if (is_array($arr) and (count($arr) > 0)) {
			foreach($arr as $key => $value) {
				if (is_object($value) or is_array($value)) {
					$value = json_encode($value);
				}
				$arr[$key] = $value;
			}
		}
		return $arr;
	}
}