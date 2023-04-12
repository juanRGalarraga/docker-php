<?php
/*
	Clase para devolver listados a través del Web Service.
	
	El cliente que hace uso de listados DEBE enviar la cookie de sesión PHP. De otra forma esto no funciona.
	El resolver que haga uso de esta clase DEBE enviar el listid al cliente como parte de la respuesta.
	
*/

require_once(DIR_includes."libfnckeys.php");


class cListado {
	
	private $camposOrdenacion = []; // Aquí se almacenan los campos permitidos en la ordenación.
	private $currentSearch = null;//busqueda actual que se esta realizando
	public $ordenes = null; // El campo y la dirección de la ordenación.
	public $listid = null;
	public $sql = null;
	public $PaginaActual = 1;
	public $ItemsPorPagina = 25;
	public $SearchFields = [];
	public $withLimit = true;//Indica si incluir limit en la consulta o no
	
	
	function __construct(string $listid) {
		if (!empty($listid)) {
			$this->listid = $listid;
			if (isset($_SESSION[$listid])) {
				foreach($_SESSION[$listid] as $key => $value) {
					$this->SetSesValue($key, $value);
				}
			} else {
				$_SESSION[$listid] = array();
			}
		}
	} // constructor.

/**
* Summary. Determina si un string es un identificador MySQL válido.
*/
	private function IsValidIdent(string $value) {
		return preg_match('/^[0-9,a-z,A-Z$_.]+$/',$value) == 1; // Solo se admiten letras, números, guión bajo y el signo pesos, al menos uno.
	}

/**
* Summary. Establecer el valor de una variable de sesión para el listado.
* @param string $name El nombre de la variable.
* @param string $value El valor correspondiente.
*/
	public function SetSesValue(string $name, $value) {
		if (!empty($name)) {
			$_SESSION[$this->listid][$name] = (!is_array($value))?trim($value):$value;
			$this->$name = $_SESSION[$this->listid][$name];
		}
	} // SetSesValue

/**
* Summary. Devolver el valor de una variable de sesión para el listado o nulo en caso de no existir.
* @param string $name El nombre de la variable.
* @return string $value El valor correspondiente.
*/
	public function GetSesValue(string $name) {
		if (isset($_SESSION[$this->listid][$name]) AND $_SESSION[$this->listid][$name]) {
			return $_SESSION[$this->listid][$name];
		} else {
			return null;
		}
	} // GetSesValue
/**
* Summary. Devolver todos los valores de la sesión actual (para el listado actual)
* @return array
*/
	public function GetSes() {
		return $_SESSION[$this->listid];
	}

/**
* Summary. Obtener valores de variables desde los parámetros del Web Service.
* @param object $ws cWebService.
*/
	public function GetParams(object $ws = null) {
		if (is_a($ws, "cWebService")) {
			if ($aux = $ws->GetParams(['pag','p','pagina','page']) and CheckInt($aux)) {
				$this->SetSesValue('PaginaActual',$aux);
			}
			$aux = $ws->GetParams(['rpp','regs']);
			if (CheckInt($aux)) {
				$this->SetSesValue('ItemsPorPagina',$aux);
			}
			if ($ws->GetParams('noord')) { $this->ResetOrderFields(); }
			if ($aux = $ws->GetParams(['ord','orden','order','orderby'])) {
				if (is_array($aux) and (count($aux)>0)) {
					$this->SetOrderBy($aux);
				}
			}
			
			if (($aux = $ws->GetParams(['limit'])) !== null) {
				if($aux == false OR $aux == 0){
					$this->withLimit = false;
				}
			}
		}
	}
/**
* Summary. Establece cuáles son los campos de ordenación posibles para el listado actual.
* @param array $list La lista de nombres de campos.
*/
	public function SetOrderFields(array $list) {
		if (count($list) == 0) { return; }
		$this->camposOrdenacion = $list;
	} // SetOrderFields
/**
* Summary. Restablece los campos de ordenación a "ningún orden".
*/
	public function ResetOrderFields() {
		foreach($this->camposOrdenacion as $value) {
			$this->ordenes[$value] = '';
		}
		$this->SetSesValue('orden', $this->ordenes);
	} // ResetOrderFields
/**
* Summary. Establece un nuevo orden para la cláusula ORDER BY.
* @param array $ord Es un array de un solo elemento cuyo índice es el nombre del campo y el valor es la dirección de la ordenación.
* @note Esto funciona así: Se busca el nombre del campo en el array de órdenes, si existe, se elimina y se agrega al final del array el campo que se pasa como parámetro y luego se gira el array para que quede primero. Esto tiene como efecto que el último en llegar es el primero en la cláusula ORDER BY.
*/
	public function SetOrderBy(array $ord) {
		$ord = array_reverse($ord, true); // Se debe evaluar invertido.
		$this->ordenes = $this->GetSesValue('orden');
		if(!empty($this->ordenes)){
			$this->ordenes = array_reverse($this->ordenes); // La cabeza queda en la cola.
		}
		foreach($ord as $campo => $direccion) {
			$campo = mb_substr($campo,0,64);
			if (!$this->IsValidIdent($campo)) { continue; } // Si no es un identificador válido, arafue!
			if (!in_array($campo, $this->camposOrdenacion)) { continue; } // El campo indicado no está entre los campos permitidos para la ordenación.
			$direccion = strtoupper(substr($direccion,0,4));
			if (!in_array($direccion, ['','ASC','DESC'])) { $direccion = ''; } // Solo valen estas palabras clave o string vacío.
			if (isset($this->ordenes[$campo])) { // Si el campo ya existe...
				unset($this->ordenes[$campo]); // Se retira del array...
			}
			if (!empty($direccion)) // Se agrega solo si tiene direccion
				$this->ordenes[$campo] = $direccion; // Y se agrega al final.
		}
		$this->ordenes = array_reverse($this->ordenes); // Se reinvierte para que los recién agregados queden al inicio.
		$this->SetSesValue('orden', $this->ordenes);
	} // SetOrderBy

/**
* Summary. Establece los campos por los cuales hacer búsqueda textual.
* @param array $list La lista de nombres de campos.
*/
	public function SetSearchFields(array $list) {
		if (count($list) == 0) { return; }
		$_SESSION[$this->listid]['buscar'] = $list;
		$this->SearchFields = $this->SearchFields+$list;
	}

/**
* Summary. Arma la condición de búsqueda textual en la cláusula WHERE de la consulta SQL.
* @param string $lexema El lexema de búsqueda.
* @param array $fields optional Los campos donde buscar el lexema. Si no se indica se usa la lista previamente guardada con ->SetSearchFields.
*/
	public function SetSearch(string $lexema, array $fields = null):string {
		$lexema = trim($lexema);
		if (empty($lexema)) { return ''; }
		if (empty($fields)) {
			$fields = $this->SearchFields;
		}
		if (empty($fields)) { return ''; }
		$lexema = mb_escape($lexema); // Hace lo mismo que mysqli_real_escape_string()
		$work = [];
		foreach($fields as $field) {
			$work[] = "LOWER(".$field.") LIKE LOWER('%".$lexema."%')";
		}
		$this->currentSearch = $lexema;
		return "AND ((".implode(") OR (",$work).")) ";
	}
/**
* Summary. Devuelve la cláusula ORDER BY de SQL.
*/
	public function MakeOrderBy() {
		$result = 'ORDER BY NULL ';
		$this->ordenes = $this->GetSesValue('orden');
		if (!empty($this->ordenes) and is_array($this->ordenes) and (count($this->ordenes) > 0)) {
			$par = [];
			foreach ($this->ordenes as $campo => $orden) {
				if (!empty($orden)) {
					$p = explode('.',$campo);
					if (count($p) > 1) {
						$par[]= SQLQuote($p[0]).".".SQLQuote($p[1])." ".$orden;
					} else {
						$par[] = SQLQuote($campo)." ".$orden;
					}
				}
			}
			if (count($par) > 0) {
				$result = 'ORDER BY '.implode(', ',$par);
			}
		}
		return trim($result);
	}

/**
* Summary. Arma la cláusula LIMIT de SQL.
*/
	public function MakeLimit() {
		$result = '';
		if (CheckInt($this->PaginaActual??null) and CheckInt($this->ItemsPorPagina??null) and $this->withLimit) {
			// ShowVar($this->ItemsPorPagina);
			if ($this->ItemsPorPagina > 0){
				$result = "LIMIT ".((($this->PaginaActual-1)*$this->ItemsPorPagina).",".$this->ItemsPorPagina);
			}
		}
		return $result;
	}
/**
* Summary. Establecer la consulta SQL.
* @param string $sql
*/
	public function SetSQL(string $sql) {
		$this->sql = trim($sql)." ".$this->MakeOrderBy()." ".$this->MakeLimit();
	}
/**
* Summary. Devuelve los datos de la consulta ejecutada.
* @param object $db cModels
*/

