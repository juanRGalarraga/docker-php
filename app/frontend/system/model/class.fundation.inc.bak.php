<?php
/*
		Foundation class.
	
	Modif: 2021-01-13
	Author: DriverOp
	Desc:
		- Agregada propiedad forceParse para forzar a hacer decodificación de los campos tipo JSON leídos de la base de datos en los métodos ->First(), ->Next(), ->Last() y ->Seek().
		- Agregado método parseJsonFields() para rastrear campos tipo JSON y hacer un json_decode().
	
	Modif: 2021-01-14
	Author: DriverOp
	Desc:
		- Ahora los métodos Update e Insert buscan en el array de trabajo valores de tipo object y array y los convierte en string JSON antes de guardarlos en la tabla usando el método privado convertToJSON().

	Modif: 2021-02-18
	Author: DriverOp
	Desc:
		- Agregado método SetErrorEx que mejora el tratamiento de excepciones.
*/
require_once(DIR_includes."class.logging.inc.php");
require_once(DIR_includes."class.fechas.inc.php");
require_once(DIR_model."class.dbutili.2.inc.php");
if (defined('DBNAME') == false) {
	require_once(DIR_config."database.config.inc.php");
}
require_once(DIR_includes . "class.sidekick.inc.php");
if (!isset($db_link)) {
	$db_link = NULL;
}
if (!isset($reuseLink)) {
	$reuseLink = true;
}


class cModels extends cDb {

	public $existe = false;
	public $encontrado = false;
	public $error = false;
	public $msgerr = null;
	public $raw_record = array();
	public $DebugOutput = true;
	public $sql = '';
	public $tabla_principal = null;
	public $usuario = null;
	public $aliasConvertir = null;
	public $decAsCurrency = false;
	private $res = null;
	public $actual_file = __FILE__;
	public $forceParse = false; // forzar a usar ParseRecord en cada lectura de registro en First() y Next()

	public function __construct($dbhost = null, $dbname = null, $dbuser = null, $dbpass = null, $dbport = null) {
		global $db_link, $reuseLink;
		parent::__construct();
		try {
			if (empty($dbport)) { $this->dbport = DBPORT; } else { $this->dbport = $dbport; }
			if (empty($dbpass)) { $this->dbpass = DBPASS; } else { $this->dbpass = $dbpass; }
			if (empty($dbuser)) { $this->dbuser = DBUSER; } else { $this->dbuser = $dbuser; }
			if (empty($dbname)) { $this->dbname = DBNAME; } else { $this->dbname = $dbname; }
			if (empty($dbhost)) { $this->dbhost = DBHOST; } else { $this->dbhost = $dbhost; }
			$this->Connect($this->dbhost, $this->dbname, $this->dbuser, $this->dbpass, $this->dbport);
			if ($this->error) {
				throw new Exception(__LINE__." DBErr: ".$this->errmsg);
			}
			if ($reuseLink) {
				if ($db_link == NULL) {
					$db_link = $this->link;
				} else {
					$this->link = $db_link;
					$this->opened = true;
				}
			}
		} catch(Exception $e) {
			$this->SetError(__METHOD__,$e->GetMessage());
		}
	} // __construct

	public function ParseJson($data) {
		if (is_object($data)) return $data;
		$result = new StdClass();
		if (!empty(trim($data))) {
			try {
				$result = json_decode($data);
				if (json_last_error() != JSON_ERROR_NONE) {
					throw new Exception(__LINE__." ".ShowLastJSONError(json_last_error(), true));
				}
			} catch(Exception $e) {
				$this->SetError(__METHOD__,$e->GetMessage());
				return false;
			}
		}
		return $result;
	} // ParseJson
	
