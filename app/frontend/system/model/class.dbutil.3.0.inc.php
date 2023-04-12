<?php
/*
	Clase fundacional de acceso a la base de datos MySQL.
	Requiere MySQL 8.0 o superior con soporte para campos tipo JSON.
	Created: 2021-08-14
	Author: Rebrit SRL.
	
	Constantes que podrían estar definidas y afectan a esta clase:
			  DEVELOPE: Establecer si se está en modo desarrollo o no, esto genera o no mensajes explícitos para propósitos de depuración.
		SSL_CERTS_PATH: Es el directorio donde buscar los certificados SSL.
*/

require_once(dirname(__FILE__).DIRECTORY_SEPARATOR."class.dbutil_exception.inc.php");

$connection_to_database = null;


class cDb {
	// Propiedades de conexión.
	public $dbhost = 'localhost';
	public $dbname = '';
	public $dbuser = 'root';
	public $dbpass = '';
	public $dbport = 3306;

	// Propiedades del manejo de errores.
	public $errmsg = ""; // Mensaje textual del último error.
	public $error = false; // Hubo un error en la última operación.
	public $errno = 0; // Código de error devuelto por MySQL.
	public $throwableErrors = false; // Generar una excepción cuando se encuentra un error en sentencia SQL.
	public $trackback = true; // Habilitar la traza de ejecuciones.
	public $trackback_sql = array(); // Lista de las consultas ejecutadas hasta ahora.

	// Propiedades de SSL.
	public $path_to_certs = null; // Directorio donde están los certificados SSL.
	public $key_pem = null; // Nombre del archivo key.
	public $cert_pem = null; // Nombre del archivo pem
	public $ca_pem = null; // Nombre del archivo de certificado.

	// Propiedades de las consultas ejecutadas.
	public $result = null; // Puntero al resultado de una consulta.
	public $last_id = 0; // Último ID autonumérico generado por INSERT.
	public $lastsql = ""; // La última sentencia SQL ejecutadas hasta ahora.
	public $rowsNumber = 0; // Cantidad de registros devueltos por la última consulta.
	public $cantidad = 0; // Alias del anterior.
	public $getCount = false; // Contar o no la cantidad de registros devueltos por la última consulta.
	public $numrows = 0; // Cantidad de registros implicados en la ultima operación.
	public $affectedrows = 0; // Cantidad de registros afectados por UPDATE o DELETE.
	private $rowAs = 'assoc'; // El tipo de resultado a devolver para cada registro ('assoc','numeric','object').

	// Propiedades de los campos.
	public $fieldTypes = array(); // Lista de los tipos de campos devueltos.
	public $getFieldTypes = false; // Habilitar la toma de los tipos de campos.
	const defaultFieldProperties = ['type','length','decimals']; // Nombre de las propiedades de los campos que se quiere devolver cuando no se indica ninguno.
	public $fieldProperties = array(); // Lista de propiedades de los campos que se quieren obtener.
	public $is_ssl = false; // Indica si la conexión es por SSL.
	
	// Propiedades privadas
	private $link = null; // El recurso o conexión a la base de datos.

/**
* Summary. Constructor de la clase.
*/
	public function __construct(string $dbhost = null, string $dbname = null, string $dbuser = null, string $dbpass = null, int $dbport = 3306) {
		global $connection_to_database;
	/* Si las constantes están definidas... */
		$this->dbhost = (defined("DBHOST"))?DBHOST:$this->dbhost;
		$this->dbname = (defined("DBNAME"))?DBNAME:$this->dbname;
		$this->dbuser = (defined("DBUSER"))?DBUSER:$this->dbuser;
		$this->dbpass = (defined("DBPASS"))?DBPASS:$this->dbpass;
		$this->dbport = (defined("DBPORT"))?DBPORT:$this->dbport;
		

	/* Si alguno de los parámetros tiene valor ... */
		$this->dbhost = $dbhost??$this->dbhost;
		$this->dbname = $dbname??$this->dbname;
		$this->dbuser = $dbuser??$this->dbuser;
		$this->dbpass = $dbpass??$this->dbpass;
		$this->dbport = $dbport??$this->dbport;

		if (defined("DEVELOPE")) {
			$this->trackback = DEVELOPE;
		}
		if (defined("SSL_CERTS_PATH")) {
			$this->path_to_certs = SSL_CERTS_PATH;
		} else {
			$this->path_to_certs = dirname(__FILE__).DIRECTORY_SEPARATOR;
		}
		$this->fieldProperties = self::defaultFieldProperties;
		$this->rowAs = 'assoc';
		if (!is_null($connection_to_database)) { // Reutilizar la conexión si ya está conectado.
			$this->link = $connection_to_database;
		}
	}


/* **************************** Métodos de conexión ************************************** */
/**
* Summary. Intenta conectarse de acuerdo a los datos en las propiedades correspondientes.
* @param bool $secure default false Establece que la conexión se hará por SSL.
* @return link. Recurso de la conexión o false en caso de error.
*/

