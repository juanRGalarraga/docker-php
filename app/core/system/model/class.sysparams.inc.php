<?php
/**
 * Clase para manejar los parámetros del sistema.
 * Created: 2020-10-03
 * Author: DriverOp
 * 
 * Modif.: 2021-06-22
 * @author Juan Galarraga
 * Desc.: Agrego métodos y modifico existentes para funcionamiento del Web Service.
 */

require_once(DIR_includes."core_constants.inc.php");

if (!defined("TBL_parametros")) {
	define("TBL_parametros","config_parametros");
}
if (!defined("TBL_config_parametros_grupos")) {
	define("TBL_config_parametros_grupos","config_parametros_grupos");
}

require_once(DIR_model."class.dbutil.3.0.inc.php");

class cSysParams extends cDb {
	
	public $DebugOutput = true;
	public $estricto = true;
	public $registro = null;
	public $existe = false;
	public $exponer = false;
	const tabla_parametros = TBL_parametros;
	const tabla_parametros_grupos = TBL_config_parametros_grupos;
	private $actual_file = __FILE__;
	public $usuario = null;
	private $ValidTrueValues = VALID_TRUE_VALUES;
	private $ValidFalseValues = VALID_FALSE_VALUES;
	private $ValidTypesValues = VALID_TYPES_VALUES;
	
/**
*	Summary. Constructor de la clase.
*/
	function __construct() {
		parent::__construct(DBHOST, DBNAME, DBUSER, DBPASS, DBPORT);
		$this->Connect();
		$this->qMainTable = SQLQuote(self::tabla_parametros);
		$this->qGroupsTable = SQLQuote(self::tabla_parametros_grupos);
	}

/**
*	Summary. Devuelve el valor para el parámetro solicitado. Si la propiedad estricto es true solamente encuentra el parámetro si está habilitado.
* @param string $parametro el nombre del parámetrpo buscado.
* @param string $default si el parámetro no es encontrado, devuelve este valor.
* @return var $result El valor del parámetro o el valor de $default cuando no se encuentra en la tabla.
* @notes Verificar la propiedad ->existe para saber si el parámetro fue tomado de la tabla o se está devolviendo el valor de $default. Si el registro leído en la tabla tiene tipo STRING y además el valor es un JSON, entonces se regresa un objeto a partir del JSON almacenado en el campo valor.
*/
	public function Get($parametro=null, $default = null) {
		$result = $default;
		$this->registro = new stdClass();
		$this->existe = false;
		$this->exponer = false;
		try {
			if (!empty($parametro)) {
				$sql = "SELECT * FROM ".$this->qMainTable. " WHERE LOWER(`nombre`) = LOWER('".$this->RealEscape(mb_substr($parametro,0,64))."') ";
				$this->Query($sql);
				if ($fila = $this->First()) {
					$this->existe = true;
					$result = $this->ExtractValue($fila);
					$this->exponer = ((int)$fila['exponer'] > 0);
				}
			}
		} catch(Exception $e) {
			$this->SetError($e);
		}
		return $result;
	}

/**
* Obtiene el registro completo de un parámetro mediante el nombre
* @param string $parametro el nombre del parámetrpo buscado.
* @param string $default si el parámetro no es encontrado, devuelve este valor.
* @return var $result El valor del parámetro o el valor de $default cuando no se encuentra en la tabla.
* @notes Verificar la propiedad ->existe para saber si el parámetro fue tomado de la tabla o se está devolviendo el valor de $default. Si el registro leído en la tabla tiene tipo STRING y además el valor es un JSON, entonces se regresa un objeto a partir del JSON almacenado en el campo valor.
*/

