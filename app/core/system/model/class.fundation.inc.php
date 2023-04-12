<?php
/*
		Clase fundacional de los modelos de entidades de la plataforma
		Created: 2021-08-15
		Author: Rebrit SRL.
		
	La intensión de esta clase es que sirva como clase fundacional para todas las clases que abstraen las entidades principales de la plataforma con acceso a la base de datos.

	Updated: 2021-10-31
	Author: DriverOp
		Reparo bug en método FilterFields().
		Cuando el campo a filtrar es de tipo ENUM, hacía una evalualción incorrecta, el orden de los parámetros de la función strpos() estaban al revés, pero además se estaba evaluando sobre la propiedad incorrecta. type en vez de size.
*/


require_once(DIR_includes."common.inc.php"); // Donde está declarado SecureInt().
require_once(DIR_includes."class.logging.inc.php");
require_once(DIR_includes."class.fechas.inc.php");
require_once(DIR_model."class.dbutil.3.0.inc.php");
require_once(DIR_model."divisas".DS."class.divisas.inc.php");


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


	private $res = null; // Puntero al recurso local.
	public $actualRecord = null;
	public $updatedRecord = null;
	public $recordClosed = false;

	
	public function __construct($dbhost = null, $dbname = null, $dbuser = null, $dbpass = null, $dbport = null) {
		$this->actualRecord = new stdClass;
		$this->updatedRecord = new stdClass;
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
	}

	public function __isset($prop) {
		return isset($this->actualRecord->$prop);
	}

	public function __get($prop) {
		if (property_exists($this->updatedRecord, $prop)) {
			return $this->updatedRecord->$prop;
		}
		if (property_exists($this->actualRecord, $prop)) {
			return $this->actualRecord->$prop??null;
		}
		if (property_exists($this, $prop)) {
			return $this->$prop;
		}
		throw new DBException('Propiedad desconocida: '.$prop,12);
	}

	public function __set($prop, $value) {
		if (property_exists($this, $prop)) {
			$this->$prop = $value;
		}
		if (property_exists($this->actualRecord, $prop)) {
			// El registro actual nunca se cambia, es de "solo lectura".
			$this->updatedRecord->$prop = $value;
			return;
		}
		return $this;
	}
	
	public function Test() {
		return property_exists($this->actualRecord, 'data');
	}
	
	public function GetUpdatedRecord() {
		return $this->updatedRecord;
	}

	public function ResetInstance() {
		if (empty($this->mainTable)) { return null; }
		$this->existe = false;
		$this->actualRecord = new stdClass;
		$this->updatedRecord = new stdClass;
		$this->fieldsName = $this->GetColumnsNames();
		if (count($this->fieldsName)>0) {
			foreach($this->fieldsName as $f) {
				$name = $f['name'];
				$this->$name = null;
				$this->actualRecord->$name = null;
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
		if ($this->DebugOutput) { EchoLog(__LINE__);EchoLog($line); }
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
				if (!$this->recordClosed) {
					$this->updatedRecord = new stdClass();
					$this->actualRecord = $this->transformFields($result);
				}
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
			if ($this->error) { throw new Exception(__LINE__ ." DBErr: ".$this->errmsg); }
			if (!$this->recordClosed) {
				$this->updatedRecord = new stdClass();
				$this->actualRecord = $this->transformFields($result);
			}
		} catch(DBException $d) {
			$this->SetError($d);
		}
		return $result;
	}

	function Next($res = NULL) {
		if ($res == NULL) { $res = $this->res; }
		$result = parent::Next($res);
		if ($this->error) { throw new Exception(__LINE__ ." DBErr: ".$this->errmsg); }
		if (!$this->recordClosed) {
			$this->updatedRecord = new stdClass();
			$this->actualRecord = $this->transformFields($result);
		}
		return $result;
	}

/* *** */
/**
* Métodos escritores
*/
	function Update($tabla, array $lista, $where = "") {
		$result = null;
		try {
			$lista = $this->RealEscapeArray($this->FilterFields($lista));
			$result = parent::Update($tabla, $lista, $where);
			if ($this->error) { throw new Exception(__LINE__ ." DBErr: ".$this->errmsg); }
		} catch(DBException $d) {
			$this->SetError($d);
		}
		return $result;
	}
	
	function Insert($tabla, $lista) {
		$result = null;
		try {
			$lista = $this->RealEscapeArray($this->FilterFields($lista));
			$result = parent::Insert($tabla, $lista);
			if ($this->error) { throw new Exception(__LINE__ ." DBErr: ".$this->errmsg); }
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
			if ($this->error) { throw new Exception(__LINE__ ." DBErr: ".$this->errmsg); }
		} catch(DBException $d) {
			$this->SetError($d);
		}
		return $result;
	}

/**
* Summary. Obtener un registro a partir de una consulta.
* @note Este puede que sea el método más importante de la clase.
*/
	public function Get():? object {
		$this->existe = false;
		$result = null;
		try {
			if (empty($this->sql)) { return null; }
			$this->res = $this->Query($this->sql);
			$this->recordClosed = false;
			if ($result = $this->First($this->res)) {
				$this->existe = true;
			}
			$this->recordClosed = true;
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
			if (!isset($this->updatedRecord)) { return true; } // No hay nada qué actualizar.
			if (count((array)$this->updatedRecord) == 0) { return true; } // No hay nada qué actualizar.
			if (property_exists($this->actualRecord,'sys_fecha_modif')) {
				$this->updatedRecord->sys_fecha_modif = cFechas::Ahora();
			}
			if (property_exists($this->actualRecord,'sys_usuario_id')) {
				if (isset($this->usuario)) {
					if (is_object($this->usuario) and isset($this->usuario->id)) {
						$this->updatedRecord->sys_usuario_id = $this->usuario->id;
					}
					if (is_numeric($this->usuario)) {
						$this->updatedRecord->sys_usuario_id = $this->usuario;
					}
				}
			}
			if (empty($where)) {
				if (isset($this->actualRecord->id) and is_numeric($this->actualRecord->id)) {
					$where = "`id` = ".$this->actualRecord->id;
				}
			}

			if($result = $this->Update($this->mainTable, (array)$this->updatedRecord, $where)){
				foreach($this->updatedRecord as $key => $value){
					$this->actualRecord->$key = $value;
				}
			}
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
			if (empty($this->updatedRecord->sys_fecha_alta) and isset($this->fieldsName['sys_fecha_alta'])) {
				$this->updatedRecord->sys_fecha_alta = cFechas::Ahora();
			}
			if (empty($this->updatedRecord->sys_usuario_id) and isset($this->fieldsName['sys_usuario_id'])) {
				if (isset($this->usuario) and is_object($this->usuario) and isset($this->usuario->id)) {
					$this->updatedRecord->sys_usuario_id = $this->usuario->id;
				}
			}
			if ($this->Insert($this->mainTable, (array)$this->updatedRecord)) {
				$result = $this->last_id;
			}
		} catch(Exception $e) {
			$this->SetErrorEx($e);
		}
		return $result;
	}

/**
* Summary. Escribir un nuevo registro a partir de un array que se pasa como parámetros. Filtra los índices que no son nombre de campo.
* @param array $record. El registro a ser escrito.
* @return int El id del nuevo registro o null en caso de error.
*/
	public function NewRecord(array $record = []):?int {
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
					if (isset($this->usuario) and is_object($this->usuario) and isset($this->usuario->id)) {
						$reg['sys_usuario_id'] = $this->usuario->id;
					}
				}
				if ($this->Insert($this->mainTable, $reg)) {
					$result = $this->last_id;
				}
			}
		} catch(Exception $e) {
			$this->SetErrorEx($e);
		}
		return $result;
	}