	function SetErrorEx(Exception $e) {
		$this->msgerr = $e->GetMessage();
		$this->error = true;
		$trace = debug_backtrace();
		$caller = @$trace[1];
		$file = @$trace[0]['file'];
		if (strpos($file, DIR_BASE) !== false) {
			$file = substr_replace($file, '', strpos($file, DIR_BASE), strlen(DIR_BASE));
		}
		$line = sprintf('%s:%s %s%s', $file, @$e->GetLine(), ((isset($caller['class']))?$caller['class'].'->':null), @$caller['function']);
		$line .= ' '.$this->msgerr;
		$error_level = LGEV_WARN;
		if (in_array($this->errno,[1054,1064,1146])) {
			$line .= PHP_EOL."SQL: ".$this->lastsql;
			$error_level = LGEV_ERROR;
		}
		if (DEVELOPE and $this->DebugOutput) { EchoLogP(htmlentities($line)); }
		cLogging::Write($line, $error_level);
	}

	function SetError($method, $msg) {
		$this->error = true;
		$this->msgerr = $msg;
		if (in_array($this->errno,[1054,1064,1146])) {
			$msg .= " SQL: ".$this->lastsql;
		}
		$line = basename($this->actual_file)." -> ".$method.". ".$msg;
		if (DEVELOPE and $this->DebugOutput) { EchoLogP(htmlentities($line)); }
		cLogging::Write($line);
	} // SetError
	
	function Query($sql, $contar = false, $tipos = false) {
		if ($this->decAsCurrency) { $tipos = ['type','length','decimals']; }
		$this->res = parent::Query($sql, $contar, ($tipos or $this->forceParse));
		if ($this->error) { throw new Exception(__LINE__." DBErr: ".$this->errmsg); }
		return $this->res;
	}
	
	function RawQuery($sql) {
		$this->res = parent::RawQuery($sql);
		if ($this->error) { throw new Exception(__LINE__." DBErr: ".$this->errmsg); }
		return $this->res;
	}

	function FirstQuery($sql, $contar = false, $tipos = false) {
		if ($this->decAsCurrency) { $tipos = ['type','length','decimals']; }
		$this->res = parent::Query($sql, $contar, $tipos);
		if ($this->error) { throw new Exception(__LINE__." DBErr: ".$this->errmsg); }
		return parent::First($this->res);

	}

/**
* Métodos lectores.
*/
	function First($res = NULL) {
		if ($res == NULL) { $res = $this->res; }
		$result = parent::First($res);
		if ($this->error) { throw new Exception(__LINE__." DBErr: ".$this->errmsg); }
		if ($this->decAsCurrency) { $result = $this->transformDecAsCurrency($result); }
		if ($this->forceParse) { $result = $this->parseJsonFields($result); }
		return $result;
	}

	function Next($res = NULL) {
		if ($res == NULL) { $res = $this->res; }
		$result = parent::Next($res);
		if ($this->error) { throw new Exception(__LINE__." DBErr: ".$this->errmsg); }
		if ($this->decAsCurrency) { $result = $this->transformDecAsCurrency($result); }
		if ($this->forceParse) { $result = $this->parseJsonFields($result); }
		return $result;
	}

	function Last($res = NULL) {
		if ($res == NULL) { $res = $this->res; }
		$result = parent::Last($res);
		if ($this->error) { throw new Exception(__LINE__." DBErr: ".$this->errmsg); }
		if ($this->decAsCurrency) { $result = $this->transformDecAsCurrency($result); }
		if ($this->forceParse) { $result = $this->parseJsonFields($result); }
		return $result;
	}
	
	function Seek($num, $res = NULL) {
		if ($res == NULL) { $res = $this->res; }
		$result = parent::Seek($num, $res);
		if ($this->error) { throw new Exception(__LINE__." DBErr: ".$this->errmsg); }
		if ($this->decAsCurrency) { $result = $this->transformDecAsCurrency($result); }
		if ($this->forceParse) { $result = $this->parseJsonFields($result); }
		return $result;
	}

