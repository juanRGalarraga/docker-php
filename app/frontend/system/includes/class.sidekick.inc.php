<?php
/*

	- SetError. Controla los erroes. Pasar __METHOD__ y el mensaje siempre.
	- FilterFloat: Convierte la entrada en un tipo flotante PHP, o false en caso de no poder hacerlo.
	- FilterInt: Convierte la entrada en un tipo int PHP, o false en caso de no poder hacerlo.
	- FilterNumber: Verifica si $value está compuesto de solo números.
	- FilterVar: Recorta la entrada a una cantidad arbitraria de caracteres y opcionalmente pasa a minúsculas.
	- BuildEstadoCond: Uso interno, resuelve la lógica para armar la consulta SQL en base al campo `estado`.
*/

// require_once(DIR_model."class.dbutili.2.inc.php"); // Versión anterior.
require_once(DIR_model."class.dbutil.3.0.inc.php");
include_once(DIR_includes . "class.checkinputs.inc.php");
include_once(DIR_includes . "class.logging.inc.php");

class cSidekick
{

	public static $reg_param = null;
	public static $feriados = null;
	public static $año_feriados = null;

	static function SetError($method, $msg)
	{
		$line = substr(__FILE__, strlen(DIR_BASE)) . " -> " . $method . ". " . $msg;
		if (DEVELOPE) {
			EchoLogP(htmlentities($line));
		}
		cLogging::Write($line);
	}
	/*
	Convierte la entrada $value en un tipo flotante PHP, o false en caso de no poder hacerlo.
*/
	static function FilterFloat($value)
	{
		$value = trim($value);
		$value = substr($value, 0, 11);
		$value = trim($value);
		if (empty($value)) {
			return false;
		}
		$p = strrpos($value, ',');
		if (($p !== false) and ($p > 0)) {
			$value = str_replace('.', '', $value);
			$value = str_replace(',', '.', $value);
		} else {
			$p = strrpos($value, '.');
			if (($p !== false) and ($p > 0)) {
				$value = str_replace(',', '', $value);
			}
		}
		$value = trim($value);
		if (empty($value)) {
			return false;
		}
		if (!preg_match('/[0-9\.]+/', $value)) {
			return false;
		}
		if (!is_numeric($value)) {
			return false;
		}
		return (float)$value;
	}
	/*
	Convierte la entrada $value en un tipo int PHP, o false en caso de no poder hacerlo.
*/
	static function FilterInt($value)
	{
		$value = trim($value);
		$value = substr($value, 0, 11);
		$value = trim($value);
		if (empty($value)) {
			return false;
		}
		if (!preg_match('/^[0-9]+$/', $value)) {
			return false;
		}
		return (int)$value;
	}
	/*
	Verifica si $value está compuesto de solo números.
*/
	static function FilterNumber($value)
	{
		$value = trim($value);
		if (empty($value)) {
			return false;
		}
		if (!preg_match('/[0-9]+/', $value)) {
			return false;
		}
		return $value;
	}
	/*
	Recorta la entrada a una cantidad arbitraria de caracteres y opcionalmente pasa a minúsculas.
	$var: la variable.
	$length: a qué largo cortarla.
	$lower: convertir a minúsculas.
*/
	static function FilterVar($var, $length = 11, $lower = false)
	{
		$var = trim($var);
		$var = mb_substr($var, 0, $length);
		$var = trim($var);
		if ($lower) {
			$var = mb_strtolower($var);
		}
		return $var;
	}
	/*
	Resuelve la lógica para armar la consulta SQL en base al campo `estado`.
*/
	static function BuildEstadoCond($db, $estado, $field = 'estado', $tabla = NULL)
	{
		$salida = '';
		if (!empty($estado)) {
			if (!empty($tabla)) {
				$campo = SQLQuote($tabla) . "." . SQLQuote($field);
			} else {
				$campo = SQLQuote($field);
			}
			if (is_array($estado) and (count($estado) > 0)) {
				foreach ($estado as $k => $e) {
					$estado[$k] = $db->RealEscape($e);
				}
				$salida = $campo . " IN ('" . implode("','", $estado) . "')";
			}
			if (is_string($estado)) {
				$salida = "UPPER(" . $campo . ") = UPPER('" . $db->RealEscape($estado) . "')";
			}
		}
		if (!empty($salida)) {
			$salida = "AND (" . $salida . ") ";
		}
		return $salida;
	}
	/**
	 * Summary. Función para mostrar un mensaje genérico en la interfaz principal.
	 *  @param str $msg El mensaje a mostrar
	 * 
	 */
	static function ShowWarning($msg)
	{
		$nombre_plantilla = "default_warning.htm";
		if (ExisteArchivo(DIR_plantillas . $nombre_plantilla)) {
			include(DIR_plantillas . $nombre_plantilla);
		} else {
			$content = '<p>' . $msg . '</p>';
		}
	}