	public function GetRegistro($parametro=null, $default = null) {
		$result = $default;
		$this->registro = new stdClass();
		$this->existe = false;
		try {
			if (!empty($parametro)) {
				$sql = "SELECT `parametros`.*, `grupos`.`nombre` AS `nombre_grupo` FROM ".$this->qMainTable. " AS `parametros`, ".$this->qGroupsTable." AS `grupos`
				WHERE LOWER(`parametros`.`nombre`) = LOWER('".$this->RealEscape(mb_substr($parametro,0,64))."') AND	`grupos`.`id` = `parametros`.`grupo_id`;";
				$this->Query($sql);
				if ($fila = $this->First()) {
					$this->existe = true;
					$result = $fila;
				}
			}
		} catch(Exception $e) {
			$this->SetError($e);
		}
		return $result;
	}
	
/**
*	Summary. Devuelve el valor del regsitro cuyo ID es igual a $id.
*   @param int $id el id del registro buscado
*/
	public function GetById($id) {
		$this->existe = false;
		$this->exponer = false;
		$result = false;
		try {
			if (!SecureInt($id, false)) { throw new Exception(__LINE__." ID debe ser un entero."); }
			$sql = "SELECT * FROM ".$this->qMainTable." WHERE `id`=$id";
			// if ($this->estricto) {
			// 	$sql .= "AND `estado` = 'HAB'";
			// }
			$this->Query($sql);
			if ($fila = $this->First()) {
				$result = $this->ExtractValue($fila);
				$this->exponer = ((int)$fila['exponer'] > 0);
				$this->existe = true;
			}
		} catch(Exception $e) {
			$this->SetError($e);
		}
		return $result;
	}

/**
*	Summary. Establecer el valor de un parámetro.
* @param string $nombre el Nombre del parámetros a almacenar
* @param string $valor el valor del parámetros a almacenar
* @param string $tipo default 'STRING' el tipo de parámetro a almacenar, si el tipo no es válido se eleva una excepción.
* @return bool $result, true en caso de éxito, false en caso contrario.
* @notes Si el parámetro a almacenar no existe, entonces se agrega a la tabla, si existe entonces se modifica.
*/
	public function Set($nombre, $valor, $tipo = 'STRING') {
		$result = false;
		$est = $this->estricto;
		$this->estricto = false;
		$reg = array();
		$reg['sys_fecha_modif'] = cFechas::Ahora();
		if (isset($this->usuario) and is_object($this->usuario) and isset($this->usuario->id)) {
			$reg['sys_usuario_id'] = $this->usuario->id;
		}
		try {
			if (!in_array($tipo, $this->ValidTypesValues)) { throw new Exception(__LINE__." Tipo del parámetro no válido ('".$tipo."')."); }
			$nombre = mb_substr(trim($nombre),0,64);
			if (($this->Get($nombre, false) !== false) and $this->existe) {
				$reg['valor'] = $valor;
			} else {
				$reg['sys_fecha_alta'] = cFechas::Ahora();
				$reg['nombre'] = $nombre;
				$reg['tipo'] = $tipo;
				$reg = $this->RealEscapeArray($reg);
				$this->Insert(self::tabla_parametros, $reg);
			}
			$result = true;
		} catch(Exception $e) {
			$this->SetError($e);
		}
		$this->estricto = $est;
		return $result;
	}

/**
*	Summary. Actualizar un registro completo.
* @param int $id El id del registro a modificar
* @param arra $reg El registro a almacenar
*/
	public function UpdateReg($id, $reg) {
		$result = false;
		try {
			if (!SecureInt($id, false)) { throw new Exception(__LINE__." ID debe ser un entero."); }
			$reg['sys_fecha_modif'] = cFechas::Ahora();
			if (empty($reg['sys_usuario_id']) and isset($this->usuario) and is_object($this->usuario) and isset($this->usuario->id)) {
				$reg['sys_usuario_id'] = $this->usuario->id;
			}
			$reg = $this->RealEscapeArray($reg);
			$result = $this->Update(self::tabla_parametros, $reg, "`id` = ".$id);
		} catch(Exception $e) {
			$this->SetError($e);
		}
		return $result;
	}

/**
*	Summary. Insertar un nuevo registro.
* @param array $reg El registro a almacenar
*/
	public function InsertReg($reg) {
		$result = false;
		try {
			if (empty($reg['nombre'])) { throw new Exception(__LINE__." No se puede almacenar un parámetro sin nombre."); }
			if (empty($reg['tipo'])) { throw new Exception(__LINE__." No se puede almacenar un parámetro sin tipo."); }
			if (!in_array($reg['tipo'], $this->ValidTypesValues)) { throw new Exception(__LINE__." Tipo del parámetro no válido ('".$reg['tipo']."')."); }
			$reg['sys_fecha_modif'] = cFechas::Ahora();
			$reg['sys_fecha_alta'] = cFechas::Ahora();
			if (empty($reg['sys_usuario_id']) and isset($this->usuario) and is_object($this->usuario) and isset($this->usuario->id)) {
				$reg['sys_usuario_id'] = $this->usuario->id;
			}
			$reg = $this->RealEscapeArray($reg);
			$this->Insert(self::tabla_parametros, $reg);
			$result = $this->last_id;
		} catch(Exception $e) {
			$this->SetError($e);
		}
		return $result;
	}

	/**
	 * Inserta un nuevo registro en la tabla de grupo de parámetros
	 * @param array $reg - El registro a almacenar
	 */