/**
* Summary. Devuelve todos los registros de una consulta en un array.
*/
	public function GetAllRecords($res = null) {
		$salida = [];
		if ($res == NULL) { $res = $this->res; }
		$this->getFieldsProperties($res);
		if ($fila = parent::First($res)) {
			do {
				$salida[] = $this->transformFields($fila);
			} while($fila = parent::Next($res));
		}
		return $salida;
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

	function GetColumnsNamesSimplify($table = null) {
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
			if(empty($table)){ $table = $this->tabla_principal; }
			if (empty($table)) { throw new Exception(__LINE__." Table no puede ser nulo o vacío."); }
			$sql = "SELECT COLUMN_NAME, COLUMN_TYPE, DATA_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE `table_schema` = '".$db_actual."' AND `table_name` = '".$this->RealEscape($table)."'";
			$this->Query($sql);
			while($row = self::Next()){
				$result[] = $row->COLUMN_NAME;
			}
			return $result;
		}
		catch(Exception $e) {
			trigger_error('DBErr: '.$e->getMessage(), E_USER_ERROR);
		}
		return $result;
	}

/**
* Summary. Determina si la tabla que se pasa como parámetro existe en la base de datos actual.
* @param string $table El nombre de la tabla buscada.
* @return bool.
*/
	public function TableExists(string $table = null):bool {
		$result = false;
		try {
			$table = substr(trim($table),0,64);
			if(empty($table)){ $table = $this->mainTable; }
			if (empty($table)) { throw new DBException("Table no puede ser nulo o vacío.",2); }
			$sql = "SHOW TABLES LIKE '".$this->RealEscape($table)."';";
			$this->res = parent::Query($sql);
			$result = !empty(parent::First($this->res));
		} catch(Exception $e) {
			trigger_error('DBErr: '.$e->getMessage(), E_USER_ERROR);
		}
		return $result;
	}
