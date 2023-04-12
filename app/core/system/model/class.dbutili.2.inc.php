<?php
/*
	Clase para manejo de base de datos MySQL.
	Version: 2.0
	Created: long time ago.
	Author: DriverOp. http://driverop.com.ar/
	Licence: LGPL 3. http://www.gnu.org/licenses/lgpl.html
	Last Modif: Added option 'contar' on method Query().
	Last Modif: Agregue un parámetro a ShowLastError y la función controla el valor de DEVELOPE.
	Last Modif: Agregue el método GetArray.
	Last Modif: Arreglado bug en RealEscape cuando $str = NULL devolvía string vacío en vez de NULL
	Modified: 2019-03-09
	Desc: Arreglado bug en método Last(). mysql_num_rows debía ser mysqli_numrows.
	Modified: 2019-09-18
	Desc: Agregada función SetONLY_FULL_GROUP_BY_off() para desactivar la directiva ONLY_FULL_GROUP_BY en los servidores MySQL 5.7
	Modified: 2020-02-18
	Reescritura de la conexión a la base de datos para contemplar que se pueda conectar por SSL a MySQL.
	Modified: 2020-05-30
	Agregado $trackback y $trackback_sql para hacer debug profundo de las consultas a la base de datos.
	Modified: 2020-09-12
	Desc: Completada la documentación interna.
	Modified: 2020-09-30
	Desc: Agregado mecanismo para devolver los tipos de campos de una consulta.
	Modified: 2021-01-13
	Desc: En RealEscape() ignorar elementos que son object o array.
	
*/

if (!isset($db_link)) {
	$db_link = NULL;
}
if (!isset($reuseLink)) {
	$reuseLink = true;
}

class cDb {
	var $dbhost = 'localhost';
	var $dbname = 'dbname';
	var $dbuser = 'root';
	var $dbpass = '';
	var $dbport = 3306;
	var $link = NULL;
	var $errmsg = "";
	var $error = false;
	var $errno = 0;
	var $numrows = 0;
	var $affectedrows = 0;
	var $result = NULL;
	var $last_id = 0;
	var $lastsql = "";
	var $persistent = false;
	var $cantidad = 0;
	var $tipoCampos = array();
	public $trackback = DEVELOPE;
	public $trackback_sql = array();
	private $opened = false;
	private $ssl = false;
	private $path_to_certs = null;
	private $key_pem = null;
	private $cert_pem = null;
	private $ca_pem = null;

/**
* Summary. Constructor de la clase. Llama a ->Connect al final.
* @param str $dbhost La dirección del host de MySQL.
* @param str $dbname El nombre de la base de datos a usar.
* @param str $dbuser El nombre de usuario en MySQL.
* @param str $dbpass La contaseña del usuario de MySQL.
* @param int $dbport default 3306. El puesto de conexión a MySQL
*/
	function __construct($dbhost = null, $dbname = null, $dbuser = null, $dbpass = null, $dbport = 3306) {
		$this->dbhost = $dbhost;
		$this->dbname = $dbname;
		$this->dbuser = $dbuser;
		$this->dbpass = $dbpass;
		$this->dbport = $dbport;
		if (!empty($dbhost) and !empty($dbname) and !empty($dbuser) and !empty($dbpass)) {
			$this->Connect($dbhost, $dbname, $dbuser, $dbpass);
		}
		$this->traceback = DEVELOPE;
	}
	
/**
* Summary. Establecer las opciones de conexión sobre SSL. Establece la propiedad ->ssl en true.
* @param str $path Ruta hacia los archivos .pem
* @param str $key, $cert, $ca Nombre de los archivos .pem
*/
	function SetSSL($path, $key, $cert, $ca) {
		$this->path_to_certs = $path;
		$this->key_pem = $key;
		$this->cert_pem = $cert;
		$this->ca_pem = $ca;
		if ((!empty($this->key_pem)) and !$this->ExisteArchivo($this->path_to_certs.$this->key_pem)) { throw New Exception($this->path_to_certs.$this->key_pem.' no encontrado.'); }
		if ((!empty($this->cert_pem)) and !$this->ExisteArchivo($this->path_to_certs.$this->cert_pem)) { throw New Exception($this->path_to_certs.$this->cert_pem.' no encontrado.'); }
		if ((!empty($this->ca_pem)) and !$this->ExisteArchivo($this->path_to_certs.$this->ca_pem)) { throw New Exception($this->path_to_certs.$this->ca_pem.' no encontrado.'); }
		$this->ssl = true;
	}
/**
* Summary. Verifica si en la última operación hubo un error y establece las propiedades para así informarlo.
*/
	function CheckError() {
		$this->errno = mysqli_errno($this->link);
		$this->error = $this->errno != 0;
		$this->errmsg = $this->errno.": ".mysqli_error($this->link);
		return $this->error;
	}
/**
* Summary. Abre la conexión con el servidor. Si existe una constante DBSSL y ésta vale true, establece las opciones de conexión segura SSL llamando a ->SetSSL.
* @param str $dbhost La dirección del host de MySQL.
* @param str $dbname El nombre de la base de datos a usar.
* @param str $dbuser El nombre de usuario en MySQL.
* @param str $dbpass La contaseña del usuario de MySQL.
* @param int $dbport default 3306. El puesto de conexión a MySQL.
* @return bool ->error True en caso de conexión exitosa, false en caso contrario.
*/