	function GetNumRows($res = NULL) {
		if ($res == NULL) { $res = $this->res; }
		$result = parent::GetNumRows($res);
		if ($this->error) { throw new Exception(__LINE__." DBErr: ".$this->errmsg); }
		return $result;
	}
	
/* *** */
/**
* Métodos escritores
*/
	function Update($tabla, array $lista, $where = "") {
		$lista = $this->convertToJSON($lista);
		$result = parent::Update($tabla, $lista, $where);
		if ($this->error) { throw new Exception(__LINE__." DBErr: ".$this->errmsg); }
		return $result;
	}
	
	function Insert($tabla, $lista) {
		$lista = $this->convertToJSON($lista);
		$result = parent::Insert($tabla, $lista);
		if ($this->error) { throw new Exception(__LINE__." DBErr: ".$this->errmsg); }
		return $result;
	}
/* *** */	
/**
* Summary. Transforma los campos de tipo 246, tamaño 13 y decimales 4 en moneda. Agrega un nuevo campo 'cur_<nombre del campo>'
* @param array $result Array con el registro leído.
* @return array Los campos del registro leído con los campos correspondientes cambiados.
*/
	private function transformDecAsCurrency($result) {
		if ($result and $this->decAsCurrency and $this->tipoCampos) {
			if (is_array($result) and is_array($this->tipoCampos)) {
				$moneda = $this->aliasConvertir;
				if(!empty(($result["tipo_moneda"]))){
					$moneda = $result["tipo_moneda"];
				}
				foreach($result as $fieldName => $fieldValue) {
					if (array_key_exists($fieldName, $this->tipoCampos)) {
						$workField = $this->tipoCampos[$fieldName];
						if (isset($workField['type']) and ($workField['type'] == 246)) {
							if (isset($workField['length']) and ($workField['length'] >= 13)) {
								if (isset($workField['decimals']) and ($workField['decimals'] == 4)) {
									$result['orig_'.$fieldName] = $fieldValue;
									$result[$fieldName] = cSidekick::ConvertMoneyTo($this->aliasConvertir,$moneda,$fieldValue);
								}
							}
						}
					}
				}
			}
		}
		return $result;
	}