	public function GetResult(object $db):array {
		$result = array('header'=>array('listid'=>$this->listid,'cant'=>0,'items'=>0,'fields'=>[],'pag'=>$this->PaginaActual,'rpp'=>$this->ItemsPorPagina),'list'=>[]);
		if (is_a($db, "cModels")) {
			$db->Query($this->sql, true);
			if ($db->cantidad) {
				$result['header']['cant'] = $db->cantidad;
				$result['list'] = $db->GetAllRecords();
				$result['header']['fields'] = $this->camposOrdenacion;
				$result['header']['currentOrd'] = $this->ordenes;//El orden actual de los items
				$result['header']['items'] = count($result['list']);
				$result['header']['currentSearch'] = $this->currentSearch;
			}
		} else {
			cLogging::Write(__FILE__ ." ".__LINE__ ." No se pasó como parámetro un objeto cModels");
		}
		return $result;
	}




} // class
$listid = null;
if (isset($ws)) {
	$listid = (!empty($ws->params['listid']))?$ws->params['listid']:UnambiguousRandomChars(8);
}

$listado = new cListado($listid);
if (isset($campos_orden)) {
	$listado->SetOrderFields($campos_orden);
}
if (isset($campos_busqueda)) {
	$listado->SetSearchFields($campos_busqueda);
}
if (isset($ws)) {
	$listado->GetParams($ws);
}