	function Connect($dbhost, $dbname, $dbuser, $dbpass, $dbport = 3306) {
		global $db_link, $reuseLink;
		$this->dbhost = $dbhost;
		$this->dbname = $dbname;
		$this->dbuser = $dbuser;
		$this->dbpass = $dbpass;
		$this->dbport = $dbport;
		$this->error = false;
		if (($this->opened != true) or ($this->persistent != true)){
			if (defined("DBSSL") and (DBSSL == true)) {
				$this->SetSSL(
					(defined("DBSSL_PATH")?DBSSL_PATH:null),
					(defined("DBSSL_KEY_PEM")?DBSSL_KEY_PEM:null),
					(defined("DBSSL_CERT_PEM")?DBSSL_CERT_PEM:null),
					(defined("DBSSL_CA_PEM")?DBSSL_CA_PEM:null)
				);
			}
			if ($db_link == NULL) {
				$this->GetConnect();
			} else {
				if ($reuseLink) {
					$this->link = $db_link;
				} else {
					$this->GetConnect();
				}
			}
		}
		if (!$this->error) {
			$this->SetUTF8();
			$this->SetONLY_FULL_GROUP_BY_off();
		}
		return $this->error;
	}
	
/**
* Summary. Obtener la conexión a la base de datos. Este es el método que efectivamente abre la conexión con MySQL. Establece la propiedad ->link. A asigna a la variable global $db_link si la variable global $reuseLink es true.
*/
	private function GetConnect() {
		global $reuseLink;
		$this->link = mysqli_init();
		if ($this->ssl) {
			mysqli_ssl_set($this->link,
				$this->path_to_certs.$this->key_pem,
				$this->path_to_certs.$this->cert_pem,
				$this->path_to_certs.$this->ca_pem,
				NULL,
				NULL
			);
			$result = @mysqli_real_connect($this->link, $this->dbhost, $this->dbuser, $this->dbpass, $this->dbname, $this->dbport, null, MYSQLI_CLIENT_SSL_DONT_VERIFY_SERVER_CERT);
		} else {
			$result = @mysqli_real_connect($this->link, $this->dbhost, $this->dbuser, $this->dbpass, $this->dbname, $this->dbport);
		}
		if (!$result or (mysqli_connect_errno() > 0)) {
			$this->error = true;
			$this->errno = mysqli_connect_errno();
			$this->errmsg = $this->errno.": ".mysqli_connect_error();
		} else {
			$this->opened = true;
			$db_link = ($reuseLink)?$this->link:null;
		}
	}

/**
* Summary. Cierra la conexión con la base de datos.
*/
	function Disconnect() {
		global $db_link;
		if (!$this->persistent) {
			if ($this->link !== NULL) {
				mysqli_close($this->link);
				$this->link = NULL;
				$this->opened = false;
				$db_link = $this->link;
			}
		}
	}

/**
* Summary. Verifica si hay una conexión aberta.
* @return bool.
*/
	function IsConnected() {
		if (!is_bool($this->link) and !($this->link == NULL)) {
			return true;
		} else {
			return false;
		}
	}

/**
* Summary. Devuelve el puntero a la conexión.
* @return link.
*/
	function GetLink() {
		return $this->link;
	}
	
/**
* Summary. Devuelve una descripción de la conexión.
* @return str.
*/
	function GetInfo() {
		return mysqli_get_host_info($this->link);
	}

/**
* Summary. Permite mandar a ejecutar cualquier instrucción SQL. Devuelve un puntero al conjunto de resultado si los hay. Se hace cargo de actualizar el estado de error y la cantidad de registros devueltos (->numrows) o afectados (->affectedrows) por la instrucción.
* @param str $sql La sentencia SQL a ejecutar.
* @param bool $contar default false Cuando vale true, establece la propiedad ->cantidad que es la cantidad de registros afectados sin tener en cuenta la cláusula LIMIT (mientras que ->numrows devuelve siempre <= LIMIT).
* @param bool/array $tipos Cuando true devuelve las propiedades de cada campo de la consulta, cuando array indica los nombres de las propiedades del campo que se quieren obtener
* @return pointer/bool Puntero a los resultados, o true o false.
*/
	function Query($sql, $contar = false, $tipos = false) {
		if (($contar) AND (strtoupper(substr(trim($sql), 0,6)) == 'SELECT')) {
			$sql = 'SELECT SQL_CALC_FOUND_ROWS'.substr($sql, 6);
			$sql_found = 'SELECT FOUND_ROWS() AS `cantidad`';
		}
		$this->numrows = 0;
		$this->lastsql = $sql;
		if ($this->trackback) { $this->trackback_sql[] = __METHOD__.": ".$sql; }
		$this->result = mysqli_query($this->link,$sql);
		
		if (!$this->CheckError()) {
			if (!is_bool($this->result)) {
				$this->numrows = mysqli_num_rows($this->result);
			} else {
				$this->affectedrows = mysqli_affected_rows($this->link);
			}
			$this->tipoCampos = array();
			if (!is_bool($this->result) and ($tipos)) { // tiene que haber un puntero a resultado para poder analizar los campos
				$this->tipoCampos = $this->getFieldsProperties($this->result, $tipos);
			}
			// Si quiero contar la cantidad de registros sin el limit
			if (isset($sql_found)) {
				if ($this->trackback) { $this->trackback_sql[] = __METHOD__.": ".$sql_found; }
				$aux = mysqli_query($this->link, $sql_found);
				$aux2 = mysqli_fetch_assoc($aux);
				$this->cantidad = $aux2['cantidad'];
			}
		} // si no hubo error
		return $this->result;
	}

/**
* Summary. Ejecuta cualquier instrucción SQL "a ciegas" y devolver el resultado. No afecta a las propiedades el objeto.
* @return ->result Puntero a resultado de la ejecución o false en caso de error.
*/
	function RawQuery($sql) {
		if ($this->trackback) { $this->trackback_sql[] = __METHOD__.": ".$sql; }
		$this->result = mysqli_query($this->link,$sql);
		return $this->result;
	}

/**
* Summary. Simplifica la sentencia UPDATE de SQL. Establece la propiedad ->affectedrows.
* @param string $tabla El nombre de la tabla a la cual hacerle UPDATE.
* @param array $lista Se interpreta que el índice del array es el nombre del campo y el valor del elemento es el valor a asignar a ese campo.
* @param string $where Cualquier condición que se agregue a la cláusula WHERE de UPDATE.
* @return bool true en caso de éxito, false en caso de error.
* @tutorial Este método genera el número de error -1 si el segundo parámetro no es un array.
*/