	public function Connect(bool $secure = false) {
		global $connection_to_database;
		if (!is_null($this->link)) { // Si ya está conectado, no volver a conectar.
			return $this->link;
		}
		$this->link = mysqli_init();
		if ($secure) {
			$result = $this->GetConnectSecure();
		} else {
			$result = $this->GetConnect();
		}
		if (!$result or (mysqli_connect_errno() > 0)) {
			$this->link = null;
			$this->error = true;
			$this->errno = mysqli_connect_errno();
			$this->errmsg = $this->errno.": ".mysqli_connect_error();
			throw new DBException($this->errmsg, $this->errno);
		}
		$connection_to_database = $this->link;
		return $result;
	}

/**
* Summary. Establece las propiedades de la conexión usando SSL.
* @param str $path Ruta hacia los archivos .pem
* @param str $key, $cert, $ca Nombre de los archivos .pem
*/
	public function SetSSL(string $path = null, string $key = null, string $cert = null, string $ca = null) {
		$this->path_to_certs = $path;
		$this->key_pem = $key;
		$this->cert_pem = $cert;
		$this->ca_pem = $ca;
		if ((!empty($this->key_pem)) and !$this->ExisteArchivo($this->path_to_certs.$this->key_pem)) { throw New DBException($this->path_to_certs.$this->key_pem.' no encontrado.'); }
		if ((!empty($this->cert_pem)) and !$this->ExisteArchivo($this->path_to_certs.$this->cert_pem)) { throw New DBException($this->path_to_certs.$this->cert_pem.' no encontrado.'); }
		if ((!empty($this->ca_pem)) and !$this->ExisteArchivo($this->path_to_certs.$this->ca_pem)) { throw New DBException($this->path_to_certs.$this->ca_pem.' no encontrado.'); }
		return true;
	}
/**
* Summary. Trata de establcer una conexión usando SSL.
* @return link. Recurso de la conexión o false en caso de error.
*/
	private function GetConnectSecure() {
		$this->is_ssl = true;
		mysqli_ssl_set($this->link,
			$this->path_to_certs.$this->key_pem,
			$this->path_to_certs.$this->cert_pem,
			$this->path_to_certs.$this->ca_pem,
			NULL,
			NULL
		);
		return @mysqli_real_connect($this->link, $this->dbhost, $this->dbuser, $this->dbpass, $this->dbname, $this->dbport, null, MYSQLI_CLIENT_SSL_DONT_VERIFY_SERVER_CERT);
	}

/**
* Summary. Trata de establcer una conexión.
* @return link. Recurso de la conexión o false en caso de error.
*/
	private function GetConnect() {
		$this->is_ssl = false;
		return @mysqli_real_connect($this->link, $this->dbhost, $this->dbuser, $this->dbpass, $this->dbname, $this->dbport);
	}

/**
* Summary. Cierra la conexión con la base de datos.
*/
	public function Disconnect() {
		if ($this->link !== NULL) {
			mysqli_close($this->link);
			$this->link = NULL;
			$this->opened = false;
			$db_link = $this->link;
		}
	}

/**
* Summary. Verifica si hay una conexión aberta.
* @return bool.
*/
	public function IsConnected() {
		return (!is_bool($this->link) and !($this->link == NULL));
	}

/**
* Summary. Devuelve el puntero a la conexión.
* @return link.
*/
	public function GetLink() {
		return $this->link;
	}
/**
* Summary. Devuelve una descripción de la conexión.
* @return str.
*/
	public function GetInfo() {
		return mysqli_get_host_info($this->link);
	}

/**
* Summary. Verifica si en la última operación hubo un error y establece las propiedades para así informarlo.
*/
	public function CheckError() {
		$this->errno = mysqli_errno($this->link);
		$this->error = $this->errno != 0;
		$this->errmsg = $this->errno.": ".mysqli_error($this->link);
		return $this->error;
	}
/**
* Summary. Imprime el mensaje de error que generó el servidor MySQL y la última sentencia ejecutada. Si no hubo error, este método no hace nada.
* @param bool $forzar Muestra el mensaje aún cuando el modo no sea DEVELOPE (trackback = false).
*/
	public function ShowLastError($forzar = false) {
		if (($forzar == true) OR ($this->trackback)) {
			if ($this->error) {
				echo $this->errno.": ".$this->errmsg."<br />";
				echo $this->lastsql."<br>";
			}
		}
	} // function ShowLastError



/* **************************** Métodos de Ejecución de consultas ************************************** */
/**
* Summary. Permite mandar a ejecutar cualquier instrucción SQL. Devuelve un puntero al conjunto de resultado si los hay. Se hace cargo de actualizar el estado de error y la cantidad de registros devueltos (->numrows) o afectados (->affectedrows) por la instrucción.
* @param str $sql La sentencia SQL a ejecutar.
* @param bool $getCount default false Cuando vale true, establece la propiedad ->cantidad que es la cantidad de registros afectados sin tener en cuenta la cláusula LIMIT (mientras que ->numrows devuelve siempre <= LIMIT).
* @param bool/array $getFieldTypes Cuando true devuelve las propiedades de cada campo de la consulta, cuando array indica los nombres de las propiedades del campo que se quieren obtener
* @return pointer/bool Puntero a los resultados, o true o false.
*/
	public function Query($sql, $getCount = false, $getFieldTypes = false) {
		if (!$this->IsConnected()) { throw new DBException('No hay conexión abierta.', 1); } // Sin una conexión activa esto no funciona...
		$sql = trim($sql);
		if (($getCount or $this->getCount) AND preg_match('~^SELECT\b~i',$sql)) {
			$sql = 'SELECT SQL_CALC_FOUND_ROWS'.substr($sql, 6);
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
			if ($getFieldTypes or $this->getFieldTypes) { // Obtener las propiedades de los campos devueltos por la consulta.
				$this->GetFieldTypes();
			}
			if ($getCount or $this->getCount) { // Obtener la cantidad de registros sin la cláusula LIMIT.
				$this->GetRowCount();
			}
		} else {
			if ($this->throwableErrors) { throw new DBException($this->errmsg, $this->errno); }
		}

		return $this->result;
	}
	