/**
* Summary. Compara los índices del array que se pasa como parámetros contra la lista de campos de la tabla principal y deja pasar solo aquellos que coinciden en nombre.
* @param array $arr El array de entrada.
* @return array
*/
	public function FilterFields($arr):?array {
		$result = [];
		try {
			if (count($this->fieldsName) == 0) { $this->fieldsName = $this->GetColumnsNames(); }
			if (empty($arr) or !is_array($arr)) { return []; }
			$arr = array_change_key_case($arr);
			foreach($arr as $key => $value) {
				if (!isset($this->fieldsName[$key])) { continue; }
				$result[$key] = $value;
				if (!isset($this->fieldsName[$key]['data'])) { continue; } // 'data' contiene el tipo de campo, y 'type' el ancho o valores admitidos para ese campo.
				$type = $this->fieldsName[$key]['data'];
				if (in_array($type,['varchar','enum'])) {
					$size = $this->fieldsName[$key]['type']; // contiene el tamaño para campos varchar o la lista de valores de un campo tipo enum.
					if ($type == 'varchar') { $value = mb_substr($value, 0, $size); }
					else {
						if (strpos(strtolower($size), "'".strtolower(trim($value))."'") !== false) {
							$result[$key] = $value;
						} else { unset($result[$key]); } // Si el valor asignado a este campo no está entre los valores permitidos, hay que retirar este campo del array resultado previamente agregaso en la línea 415.
					}
					continue;
				}
				if ($type == 'int') { $result[$key] = SecureInt($value, null); continue; }
				if ($type == 'bigint') { $result[$key] = SecureBigInt($value, null); continue; }
				if (in_array($type,['float','decimal','double','real'])) {
					$result[$key] = SecureBigFloat($value, null);
					continue;
				}
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
* @return object Los campos del registro leído con los campos correspondientes agregados.
*/
	private function transformFields($result) {
		//ShowVar($this->fieldTypes);
		$headers = getallheaders();
		if($headers && isset($headers['currency-id'])){
			if ($this->formatCurrency) {
				cLogging::Write(__FILE__." ".__LINE__." Enviado un tipo de moneda por headers tipo enviado ".$headers['currency-id']);
				$this->aliasConvertir = $headers['currency-id'];
			}
		}		
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
						$result->tipo_moneda;
						if (isset($workField['length']) and ($workField['length'] >= 13)) {
							if (isset($workField['decimals']) and ($workField['decimals'] == 4)) {
								$curname = 'cur_'.$fieldName;
								$result->$curname = '$'.number_format($fieldValue, 2, ',','.'); // cSidekick::ConvertMoneyTo($this->aliasConvertir,$moneda,$fieldValue);
								if($moneda !== $result->tipo_moneda){
									$result->money_converted = $this->aliasConvertir;
									$curname2 = 'converted_'.$fieldName;
									$divisas = new cDivisas();
									$result->$curname2 = $divisas->ConvertMoneyTo($moneda,$result->tipo_moneda,$fieldValue);
								}
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

	function BeginTransaction() {
		$this->Query("START TRANSACTION;");
	}

	function Commit() {
		$this->Query("COMMIT;");
	}

	function Rollback() {
		$this->Query("ROLLBACK;");
	}
	
/**
* Summary. Determina si un valor está permitido en el campo de tipo enum.
* @param string $value El valor a evaluar.
* @param string $field El nombre del campo.
* @return bool.
* @note Si $field señala un campo que existe pero no es enum, se devuelve true, si el campo no existe, false.
*/
	public function In(string $value, string $field):bool {
		if (count($this->fieldsName) == 0) { return true; } // Esto es para no truncar el proceso.
		if (!isset($this->fieldsName[$field])) { return false; } // Se apunta a un campo que no existe.
		$tipo = $this->fieldsName[$field]; // Simplificamos
		if ($tipo['data'] != 'enum') { return true; } // El campo no es enum. No pasa nada.
		return (strpos(strtolower($tipo['type']), "'".strtolower(trim($value))."'") !== false);
	}
}