	function Update($tabla, array $lista, $where = "") {
		$this->affectedrows = -1;
		if (!is_array($lista)) {
			$this->error = true;
			$this->errno = -1;
			$this->errmsg = "Segundo parámetro no es array ('campo'=>'valor')";
			return false; }
		else  {
			$sql = "UPDATE `".$tabla."` SET";
			foreach ($lista as $key => $value) {
				if ($value === null) {
					$sql .= " `".$key."` = NULL,";
				} else {
					$sql .= " `".$key."` = '".$value."',";
				}
			}
			$sql = substr($sql, 0, -1); // Quita la última coma.
			$where = trim($where);
			if (!empty($where)) {  $sql .= " WHERE ".$where; }
			$sql .= ";";
			$this->lastsql = $sql;
			if ($this->trackback) { $this->trackback_sql[] = __METHOD__.": ".$sql; }
			$this->result = mysqli_query($this->link,$sql);
			if (!$this->CheckError()) {
				$this->affectedrows = mysqli_affected_rows($this->link);
				return true;
			} else {
				return false;
			}
		}
	}

/**
* Summary. Simplifica la sentencia INSERT de SQL. 
* @param str $tabla El nombre de la tabla a la cual hacerle INSERT.
* @param array $lista Array donde el índice es el nombre del campo y el valor el valor a establecer para ese campo.
* @return bool true si no hubo errores al ejecutar la instrucción, o false en caso contrario.
* @tutorial Consultar la propiedad errmsg para saber cuál fue el error, y affectedrows para saber cuántos registros se insertaron. Consultar la propiedad ->last_id para saber el valor generado para un campo AUTO_INCREMENT, usualmente la clave primaria.
*/
	function Insert($tabla, $lista) {
		$this->affectedrows = -1;
		if (!is_array($lista)) {
			$this->error = true;
			$this->errno = -1;
			$this->errmsg = "Segundo parámetro no es array ('campo'=>'valor')";
			return false;
		} else {
			$sql = "INSERT INTO `".$tabla."` (`".implode("`, `",array_keys($lista))."`) VALUES (";
			reset($lista);
			foreach ($lista as $key => $value) {
				if ($value === null) {
					$sql .= "NULL,";
				} else {
					$sql .= "'".$value."',";
				}
			}
			$sql = substr($sql, 0, -1); // Quita la última coma.
			$sql .= ");";
			
			$this->lastsql = $sql;
			if ($this->trackback) { $this->trackback_sql[] = __METHOD__.": ".$sql; }
			$this->result = mysqli_query($this->link,$sql);
			if (!$this->CheckError()) {
				$this->last_id = mysqli_insert_id($this->link);
				$this->affectedrows = mysqli_affected_rows($this->link);
				return true;
			} else {
				return false; 
			}
		}
	}

/**
* Summary. Ejecuta una sentencia INSERT extendida.
* @param str $tabla El nombre de la tabla a insertar.
* @param array $campos Un array simple con la lista de nombres de campos.
* @param array $valores Un array simple o compuesto. La cantidad de valores en los subarray de este array debe coincidir con la cantidad de campos enumerados en el parámetro $campos.
* @return bool true si no hubo errores al ejecutar la instrucción, o false en caso contrario.
* @tutorial $valores puede ser un array múltiple, los valores de cada elemento se corresponderán por orden con los nombres de campos listados en el parámetro $campo. Consultar la propiedad errmsg para saber cuál fue el error, y affectedrows para saber cuántos registros se insertaron. Consultar la propiedad ->last_id para saber el valor generado para un campo AUTO_INCREMENT, usualmente la clave primaria.
*/
	function MultiInsert($tabla, $campos, $valores) {
		$this->affectedrows = -1;
		if ((!is_array($campos)) or (!is_array($valores))) {
			$this->error = true;
			$this->errno = -1;
			$this->errmsg = "Parámetros 'campos' y 'valores' deben ser array.";
			return false;
		} else {
			if (count($valores) > 0) {
					$sql = "INSERT INTO `".$tabla."` (`".implode("`, `",$campos)."`) VALUES ";
					
					foreach ($valores as $key => $value) {
						$sql .= "('".implode("', '",$value)."'),";
					}
					$sql = substr($sql, 0, -1); // Quita la última coma.

					$this->lastsql = $sql;
					if ($this->trackback) { $this->trackback_sql[] = __METHOD__.": ".$sql; }
					$this->result = mysqli_query($this->link,$sql);
					if (!$this->CheckError()) {
						$this->last_id = mysqli_insert_id($this->link);
						$this->affectedrows = mysqli_affected_rows($this->link);
						return true;
					} else {
						return false; 
					}
					
			} else {
				$this->error = true;
				$this->errno = -2;
				$this->errmsg = "Valores está vacío";
				return false;
			}
		}
	} // MultiInsert

/**
* Summary. Ejecuta una sentencia DELETE de SQL.
* @param str $tabla El nombre de la tabla a la cual hacer el DELETE.
* @param str $where Condiciones de la cláusula WHERE. Es obligatorio para asegurar que no ocurran borrados accidentales.
* @return bool true si no hubo errores al ejecutar la instrucción, o false en caso contrario.
* @tutorial Consultar la propiedad errmsg para saber cuál fue el error, y affectedrows para saber cuántos registros se eliminaron.
*/
	function Delete($tabla, $where) {
		$sql = "DELETE FROM `".$tabla."` WHERE ".$where.";";
		$this->lastsql = $sql;
		if ($this->trackback) { $this->trackback_sql[] = __METHOD__.": ".$sql; }
		$this->result = mysqli_query($this->link,$sql);
		if (!$this->CheckError()) {
			$this->affectedrows = mysqli_affected_rows($this->link);
			return true; }
		else {
			return false;
		}
	}

/**
* Summary. Devuelve el primer registro de un consulto de resultado de una sentencia SELECT.
* @param pointer $res default nulll Opcional. Puntero a un conjunto de resultados. Si se proporciona, el método se refiere al primero de ese resultado, si no, usa el puntero local al objeto establecido en la última llamada a ->Query()
* @return array/bool El primer registro del conjunto de resultados o false en caso que el resultado no tenga registros.
*/
	function First($res = NULL) {
		if ($res == NULL) {
			$res = $this->result;
		}
		if (mysqli_num_rows($res) > 0) {
			mysqli_data_seek($res,0);
			return mysqli_fetch_assoc($res);
		}
		else { return false; }
	}
	
/**
* Summary. Devuelve el siguiente registro de un consulto de resultado de una sentencia SELECT.
* @param pointer $res default nulll Opcional. Puntero a un conjunto de resultados. Si se proporciona, el método se refiere al siguiente de ese resultado, si no, usa el puntero local al objeto establecido en la última llamada a ->Query()
* @return array/bool El siguiente registro del conjunto de resultados o false en caso que ya no haya más registros leídos del conjunto.
*/
	function Next($res = NULL) {
		if ($res == NULL) {
			$res = $this->result;
		}
		if (mysqli_num_rows($res) > 0) {
			return mysqli_fetch_assoc($res);
		}
		else { return false; }
	}

/**
* Summary. Devuelve el último registro de un consulto de resultado de una sentencia SELECT.
* @param pointer $res default nulll Opcional. Puntero a un conjunto de resultados. Si se proporciona, el método se refiere al último de ese resultado, si no, usa el puntero local al objeto establecido en la última llamada a ->Query()
* @return array/bool El último registro del conjunto de resultados o false en caso que el resultado no tenga registros.
*/
	function Last($res = NULL) {
		if ($res == NULL) {
			$res = $this->result;
		}
		if (mysqli_num_rows($res) > 0) {
			mysqli_data_seek($res,mysqli_num_rows($res)-1);
			return mysqli_fetch_assoc($res);
		} else { return false; }
	}

/**
* Summary. Devuelve un registro arbitrario de una sentencia SELECT.
* @param pointer $res default nulll Opcional. Puntero a un conjunto de resultados. Si se proporciona, el método se refiere al último de ese resultado, si no, usa el puntero local al objeto establecido en la última llamada a ->Query()
* @praram int $num El número de registro a devolver. Es decir, se refiere al enésimo registro en el conjunto de resultados (NO ES el `id` del registro). Cero es el primero. ->numrows - 1 es el último.
* @return array/bool El registro del conjunto de resultados o false en caso que el resultado no tenga registros.
*/
	function Seek($num, $res = NULL) {
		$result = false;
		if ($res == NULL) {
			$res = $this->result;
		}
		if (is_int($num)) {
			$num = (int)$num;
			if ((mysqli_num_rows($res) > 0) and ($num < mysqli_num_rows($res))) {
				mysqli_data_seek($res,$num);
				$result = mysqli_fetch_assoc($res);
			}
		}
		return $result;
	}

/**
* Summary. Busca en la tabla $tabla, un registro cuyo campo $campo tiene el valor $valor y en caso de encontrarlo lo devuelve como un array asociativo (el registro completo). En caso contrario devuelve FALSE.
* @param str $tabla. Nombre de la tabla donde buscar.
* @param str $campo Nombre del campo dentro de la tabla $tabla en la cual realizar la búsqueda.
* @param str $valor El valor buscado en la tabla $tabla para el campo $campo.
* @param str $altorden default null Nombre del campo a usar como índice de búsqueda. Si es null, entonces el orden es el "orden natural" de la tabla.
* @return array/bool El registro encontrado o false en caso que no haya sido encontrado (o haya habido un error).
* @tutorial Devuelve el primer registro que cumpla la condición antes mencionada. Si hay más registros coincidentes se debe usar el método ->Next() para recuperar los siguientes. Para saber cuántos registros coincidieron consultar la propiedad ->numrows.
*/
	function SeekBy($tabla, $campo, $valor, $altorden = null) {
		$result = false;
		if (!empty($tabla) and !empty($campo)) {
			$sql = "DESCRIBE `".$tabla."` `".$campo."`";
			$this->lastsql = $sql;
			if ($this->trackback) { $this->trackback_sql[] = __METHOD__.": ".$sql; }
			$this->result = mysqli_query($this->link, $sql);
			if (!$this->CheckError()) {
				$result = mysqli_fetch_assoc($this->result);
				if ($result === FALSE) {
					$this->error = true;
					$this->errno = 1054;
					$this->errmsg = "Unknown column '".$campo."' in table '".$tabla."'";
				} else {
					$sql = "SELECT * FROM `".$tabla."` WHERE ";
					if ((stripos($result['Type'],"varchar(") == 0) or (stripos($result['Type'],"text") == 0)) {
						$sql .= "LOWER(`".$campo."`) LIKE LOWER('".$valor."')";
					} else {
						$sql .= "`".$campo."` = '".$valor."' ";
					}
					if (!empty($altorden)) {
						$sql .= " ORDER BY `".$altorden."`";
					}
					$this->Query($sql);
					$this->lastsql = $sql;
					if (!$this->error and $this->numrows > 0) {
						$result = $this->First();
					} else {
						$result = false;
					}
				}
			}
		}
		return $result;
	}

/**
* Summary. Delvuelve cuántos registros hay en el conjunto de resultados apuntado por el parámetro $res.
* $param pointer $res. Puntero a un conjunto de resultados.
* @return int El número de registros o false en caso que $res no apunte a un conjunto de resultados válido.
*/
	function GetNumRows($res) {
		return mysqli_num_rows($res);
	}

/**
* Summary. Imprime el mensaje de error que generó el servidor MySQL y la última sentencia ejecutada. Si no hubo error, este método no hace nada.
* @param bool $forzar Muestra el mensaje aún cuando el modo no sea DEVELOPE.
*/
	function ShowLastError($forzar = false) {
		if (($forzar == true) OR (DEVELOPE)) {
			if ($this->error) {
				echo $this->errno.": ".$this->errmsg."<br />";
				echo $this->lastsql."<br>";
			}
		}
	} // function ShowLastError

/**
* Summary. Fuerza a que la conexión e interpretación de la tabla de caracteres de MySQL sea UTF-8.
* @param bool $value default true Forzar UTF-8, false latin1.
* @return bool true en caso de éxtio, false en caso de error.
*/
	function SetUTF8($value=true) {
		$sql = ($value)?"SET NAMES 'utf8'":"SET NAMES 'latin1'";
		$this->result = mysqli_query($this->link,$sql);
		return $this->CheckError();
	}
	
/**
* Summary. Fuerza a que se acepte el modo de GROUP BY sea laxo.
* @return bool true en caso de éxtio, false en caso de error.
*/
	function SetONLY_FULL_GROUP_BY_off() {
		$sql = "SET SESSION sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));";
		$this->result = mysqli_query($this->link,$sql);
		return $this->CheckError();
	}
/**
* Summary. Escapa los caracteres especiales de una cadena para usarla en una sentencia SQL, tomando en cuenta el conjunto de caracteres actual de la conexión.
* @param str $str La cadena de caracteres sospechosa.
* @return str La cadena de caracteres escapada.
* @note Ignorar los valores de tipo object o array. Esto VA A CAUSAR problemas cuando se hacen update o insert, pero serán atajados por la base de datos.
*/
	function RealEscape($str) {
		if ($str !== NULL) { 
			if(is_object($str) or (is_array($str))) {
				return $str;
			} else {
				return mysqli_real_escape_string($this->link,$str);
			}
		} else {
			return NULL;
		}
	}
	
/**
* Summary. Establece la conexión usando las constantes.
*/
	function InitDB() {
		if ($this->IsConnected() == false) {
			$this->Connect(DBHOST, DBNAME, DBUSER, DBPASS, DBPORT);
		}
	}
/**
* Summary. Alias de ->Disconnect()
*/
	function FinalizeDB() {
		$this->Disconnect();
	}
	
/**
* Summary. Esta función devuelve un array en base a la sql pasada como parámetro.
* @param str $sql La sentencia SQL a ejecutar.
* @param bool $contar Mismo significado que $contar de ->Query().
* @param str/array Un string indicando el único campo que se desea que sea devuelto, o un array para más de un campo.
* @return array $result Un array con los campos implicados en la sentencia, si corresponde.
* @tutorial El tercer campo se recomienda para obtener array('campo'=>'valor'), en vez de array('resultado 1'=>array('campo'=>'valor')), en una consulta que devuleve una sola columna.
*/
	public function GetArray($sql, $contar = false, $fields = null){
		$result = array();
		$this->Query($sql,$contar);
		if (!$this->error) {
			if ($this->numrows > 0) {
				$aux = array();
				$i = 0;
				while ($fila = $this->Next()) {
					if ($fields != null) {
						if (is_array($fields)) {
							foreach ($fields as $llave => $campo) {
								$aux[$i][$campo] = $fila[$campo];
							}
						}else{
							$aux[$i] = $fila[$fields];
						}
					}else{
						$aux[$i] = $fila;
					}
					$i++;
				}
				$result = $aux;
			}
		}
		return $result;
	} // function GetArray

/**
* Summary. Aplica el método ->RealEscape a cada uno de los elementos (y su índice) del array que se pasa como parámetro.
* @param array $arr El array sospechoso.
* @return array El array correctamente escapado.
*/
	public function RealEscapeArray($arr) {
		$result = array();
		foreach ($arr as $key => $value) {
			$key = $this->RealEscape($key);
			$result[$key] = $this->RealEscape($value);
		}
		return $result;
	}

/**
* Summary. Determina si el nombre de archivo pasado como parámetro existe y es accesible. Se usa en ->SetSSL()
* @param str $file El nombre (puede incluir el path) del archivo.
* @return bool true en caso que sí sea accesible, false en caso contrario.
*/
	private function ExisteArchivo($file) {
		return (file_exists($file) and is_file($file) and is_readable($file));
	}
/**
* Summary. Analiza el resultado de una consulta y devuelve las propiedades de los campos devueltos rellenando la propiedad ->tipoCampos
* @param pointer $res default nulll Opcional. Puntero a un conjunto de resultados. Si se proporciona, el método se refiere a ese resultado, si no, usa el puntero local al objeto establecido en la última llamada a ->Query()
* @param bool/array $tipos Cuando true devuelve todas las propiedades de cada campo de la consulta, cuando array indica los nombres de las propiedades del campo que se quieren obtener
* @return bool/array Un array con la lista de campos y sus propiedades o false en caso de error.
* @note Las propiedades de campos, para el array $tipos, puede ser: name, orgname, table, orgtable, def, db, catalog, max_length, length, charsetnr, flags, type, decimals
*/
	public function getFieldsProperties($res = null, $tipos = true) {
		$result = false;
		if ($res == NULL) { $res = $this->result; }
		$cantidadCampos = mysqli_num_fields($res);
		for ($i=0; $i < $cantidadCampos; $i++) { 
			$field = mysqli_fetch_field_direct($res, $i); // Esto regresa un objeto, no un array.
			//ShowVar($field);
			if(is_array($tipos)){
				$aux = array();
				foreach($tipos as $prop) {
					if (isset($field->$prop)) {
						$aux[$prop] = $field->$prop;
					}
				}
				$this->tipoCampos[$field->name] = $aux;
				reset($tipos);
			}else{
				//$this->tipoCampos[$field->name] = array("type"=>$field->type, "length"=>$field->length, "decimals"=>$field->decimals );
				$this->tipoCampos[$field->name] = (array)$field;
			}
		}
		return $this->tipoCampos;
	}

}

?>