	public function InsertGrupo($reg) {
		$result = false;
		try {
			$reg['sys_fecha_modif'] = cFechas::Ahora();
			$reg['sys_fecha_alta'] = cFechas::Ahora();
			if (empty($reg['sys_usuario_id']) and isset($this->usuario) and is_object($this->usuario) and isset($this->usuario->id)) {
				$reg['sys_usuario_id'] = $this->usuario->id;
			}
			$reg = $this->RealEscapeArray($reg);
			$this->Insert(self::tabla_parametros_grupos, $reg);
			$result = $this->last_id;
		} catch(Exception $e) {
			$this->SetError($e);
		}
		return $result;
	}
/**
*	Summary. Verifica el valor leído y lo normaliza según el tipo de dato al que corresponde. TODO: El tipo 'MONEDA' debería tener un par de vueltas de tuerca más.
*/
	private function ExtractValue($fila) {
		try {
			$result = $fila['valor'];
			$this->registro = json_decode(json_encode($fila));
			$this->existe = true;
			if ($fila['tipo'] == 'STRING') {
				if (preg_match('/^\[?\s*{[\b"\[\s]??(.*?)}\s*\]?$/s',$fila['valor'])) {
					$aux = json_decode($fila['valor']);
					if (json_last_error() == 0) {
						$result = $aux;
					}
				}
			}
			if ($fila['tipo'] == 'INT') {
				if (SecureBigInt($fila['valor'], false) === false) {
					$result = null;
					throw new Exception(__LINE__." Valor leído no es del tipo esperado: '".$fila['valor']."' no es INT.");
				}
			}
			if ($fila['tipo'] == 'FLOAT') {
				if (SecureBigFloat($fila['valor'], false) === false) {
					$result = null;
					throw new Exception(__LINE__." Valor leído no es del tipo esperado: '".$fila['valor']."' no es FLOAT.");
				}
			}
			if ($fila['tipo'] == 'BOOL') {
				if (in_array(strtolower(trim($fila['valor'])),$this->ValidTrueValues)) {
					$result = true;
				} else {
					if (in_array(strtolower(trim($fila['valor'])),$this->ValidFalseValues)) {
						$result = false;
					}
				}
			}
			if ($fila['tipo'] == 'JSON') {
				$result = json_decode($fila['valor']);
			}
		} catch(Exception $e) {
			$this->SetError($e);
		}
		return $result;
	}

	/**
	 * Captura los mensajes de errores y escribe una entrada en el log.
	 * @param string $method - Nombre del método desde el cual se captura el error
	 * @param string $msg - Mensaje del error.
	 */

	function SetError($e) {
		$trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
		$f = array_pop($trace);
		$line = sprintf('%s:%s %s%s', cLogging::TrimBaseFile($f['file']), $f['line'], ((isset($f['class']))?$f['class'].'->':null), $f['function']).' '.$e->GetMessage().PHP_EOL;
		for ($i=count($trace)-1; $i>=0; $i--) {
			$f = $trace[$i];
			$line .= sprintf("\t%s:%s %s%s", cLogging::TrimBaseFile($f['file']), $f['line'], ((isset($f['class']))?$f['class'].'->':null), $f['function']).PHP_EOL;
		}
		if (DEVELOPE) { EchoLog($line); }
		cLogging::Write(trim($line));
	} // SetError


	/**
	* Summary. Devuelve todos los grupos de parámetros.
	* @return array/bool $result - El registro o false en caso de no encontrar un registro.
	*/
	public function GetGrupos() {
		$result = false;
		try {
			$sql = "SELECT * FROM ".$this->qGroupsTable." ORDER BY `nombre` AND `estado`='HAB'";
			$this->Query($sql);
			if ($fila = $this->First()) {
				do {
					$result[] = $fila;
				} while($fila = $this->Next());
			}
		} catch(Exception $e) {
			$this->SetError($e);
		}
		return $result;
	}


/**
* Summary. Devuelve un grupo mediante el id;
* @return array/bool $result - El registro o false en caso de no encontrar un registro.
*/
	public function GetGrupo($id) {
		$result = false;
		$this->existe = false;
		try {
			if(!SecureInt($id, false)){ throw new Exception("ID debe ser un número");}
			$sql = "SELECT * FROM ".$this->qGroupsTable." WHERE id=$id";
			$this->Query($sql);
			if ($fila = $this->First()) {
				$result = $fila;
				$this->existe = true;
			}
		} catch(Exception $e) {
			$this->SetError($e);
		}
		return $result;
	}

/**
* Summary. Devuelve un grupo por su nombre.
* @param string $nombre El nombre del grupo que se quiere devolver.
* @return object/null
*/
	public function GetGrupoByName(string $nombre):?object {
		$result = null;
		try {
			if(empty($nombre)){ return null; }
			$sql = "SELECT * FROM ".$this->qGroupsTable." WHERE LOWER(`nombre`) LIKE LOWER('".$this->RealEscape($nombre)."')";
			$this->Query($sql);
			if ($fila = $this->First()) {
				$result = json_decode(json_encode($fila));
				$result->existe = true;
				$result->exponer = ((int)$result->exponer > 0);
			}
		} catch(Exception $e) {
			$this->SetError($e);
		}
		return $result;
	}
/**
* Summary. Devolver la lista de parámetros de un grupo.
* @param int $grupo_id el ID del grupo buscado.
* @return array of objects
*/
	public function GetAllByGrupo(int $grupo_id):array {
		$result = [];
		try {
			if(!CheckInt($grupo_id)){ throw new Exception('ID de grupo debe ser un número.'); }
			$sql = "SELECT * FROM ".$this->qMainTable." WHERE `grupo_id` = ".$grupo_id." AND `estado` = 'HAB'";
			$this->Query($sql);
			if ($fila = $this->First()) {
				do {
					$fila = json_decode(json_encode($fila));
					$fila->existe = true;
					$fila->exponer = ((int)$fila->exponer > 0);
					$result[] = $fila;
				} while($fila = $this->Next());
			}
		} catch(Exception $e) {
			$this->SetError($e);
		}
		return $result;
	}
}

$sysParams = new cSysParams();
if (isset($objeto_usuario) && is_object($objeto_usuario) && isset($objeto_usuario->id)) {
	$sysParams->usuario = $objeto_usuario;
}