	static function ConvertMoneyTo($alias_a_convertir = null,$alias_inicial = null,$monto_a_convertir = null){
		$result = $monto_a_convertir;
		try {
			if (empty($alias_a_convertir)) {
				$alias_a_convertir = self::GetParam(null,'tipo_moneda');
			}
			if(empty($alias_a_convertir) and defined("DEFAULT_DIVISA")){
				$alias_a_convertir = DEFAULT_DIVISA;
			}
			
			if(empty($alias_a_convertir)) {
				$alias_a_convertir = 'ARS';
			}
			if(isset(COTIZACIONES[$alias_a_convertir])){
				$moneda_a_convertir = COTIZACIONES[$alias_a_convertir];
			}else{
				throw new Exception(__LINE__ . " Tipo moneda ".$alias_a_convertir." no existe. ");
			}
			if ($alias_a_convertir != $alias_inicial) {
				if(!$alias_inicial){ $alias_inicial = $alias_a_convertir; }
				if(isset(COTIZACIONES[$alias_inicial])){
					$moneda_inicial = COTIZACIONES[$alias_inicial];
				}else{
					throw new Exception(__LINE__ . " valor moneda ".$alias_inicial." no existe. ");
				}

				$precioUSD = 0;
				$valor_oficial = 0;
				if($moneda_inicial && $moneda_inicial["conversion"] < 0){
					throw new Exception(__LINE__ . " valor moneda Argentina no puede estar vacia. ");
				}
				if($moneda_a_convertir && $moneda_a_convertir["conversion"] < 0){
					throw new Exception(__LINE__ . " Tipo moneda a convertir no puede estar vacio. ");
				}
				if($monto_a_convertir && $monto_a_convertir < 0){
					throw new Exception(__LINE__ . " Monto no puede ser vacio. ");
				}
				
				$precioUSD = $monto_a_convertir/$moneda_inicial["conversion"];
				if($precioUSD && $precioUSD > 0){
					$valor_oficial = $precioUSD*$moneda_a_convertir["conversion"];
					$valor_oficial = round($valor_oficial,2);
					$result = $valor_oficial;
				}
			}
		} catch(Exception $e) {
			self::SetError(__METHOD__, $e->getMessage());
		}
		$tipo_nombre_moneda = $moneda_a_convertir["simbolo"];
		if($moneda_a_convertir["simbolo"] == "$"){
			$tipo_nombre_moneda = $moneda_a_convertir["simbolo"]." ".$moneda_a_convertir["alias"];
		}
		if(empty($result)){ $result = 0; }
		$result = number_format($result, 2, ',', '');
		return $tipo_nombre_moneda." ".$result;
	}
	
	static function GetDB($db) {
		try {
			$db = new cDb();
			$db->Connect(DBHOST, DBNAME, DBUSER, DBPASS, DBPORT);
		} catch (Exception $e) {
			self::SetError(__METHOD__, $e->getMessage());
		}
		return $db;
	}


/**
	 * Summary. Se asegura que la rama de directorios pasada como parámetros exista en el servidor. Pero solo si parte desde la raiz del desarrollo.
	 * @param array/string $path. El path que se pretende controlar, puede ser un string representando un camino o un array donde cada elemento es un nombre de directorio.
	 * @param bool $create. Si está en true, entonces procede a crear los directorios que hagan falta para que la rama exista.
	 */
	static function EnsureDirExists($path, $create = true)
	{
		$result = true; // Seamos optimistas...
		$dir_lista = array();
		if (empty($path)) {
			return true;
		} // Si está vacío, no hacemos nada.
		if (CanUseArray($path)) {
			$dir_string = null;
			foreach ($path as $dir) {
				if (!empty($dir)) {
					$dir = str_replace(array('\\', '/'), DS, $dir); // Se cambia el separador de dirs por el correcto en el SO actual.
					$dir_string .= $dir . DS;
				}
			}
			$dir_lista = explode(DS, $dir_string);
		} else {
			$path = str_replace(array('\\', '/'), DS, $path); // Se cambia el separador de dirs por el correcto en el SO actual.
			$path = str_replace(DIR_BASE, null, $path); // Remover del path, el path al desarrollo actual, por las dudas...
			$dir_lista = explode(DS, $path);
		}
		$dir_lista = array_filter($dir_lista); // retira elementos vacíos del array.
		$dir_lista = array_map("RemoveDots", $dir_lista);
		$dir_lista = array_filter($dir_lista); // retira elementos vacíos del array.
		// Ahora cualquier cosa que hagamos es a partir de DIR_BASE.
		if (CanUseArray($dir_lista)) {
			$path = EnsureTrailingSlash(DIR_BASE);
			foreach ($dir_lista as $dir) {
				if (!is_dir($path . $dir)) {
					if ($create) {
						if (mkdir($path . $dir)) {
							chmod($path . $dir, 0777);
						} else {
							$result = false;
							break;
						}
					} else {
						$result = false;
						break;
					}
				}
				$path .= $dir . DS;
			} // foreach;
		} // if
		return $result;
	} // function EnsureDirExists

/**
* Summary. Esto elimina todos los archivos de un directorio. Alternativamente filtrado por extensión de archivo.
* @param string $dir default empty. Path al directorio de se debe vaciar.
* @param array $ext default null. Un array con las extensiones de los archivos a eliminar.
*/
	static function EmptyDir($dir = null, $ext = []) {
		$dir = EnsureTrailingSlash($dir);
		if (is_array($ext)) {
			if (count($ext > 0)) {
				foreach($ext as $value) {
					$files = glob($dir.'*.'.$value);
					foreach($files as $file){
						if(is_file($file))
							unlink($file);
					} // foreach
				} // foreach
			} else {
				$files = glob($dir.'*.*');
				foreach($files as $file){
					if(is_file($file))
						unlink($file);
				} // forteach
			} // else
		}
	}

} // class cSideKick



function RemoveDots($str)
{
	return str_replace([".." . DS, DS . "..", "..", "." . DS, DS . ".", "..", "."], NULL, $str);
}


/*
		$result = null;
		try {
			$sql = "SELECT * FROM ".SQLQuote(TBL_)." WHERE 1=1 ";
			$db->Query($sql);
			if ($db->error) { throw new Exception(__LINE__." DBErr: ".$db->errmsg); }
			if ($fila = $db->First()) {
				$result = array();
				do {
					
				} while($fila = $db->Next());
			}
		} catch(Exception $e) {
			self::SetError(__METHOD__,$e->getMessage());
		}
		return $result;
*/