	private function GetRowCount() {
		$this->rowsNumber = 0;
		if (!is_bool($this->result)) { // Debe de haber un puntero a una consulta para poder contar.
			$sql_found = 'SELECT FOUND_ROWS() AS `ilsagitdunombredenregistrementsretournes`'; // Un alias que es poco probable que se use en una consulta
			if ($this->trackback) { $this->trackback_sql[] = __METHOD__.": ".$sql_found; }
			$aux = mysqli_query($this->link, $sql_found);
			$aux2 = mysqli_fetch_assoc($aux);
			$this->rowsNumber = $aux2['ilsagitdunombredenregistrementsretournes'];
		}
		$this->cantidad = $this->rowsNumber;
	}

	private function GetFieldTypes() {
		$this->fieldTypes = array();
		if (!is_bool($this->result)) { // tiene que haber un puntero a resultado para poder analizar los campos
			$this->fieldTypes = $this->getFieldsProperties();
		}
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

	public function Update($tabla, array $lista, $where = "") {
		$this->affectedrows = -1;
		if (!$this->IsConnected()) { throw new DBException('No hay conexión abierta.', 1); } // Sin una conexión activa esto no funciona...
		if (!is_array($lista)) { throw new DBException("Segundo parámetro no es array ('campo'=>'valor')"); }
		$sql = "UPDATE `".$tabla."` SET ";
		$work = array();
		foreach ($lista as $key => $value) {
			$key = '`'.$key.'`';
			if (is_null($value)) {
				$work[] = $key."=NULL";
			} else {
				$work[] = $key."='$value'";
			}
		}
		$sql .= implode(',',$work);
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
			if ($this->throwableErrors) { throw new DBException($this->errmsg, $this->errno); }
			return false;
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
		if (!$this->IsConnected()) { throw new DBException('No hay conexión abierta.', 1); } // Sin una conexión activa esto no funciona...
		if (!is_array($lista)) { throw new DBException("Segundo parámetro no es array ('campo'=>'valor')", -1); }
		$sql = "INSERT INTO `".$tabla."` (`".implode("`, `",array_keys($lista))."`) VALUES (";
		reset($lista);
		$work = array();
		foreach ($lista as $key => $value) {
			if (is_null($value)) {
				$work[] = "NULL";
			} else {
				$work[] = "'$value'";
			}
		}
		$sql .= implode(',',$work).");";
		$this->lastsql = $sql;
		if ($this->trackback) { $this->trackback_sql[] = __METHOD__.": ".$sql; }
		$this->result = mysqli_query($this->link,$sql);
		if (!$this->CheckError()) {
			$this->last_id = mysqli_insert_id($this->link);
			$this->affectedrows = mysqli_affected_rows($this->link);
			return true;
		} else {
			if ($this->throwableErrors) { throw new DBException($this->errmsg, $this->errno); }
			return false; 
		}
	}
/**
* Summary. Ejecuta una sentencia DELETE de SQL.
* @param str $tabla El nombre de la tabla a la cual hacer el DELETE.
* @param str $where Condiciones de la cláusula WHERE. Es obligatorio para asegurar que no ocurran borrados accidentales.
* @return bool true si no hubo errores al ejecutar la instrucción, o false en caso contrario.
* @tutorial Consultar la propiedad errmsg para saber cuál fue el error, y affectedrows para saber cuántos registros se eliminaron.
*/
	function Delete($tabla, $where) {
		if (!$this->IsConnected()) { throw new DBException('No hay conexión abierta.', 1); } // Sin una conexión activa esto no funciona...
		$sql = "DELETE FROM `".$tabla."` WHERE ".$where.";";
		$this->lastsql = $sql;
		if ($this->trackback) { $this->trackback_sql[] = __METHOD__.": ".$sql; }
		$this->result = mysqli_query($this->link,$sql);
		if (!$this->CheckError()) {
			$this->affectedrows = mysqli_affected_rows($this->link);
			return true; }
		else {
			if ($this->throwableErrors) { throw new DBException($this->errmsg, $this->errno); }
			return false;
		}
	}
/**
* Summary. Esta función devuelve un array en base a la sql pasada como parámetro.
* @param str $sql La sentencia SQL a ejecutar.
* @param bool $contar Mismo significado que $contar de ->Query().
* @param str/array Un string indicando el único campo que se desea que sea devuelto, o un array para más de un campo.
* @return array $result Un array con los campos implicados en la sentencia, si corresponde.
* @tutorial El tercer campo se recomienda para obtener array('campo'=>'valor'), en vez de array('resultado 1'=>array('campo'=>'valor')), en una consulta que devuleve una sola columna.
* @note Este método se usa en cContenidos.
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

/* ********************** Métodos de registros de Resultados de consultas *************************** */

/**
* Summary. Establece la propiedad ->rowAs
* @param string $rowas El valor a establecer para la pripiedad. Valores aceptados 'assoc','numeric','object'.
*/
	public function SetRowAs(string $rowas = 'assoc') {
		$this->rowAs = 'assoc';
		$rowas = strtolower(trim($rowas));
		if (in_array($rowas, ['assoc','numeric','object'])) {
			$this->rowAs = $rowas;
		}
	}

/**
* Summary. Obtener el valor de la propiedad ->rowAs
* @return string.
*/
	public function GetRowAs(string $rowas = 'assoc') {
		return $this->rowAs;
	}

/**
* Summary. Devuelve el primer registro de un consulto de resultado de una sentencia SELECT.
* @param mysqli_result $res default nulll Opcional. Puntero a un conjunto de resultados. Si se proporciona, el método se refiere al primero de ese resultado, si no, usa el puntero local al objeto establecido en la última llamada a ->Query()
* @return array/bool El primer registro del conjunto de resultados o false en caso que el resultado no tenga registros.
* @note El tipo de dato devuelto está determinado por la propiedad ->rowAs donde 'assoc' significa un array asociativo, 'numeric' un array de índice numérico y object un objeto de la clase stdClass
*/
	function First($res = NULL) {
		$result = NULL;
		if (!$this->IsConnected()) { throw new DBException('No hay conexión abierta.', 1); }
		if (is_null($res)) { $res = $this->result; }
		if (mysqli_num_rows($res) > 0) {
			mysqli_data_seek($res,0);
			switch ($this->rowAs) {
				case 'assoc': $result = mysqli_fetch_assoc($res); break;
				case 'numeric': $result = mysqli_fetch_row($res); break;
				case 'object': $result = mysqli_fetch_object($res); break;
			}
		}
		return $result;
	}
	
/**
* Summary. Devuelve el siguiente registro de un conjunto de resultado de una sentencia SELECT.
* @param mysqli_result $res default null Opcional. Puntero a un conjunto de resultados. Si se proporciona, el método se refiere al siguiente de ese resultado, si no, usa el puntero local al objeto establecido en la última llamada a ->Query()
* @return array/bool El siguiente registro del conjunto de resultados o false en caso que ya no haya más registros leídos del conjunto.
* @note El tipo de dato devuelto está determinado por la propiedad ->rowAs donde 'assoc' significa un array asociativo, 'numeric' un array de índice numérico y object un objeto de la clase stdClass
*/
	function Next($res = NULL) {
		$result = NULL;
		if (!$this->IsConnected()) { throw new DBException('No hay conexión abierta.', 1); }
		if (is_null($res)) { $res = $this->result; }
		if (mysqli_num_rows($res) > 0) {
			switch ($this->rowAs) {
				case 'assoc': $result = mysqli_fetch_assoc($res); break;
				case 'numeric': $result = mysqli_fetch_row($res); break;
				case 'object': $result = mysqli_fetch_object($res); break;
			}
		}
		return $result;
	}

/**
* Summary. Devuelve el último registro de un conjunto de resultado de una sentencia SELECT.
* @param mysqli_result $res default null Opcional. Puntero a un conjunto de resultados. Si se proporciona, el método se refiere al último de ese resultado, si no, usa el puntero local al objeto establecido en la última llamada a ->Query()
* @return array/bool El último registro del conjunto de resultados o false en caso que el resultado no tenga registros.
* @note El tipo de dato devuelto está determinado por la propiedad ->rowAs donde 'assoc' significa un array asociativo, 'numeric' un array de índice numérico y object un objeto de la clase stdClass
*/
	function Last($res = NULL) {
		$result = NULL;
		if (!$this->IsConnected()) { throw new DBException('No hay conexión abierta.', 1); }
		if (is_null($res)) { $res = $this->result; }
		if (mysqli_num_rows($res) > 0) {
			mysqli_data_seek($res,mysqli_num_rows($res)-1);
			switch ($this->rowAs) {
				case 'assoc': $result = mysqli_fetch_assoc($res); break;
				case 'numeric': $result = mysqli_fetch_row($res); break;
				case 'object': $result = mysqli_fetch_object($res); break;
			}
		}
		return $result;
	}

/**
* Summary. Delvuelve cuántos registros hay en el conjunto de resultados apuntado por el parámetro $res.
* $param pointer $res default null. Puntero a un conjunto de resultados.
* @return int El número de registros o false en caso que $res no apunte a un conjunto de resultados válido.
*/
	function GetNumRows($res = null) {
		if (!$this->IsConnected()) { throw new DBException('No hay conexión abierta.', 1); }
		if (is_null($res)) { $res = $this->result; }
		return mysqli_num_rows($res);
	}


/* **************************** Métodos con Campos ************************************** */
/**
* Summary. Analiza el resultado de una consulta y devuelve las propiedades de los campos devueltos rellenando la propiedad ->fieldTypes
* @param pointer $res default nulll Opcional. Puntero a un conjunto de resultados. Si se proporciona, el método se refiere a ese resultado, si no, usa el puntero local al objeto establecido en la última llamada a ->Query()
* @param bool $allProperties Cuando true devuelve todas las propiedades de cada campo de la consulta, cuando false se usa la lista en la propiedad ->fieldProperties.
* @return bool/array Un array con la lista de campos y sus propiedades o false en caso de error.
* @note Las propiedades de campos, para el array $fieldProperties, puede ser: name, orgname, table, orgtable, def, db, catalog, max_length, length, charsetnr, flags, type, decimals
*/
	public function GetFieldsProperties($res = null, $allProperties = false) {
		$result = false;
		if (is_null($res)) { $res = $this->result; }
		$fieldCount = mysqli_num_fields($res);
		for ($i=0; $i < $fieldCount; $i++) { 
			$field = mysqli_fetch_field_direct($res, $i); // Esto regresa un objeto, no un array.
			if($allProperties){
				$this->fieldTypes[$field->name] = (array)$field;
				continue;
			}
			$aux = array();
			foreach($this->fieldProperties as $prop) {
				if (isset($field->$prop)) {
					$aux[$prop] = $field->$prop;
				}
			}
			$this->fieldTypes[$field->name] = $aux;
			reset($this->fieldProperties);
		} // for
		return $this->fieldTypes;
	}




/* **************************** Métodos Helpers ************************************** */

/**
* Summary. Fuerza a que la conexión e interpretación de la tabla de caracteres de MySQL sea UTF-8.
* @param bool $value default true Forzar UTF-8, false latin1.
* @return bool true en caso de éxtio, false en caso de error.
*/
	function SetUTF8($value=true) {
		if (!$this->IsConnected()) { throw new DBException('No hay conexión abierta.', 1); }
		$sql = ($value)?"SET NAMES 'utf8'":"SET NAMES 'latin1'";
		$this->result = mysqli_query($this->link,$sql);
		return $this->CheckError();
	}

/**
* Summary. Forzar GROUP BY laxo.
* @return bool true en caso de éxtio, false en caso de error.
*/
	function SetONLY_FULL_GROUP_BY_off() {
		if (!$this->IsConnected()) { throw new DBException('No hay conexión abierta.', 1); }
		$sql = "SET SESSION sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));";
		$this->result = mysqli_query($this->link,$sql);
		return $this->CheckError();
	}

/**
* Summary. Escapa los caracteres especiales de una cadena para usarla en una sentencia SQL, tomando en cuenta el conjunto de caracteres actual de la conexión.
* @param string $str La cadena de caracteres sospechosa.
* @return string La cadena de caracteres escapada.
* @note devolver sin cambios los valores de tipo object o array. Esto VA A CAUSAR problemas cuando se hacen update o insert, pero serán atajados por la base de datos.
*/
	function RealEscape(?string $str = '') {
		if (!$this->IsConnected()) { throw new DBException('No hay conexión abierta.', 1); }
		if (empty($str)) { return $str; }
		if(is_object($str) or (is_array($str))) {
			return $str;
		}
		return mysqli_real_escape_string($this->link,$str);
	}
/**
* Summary. Aplica el método ->RealEscape a cada uno de los elementos (y su índice) del array que se pasa como parámetro.
* @param array $arr El array sospechoso.
* @return array El array correctamente escapado.
*/
	public function RealEscapeArray($arr) {
		if (!$this->IsConnected()) { throw new DBException('No hay conexión abierta.', 1); }
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

}