	function GetColumnsNames($table = null) {
		$result = array();
		try {
			$table = substr(trim($table),0,64);
			if(empty($table)){ $table = $this->tabla_principal; }
			if (empty($table)) { throw new Exception(__LINE__." Table no puede ser nulo o vacío."); }
			$sql = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE `table_schema` = '".$this->dbname."' AND `table_name` = '".$this->RealEscape($table)."'";
			$this->Query($sql);
			while($row = self::Next()){
				$result[] = $row['COLUMN_NAME'];                
			}
			return $result;
		}
		catch(Exception $e) {
			trigger_error('DBErr: '.$e->getMessage(), E_USER_ERROR);
		}
		return $result;
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
	
	function Get($id) {
		$result = false;
		$this->encontrado = false;
		$this->existe = false;
		try {
			if ($this->decAsCurrency and empty($this->aliasConvertir)) {
				$this->aliasConvertir = cSideKick::GetParam(null,'tipo_moneda');
			}
			$this->Query($this->sql);
			if ($fila = $this->First()) {
				$result = true;
				$this->raw_record = $fila;
				$this->encontrado = true;
				$this->existe = true;
			}
		} catch(Exception $e) {
			$this->SetError(__METHOD__,$e->GetMessage());
		}
		return $result;
	}
/*
	Determina si un registro de la tabla principal existe.
	$campo es el campo a buscar, $valor el valor en ese campo. $tabla la tabla donde buscar. Por omisión es $tabla_principal.
*/
	public function Existe($campo, $valor, $tabla = null) {
		$result = false;
		try {
			if (empty($tabla)) { $tabla = $this->tabla_principal; }
			else { $tabla = $this->RealEscape($tabla); }
			$campo = $this->RealEscape($campo);
			$valor = $this->RealEscape($valor);
			$sql = "SELECT * FROM ".SQLQuote($tabla)." WHERE ".SQLQuote($campo)." = '".$valor."' LIMIT 1;";
			$this->Query($sql);
			$result = $this->First();
		} catch(Exception $e) {
			$this->SetError(__METHOD__,$e->getMessage());
		}
		return $result;
	}
/**
 * Summary. Resetea las propiedades del objeto, pero solo las que coinciden con los nombres de los campos de la tabla principal!.
 * @param $tabla string default null. Nombre de la tabla con la cual resetar el objeto. Si es null, toma la tabla de la propiedad tabla_principal.
 * @return null
*/
	public function Reset($tabla = null) {
		$this->existe = false;
		$this->encontrado = false;
		try {
			if (empty($tabla)) { //Si el parámetro está vacío..
				if (!empty($this->tabla_principal)) { $tabla = $this->tabla_principal; } // Tomar la tabla principal...
			}
			if (empty($tabla)) { return; } // Si insiste en estar vacío, entonces salir.
			$fields = $this->GetColumnsNames($tabla);
			if (CanUseArray($fields)) {
				foreach ($fields as $field) {
					$this->$field = null;
				}
			}
			$this->raw_record = null;
		} catch(Exception $e) {
			$this->SetError(__METHOD__,$e->GetMessage());
		}
	}
/**
	* Summary. Traspasa los campos del registro leído de la tabla a propiedades del objeto. Además trata de parsear las estructuras JSON más comunes.
*/
	public function ParseRecord() {
		if ($this->raw_record) {
			foreach($this->raw_record as $key => $value) {
				if (!empty($key)) {
					$this->$key = $value;
				}
			}
			if (isset($this->metadata)) {
				$this->metadata = $this->ParseJson($this->metadata);
			}
			if (isset($this->data)) {
				$this->data = $this->ParseJson($this->data);
			}
			if (isset($this->opciones)) {
				$this->opciones = $this->ParseJson($this->opciones);
			}
			if (isset($this->config)) {
				$this->config = $this->ParseJson($this->config);
			}
		}
	} // ParseRecord

/*
	Igual que ParseRecord pero completamente agresivo.
*/
	public function ParseRecordAll() {
		if ($this->raw_record) {
			foreach($this->raw_record as $key => $value) {
				if (!empty($key)) {
					$this->$key = $value;
				}
			}
			$this->ParseFechas();
			$this->ParseTextFields();
		}
	} // ParseRecordAll

/**
	Summary. Esto recorre el array pasado por parámetro en busca de valores que parecen ser JSON y si es así, los decodifica sobre el propio elemento. Devuelve el mismo array modificado... o no.
	@param $fila array default null. Opcional. El registro a parsear.
	@param $force boolean default true. Opcional. Forzar a incorporar el resultado a la instancia del objeto.
*/
	public function ParseTextFields($fila = null, $force = true) {
		try {
			if (is_null($fila)) { $fila = $this->raw_record; }
			if (CanUseArray($fila)) {
				foreach($fila as $key => $value) {
					if (is_string($value)) {
						$value = trim($value);
						if (preg_match('/^\[?\s*{[\b"\[\s]??(.*?)}\s*\]?$/s',$value)) {
							$fila[$key] = json_decode($value);
							if ($force) {
								$this->$key = $fila[$key];
							}
						}
					}
				}
			}
		} catch(Exception $e) {
			$this->SetError(__METHOD__,$e->GetMessage());
		}
		return $fila;
	} // ParseTextFields
/**
* Summary. Trata de transformar los campos que aparentan tener una fecha hora en fecha y hora textual (human readable).
*/
	public function ParseFechas() {
		foreach ($this as $key => $value) {
			if ((substr($key,0,5) == 'fecha') or (substr($key,0,9) == 'sys_fecha')) {
				$newkey = 'txt_'.$key;
				$newkey_short = 'txt_'.$key.'_short';
				if (substr($key,0,9) == 'sys_fecha') {
					$this->$newkey = cFechas::SQLDate2Str($value);
					$this->$newkey_short = cFechas::SQLDate2Str($value, CDATE_SHORT);
				} else {
					if (cFechas::LooksLikeISODateTime($value)) {
						$this->$newkey = cFechas::SQLDate2Str($value);
						$this->$newkey_short = cFechas::SQLDate2Str($value, CDATE_SHORT);
					} else {
						$this->$newkey = cFechas::SQLDate2Str($value, CDATE_IGNORE_TIME);
						$this->$newkey_short = cFechas::SQLDate2Str($value, CDATE_SHORT+CDATE_IGNORE_TIME);
					}
				}
			}
		}
	} // ParseFechas
/**
* Summary. Dado un registro leído, busca los campos que son de tipo JSON (experimental detection!) y los convierte en objeto PHP
* @param array $registro. El registro leído de la tabla.
* @return array $registro. El registro transformado.
* @note: Esto se usa si la propiedad ->forceParse  está en true y previamente se ha extraído la definición de los tipos de campos de la consulta, es decir, la propiedad ->tipoCampos debe ser no nula.
*/
	public function parseJsonFields($registro) {
		if (!empty($registro) and is_array($registro) and $this->tipoCampos) {
			foreach($registro as $fieldName => $fieldValue) {
				if (array_key_exists($fieldName, $this->tipoCampos)) {
					$workField = $this->tipoCampos[$fieldName];
					if (isset($workField['type']) and ($workField['type'] == 245)) { // Si el campo es tipo 'text/blob'
						if (isset($workField['flags']) and ($workField['flags'] == 144)) { // Si el campo está marcado como JSON... esto es experimental.
							$registro[$fieldName] = json_decode($fieldValue);
							if (json_last_error() != JSON_ERROR_NONE) {
								// Si hubo un error de parseo, restaurar el valor original
								$registro[$fieldName] = $fieldValue;
							}
						}
					}
				}
			} // foreach
		}
		return $registro;
	}
/**
* Summary. Dado un array que será usando para insertar o actualizar un registro de la tabla actual, busca los campos que en la tabla son de tipo JSON y codifica a string JSON el valor de esos campos. Este método es el inverso de ->parseJsonFields()
* @param array $registro. El registro a ser guardado.
* @return array $registro. El registro transformado.
* @note: Esto se usa si la propiedad ->forceParse está en true y previamente se ha extraído la definición de los tipos de campos de la consulta, es decir, la propiedad ->tipoCampos debe ser no nula.
*/
	public function encodeJsonFields($registro) {
		if (!empty($registro) and is_array($registro) and $this->tipoCampos) {
			foreach($registro as $fieldName => $fieldValue) {
				// ShowVar($fieldName);
				// ShowVar($this->tipoCampos);
				if (array_key_exists($fieldName, $this->tipoCampos)) {
					$workField = $this->tipoCampos[$fieldName];
					if (isset($workField['type']) and ($workField['type'] == 245)) { // Si el campo es tipo 'text/blob'
						if (isset($workField['flags']) and ($workField['flags'] == 144)) { // Si el campo está marcado como JSON... esto es experimental.
							$registro[$fieldName] = addslashes(json_encode($fieldValue));
							if (json_last_error() != JSON_ERROR_NONE) {
								// Si hubo un error de parseo, restaurar el valor original
								$registro[$fieldName] = $fieldValue;
							}
						}
					}
				}
			} // foreach
		}
		return $registro;
	}

/**
* Summary. Rastrea del array que se pasa como parámetros valores de tipo array y object y los convierte a string JSON. Se usa en los métodos lectores.
* @param array $arr. El array que contiene los nombres de campo y valores.
* @return array $arr. El array procesado.
*/
	private function convertToJSON($arr) {
		if (is_array($arr) and (count($arr) > 0)) {
			foreach($arr as $key => $value) {
				if (is_object($value) or is_array($value)) {
					$value = $this->RealEscape(json_encode($value));
				}
				$arr[$key] = $value;
			}
		}
		return $arr;
	}
} // Class cModels
?>