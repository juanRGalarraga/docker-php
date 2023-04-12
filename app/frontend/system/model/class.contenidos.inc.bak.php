<?php
/*	
	cContenidos v 3.0
	Author: DriverOp
	Created: 2018-11.24
	Desc: Debe traer de la tabla de contenidos en la DB, todos los datos necesarios del contenido a mostrar según la lista de alias que index.php armó a partir de la URL.
	Modif: 2021-01-31
	Author: DriverOp
	Desc:
		- Agregada implementación de método Get para tomar un contenido por su id.
		- Agregado método GetParents que devuelve la lista de ancestros de un contenido arbitrario.
	Modif: 2021-02-01
	Author: DriverOp
	Desc:
		- Agregado método ->Save().
*/

require_once(DIR_includes . "class.logging.inc.php");
require_once(DIR_model . "class.fundation.inc.php");

class cContenido extends cModels
{
	public $tabla = TBL_contenido;
	public $tabla_permisos = TBL_backend_permisos;
	public $withchilds = true; // Esto se usa en GetMenuItems()
	public $parents = array();
	public $puede_ver = true;
	public $puede_crear = true;
	public $puede_modificar = true;
	public $puede_borrar = true;
	public $cifid = null;
	public $lg_menues = null;

	// El usuario actualmente logueado... o no.
	public $usuario = null;

	private $refidsalt = null;

	// Constructor
	public function __construct()
	{
		parent::__construct();
		if (defined("DEVELOPE")) {
			$this->DebugOutput = DEVELOPE;
		}
		$this->actual_file = __FILE__;
		if (!isset($_SESSION['refidsalt'])) {
			$_SESSION['refidsalt'] = rand(100, 10000);
		}
		$this->refidsalt = $_SESSION['refidsalt'];
	} // function __construct

	/*
	Obtiene un contenido y setea las propiedades de la clase de acuerdo a los valores del contenido solicitado.
	$hand es el array de aliases para buscar.
	$posicion es el índice en $hand que apunta al alias que se busca actualmente.
	$parent_id es el id del parent del alias que se busca.
	
	Este método se llama recursivamente por cada item en $hand.
	Primero se busca el alias de la posición 0, ese alias debe tener un parent_id = 0 ya que es sí o sí raíz de una serie de contenidos.
	- Si se encuentra, 
*/
	public function GetContent($hand, $posicion = 0, $parent_id = 0)
	{
		$result = true;
		try {

			$handler = array();

			// Convertir $hand en array aunque se pase un string solo.
			if (is_string($hand)) {
				$handler[] = $hand;
			} else {
				$handler = $hand;
			}
			if ($this->DebugOutput > 1) {
				cLogging::Write(basename(__FILE__) . " " . __METHOD__ . " " . print_r($hand, true));
			}

			if ($posicion > 0) {
				$this->GuardarParent(($posicion - 1)); // Se guarda el ancestro inmediato anterior al contenido que se intenta encontrar.
			} else {
				$this->parents = array();
			}

			if (empty($handler)) {
				$handler[] = '';
			}

			$alias = strtolower(@$handler[$posicion]);

			$sql = "SELECT `" . $this->tabla . "`.* FROM `" . $this->tabla . "`";
			$where = "WHERE 1=1 ";

			/*
				Esto básicamente pregunta si el usuario actual tiene permiso para acceder al contenido que se está buscando.

			if (INTERFACE_TYPE == 'backend') {
				if (!empty($this->usuario) and is_object($this->usuario) and ($this->usuario->existe)) { // Hay un usuario por el cual preguntar?
					if (!in_array($this->usuario->nivel, array('ADM', 'OWN'))) { // No es ni owner ni administrador?.
						$where = " LEFT JOIN " . SQLQuote($this->tabla_permisos) . " ON " . SQLQuote($this->tabla_permisos) . ".`contenido_id` = " . SQLQuote($this->tabla) . ".`id` AND " . SQLQuote($this->tabla_permisos) . ".`usuario_id` = " . $this->usuario->id . " WHERE ((`esta_protegido` = 0) OR (" . SQLQuote($this->tabla_permisos) . ".`id` IS NOT NULL)) "; // Entonces ver si tiene permiso para el contenido actual.
					}
				}
			}
			*/

			$sql .= $where;

			if (!empty($handler[$posicion])) {
				if (!empty($alias)) {
					$sql .= "AND `alias` = '" . $alias . "' ";
				}
			}
			$sql .= "AND `parent_id` = '" . $parent_id . "' AND `estado` = 'HAB' "; // Se busca un registro siempre con el mismo parent_id (o cero).

			if (empty($handler[$posicion])) {
				$sql .= "ORDER BY `es_default` DESC, `id` ASC ";
			}

			$sql .= "LIMIT 1;";

			// EchoLogP($sql);
			//cLogging::Write(__METHOD__.__LINE__." SQL: ".$sql);
			$this->Query($sql);

			if ($this->numrows <= 0) { // No se encontró el registro, por lo tanto cargar el contenido del error HTTP 404.
				$this->GetByController('404');
				return false;
			}

			$this->raw_record = $this->First();
			// ShowVar($this->raw_record);
			$this->ParseRecord();
			$this->SetDatos();

			$posicion++;
			/*
				Acá está el "truco". Esto básicamente...
				Si existe un siguente alias que buscar y ese alias existe como registro en la tabla y tiene como parent_id el id del registro que acabo de leer... (o sea, el contenido que acabo de leer tiene "hijos")
				o bien...
				el registro que acabo de leer es un registro "raiz" de la rama... (esto se indica porque este alias no tiene controlador, así que es solo una raíz de contenidos y no un contenido propiamente.)
			*/
			if (((isset($handler[$posicion])) and ($this->CheckExist($handler[$posicion], $this->id))) or (empty($this->controlador))) {
				$result = $this->GetContent($handler, $posicion, $this->id); // Se llama recursivamente a este mismo método.
			} else {
				/*
				Si lo anterior falla. O sea, no es un registro "raiz" el alias siguente en $handler resulta inexistente...
				Puede ser que esté allí porque no es un alias, sino un parámetro aceptado para el contenido actual...
				Para ello, se comprueba si el contenido actual admite parámetros.
			*/
				// Compruebo la cantidad de parámetros recibidos, si la cantidad es menor o igual a la de los parámetros aceptados los asigno a la propiedad parámetros del objeto contenido, sino hago una redirección a 404.

				$paramsrecived = (count($handler) - $posicion); // Se resta la cantidad de aliases en $handler menos la cantidad de aliases ya recorridos. Eso debe dar la cantidad de parámetros que se usaron en la URL.
				$paramsacepted = $this->parametros_aceptados; // Cuántos parámetros acepta el contenido actual.
				if ($paramsacepted >= $paramsrecived) { // Si la cantidad de parámetros aceptados por el contenido actual es mayor o igual a los parámetros recibidos desde la URL...
					$this->parametros = array_slice($handler, $posicion); // Pasar esos strings como parámetros del contenido actual (o sea, interpretar esos strings como parámetros y no como aliases).
				} else { // Si la cantidad de parámetros aceptados es menor a la cantidad de strings (items) restantes en el array $handler...
					// quiere decir que se intentó acceder a un contenido que no existe. O sea, el contenido actual podría no aceptar ningún parámetro y al mismo tiempo no tener hijos con los aliases pedidos por la URL.
					$this->GetByController('404');
					return false;
				}
			}
		} catch (Exception $e) {
			$this->SetError(__METHOD__, $e->GetMessage());
		}
		return $result;
	} // function Get

	/*
	Esta función establece todas las propiedades del objeto a partir del registro de la tabla.
*/
	private function SetDatos()
	{
		try {
			// Seteo los metadatos (valores del JSON en el campo 'metadata')
			if (!empty($this->metadata)) {
				foreach ($this->metadata as $key => $value) {
					if (!empty($key)) {
						$this->$key = $value;
					}
				}
			}

			// Sobreescribo los datos que son fijos
			$this->parametros_aceptados = $this->parametros;
			$this->cifid = ($this->id ^ $this->refidsalt);
		} catch (Exception $e) {
			$this->SetError(__METHOD__, $e->GetMessage());
		}
		return;
	} // function SetDatos

	/**
	 * Summary. Establece como propiedad los permisos del contenido actual de acuerdo a los permisos del usuario.
	 * @param array $permisos. Un array con los cuatro permisos básicos.
	 */
	public function SetPermisos($permisos)
	{
		if (CanUseArray($permisos)) {
			foreach ($permisos as $key => $permiso) {
				if (in_array($key, ['puede_ver', 'puede_crear', 'puede_modificar', 'puede_borrar'])) {
					$this->$key = ($permiso == 'SI');
				}
			}
		}
	}

	/*
	Esta función comprueba la existencia de un contenido, según su alias y su ancestro.
	La llama GetContent().
*/
	private function CheckExist($alias, $parent_id)
	{
		$result = false;
		try {
			$alias = $this->RealEscape(mb_strtolower($alias));

			$sql = "SELECT * FROM `" . $this->tabla . "`";
			$where = "WHERE 1=1 ";

			if (!empty($this->usuario) and is_object($this->usuario) and ($this->usuario->existe)) { // Hay un usuario por el cual preguntar?
				if (!in_array($this->usuario->nivel, array('ADM', 'OWN'))) { // No es ni owner ni administrador?.
					$where = " LEFT JOIN " . SQLQuote($this->tabla_permisos) . " ON " . SQLQuote($this->tabla_permisos) . ".`contenido_id` = " . SQLQuote($this->tabla) . ".`id` AND " . SQLQuote($this->tabla_permisos) . ".`usuario_id` = " . $this->usuario->id . " WHERE ((`esta_protegido` = 0) OR (" . SQLQuote($this->tabla_permisos) . ".`id` IS NOT NULL)) "; // Entonces ver si tiene permiso para el contenido actual.
				}
			}

			$sql .= $where;

			$sql .= "AND `alias` = '" . $alias . "' ";

			$sql .= "AND `parent_id` = '" . $parent_id . "' LIMIT 1;"; // Siempre limitarse a los registros con el mismo parent_id, o sea, siempre recorrer la misma "rama".
			// EchoLogP($sql);
			$this->Query($sql);
			$result = ($this->numrows > 0);
		} catch (Exception $e) {
			$this->SetError(__METHOD__, $e->GetMessage());
		}
		return $result;
	} // function CheckExist

	/*
	Esta función guarda los datos del ancestro de un contenido.
*/
	private function GuardarParent($key)
	{
		$this->parents[$key] = $this->raw_record;
		if (!empty($this->raw_record['metadata'])) {
			$this->parents[$key]['metadata'] = json_decode($this->raw_record['metadata']);
		} else {
			$this->parents[$key]['metadata'] = new StdClass;
		}
		/*
		$this->parents[$key]['id'] = $this->id;
		$this->parents[$key]['controlador'] = $this->controlador;

		foreach ($this->campos_parent_guardar as $k => $value) {
			$this->parents[$key][$value] = @$this->$value;
		}
		*/
	} // function GuardarParent
	/*
	Obtiene conenidos del sistema a partir de su controlador
*/
	public function GetByController($controller)
	{
		$result = true;
		try {
			$sql = "SELECT `alias` FROM `" . $this->tabla . "`";

			$where = "WHERE 1=1 ";

			if (!empty($this->usuario) and is_object($this->usuario) and ($this->usuario->existe)) { // Hay un usuario por el cual preguntar?
				if (!in_array($this->usuario->nivel, array('ADM', 'OWN'))) { // No es ni owner ni administrador?.
					$where = " LEFT JOIN " . SQLQuote($this->tabla_permisos) . " ON " . SQLQuote($this->tabla_permisos) . ".`contenido_id` = " . SQLQuote($this->tabla) . ".`id` AND " . SQLQuote($this->tabla_permisos) . ".`usuario_id` = " . $this->usuario->id . " WHERE ((`esta_protegido` = 0) OR (" . SQLQuote($this->tabla_permisos) . ".`id` IS NOT NULL)) "; // Entonces ver si tiene permiso para el contenido actual.
				}
			}

			$sql .= $where;

			$sql .= " AND `controlador` = '" . $controller . "'";
			$this->Query($sql);
			if ($this->error) {
				throw new Exception(__LINE__ . " DBErr: " . $this->errmsg);
			}

			if ($this->numrows == 0) {
				if ($controller == '404') {
					throw new Exception(__LINE__ . " El registro con el contenido para el 404 no ha sido encontrado.");
				} else {
					$this->GetByController('404');
				}
			}

			$fila = $this->First();
			$alias = $fila['alias'];

			$result = $this->GetContent($alias);
		} catch (Exception $e) {
			$result = false;
			$this->SetError(__METHOD__, $e->GetMessage());
		}
		return $result;
	} // function GetByController

	/*
	Devuleve el título de la página para ser usado en la etiqueta <title> del documento HTML.
*/
	public function GetTitulo($withmain = true)
	{

		$result = null;
		if (!empty($this->metatitle)) {
			$result = $this->metatitle;
		} else {
			if (!empty($this->nombre)) {
				$result = $this->nombre;
			}
		}
		if (!empty(MAINTITLE) and $withmain) {
			if (!empty($result)) {
				$result = $result . " :: " . MAINTITLE;
			} else {
				$result = MAINTITLE;
			}
		}
		return $result;
	} // GetTitulo
	/*
	Devuelve el título del contenido.
*/
	public function GetH1($agregar = null)
	{
		$result = null;
		if (!empty($this->titulo)) {
			$result = $this->titulo;
		} else {
			if (!empty($this->metatitulo)) {
				$result = $this->metatitulo;
			} else {
				if (!empty($this->h1)) {
					$result = $this->h1;
				} else {
					$result = $this->nombre;
				}
			}
		}
		return $result . $agregar;
	} // GetH1
/*
	Toma un contenido por el ID usando el método del ancestro.
*/
	public function Get($id) {
		$result = false;
		try {
			if (SecureInt($id) == null) { throw new Exception(__LINE__." ID debe ser un número no vacío."); }
			$this->sql = "SELECT * FROM ".SQLQuote($this->tabla)." WHERE `id` = ".$id." ";
			$this->forceParse = true;
			if (parent::Get($id)) {
				$this->ParseRecord();
				$result = true;
			}
		}catch(Excption $e) {
			$this->SetError(__METHOD__, $e->GetMessage());
		}
		return $result;
	}
/*
	Devuelve el registro de un contenido. Este es un método fuera de banda.
*/
	public function GetById($id)
	{
		$result = false;
		if ($id > 0) {
			try {
				$sql = "SELECT * FROM " . SQLQuote($this->tabla) . " ";
				$sql .= "WHERE `id` = " . $id . " AND `estado` = 'HAB' ";
				//EchoLog($sql);
				$this->Query($sql);
				if ($this->error) {
					throw new Exception(__LINE__ . " DBErr: " . $this->errmsg);
				}
				if ($fila = $this->First()) {
					$result = $fila;
				}
			} catch (Exception $e) {
				$result = false;
				$this->SetError(__METHOD__, $e->GetMessage());
			}
		}
		return $result;
	} // GetById
	/*
	Arma la URL completa hacia el contenido actual.
	$absolute: indica si la URL incluye el protocolo y el dominio.
*/
	public function GetLink($absolute = true)
	{
		$result = BASE_URL;
		$p = array();
		$alias = null;
		if (is_bool($absolute)) {
			if ($absolute == false) {
				$result = '';
			}
			if (CanUseArray($this->parents)) {
				$p = $this->parents;
			}
			$alias = $this->alias;
		} else {
			if (is_numeric($absolute)) {
				if ($absolute > 0) {
					$id = $absolute;
					while ($aux = $this->GetById($id)) {
						$p[] = $aux;
						$id = $aux['parent_id'];
					}
					$p = array_reverse($p);
				} else {
					if (CanUseArray($this->parents)) {
						for ($i = 0; $i <= (count($this->parents) + $absolute); $i++) {
							$p[]['alias']  = $this->parents[$i]['alias'];
						}
					}
				}
			} else {
				return false;
			}
		}
		if (CanUseArray($p)) {
			$b = array();
			foreach ($p as $a) {
				$b[] = $a['alias'];
			}
			$b[] = $alias;
			$result .= implode('/', $b);
		} else {
			$result .= $alias;
		}
		return $result;
	} // function GetLink

	/*
	Devuelve un array con los registros que tienen el mismo ancestro, es decir, todos los registros cuyo parent_id es igual a $id.
	Si se indica el parámetro $todo en TRUE, se devuelven todos los campos de la tabla, caso contrario solo se devuelve el id, nombre y alias.
	$en_menu indica devolver solo los regitros marcados como en_menu = 1;
*/
	public function GetSiblings($id = null, $todo = false, $en_menu = true)
	{
		$result = NULL;
		try {

			$id = (($id === null) ? $this->parent_id : $id);

			$sql = "SELECT ";
			$sql .= ($todo === true) ? '*' : "`id`, `nombre`, `alias` ";
			$sql .= "FROM `" . $this->tabla . "` ";

			$where = "WHERE 1=1 ";
			if (!empty($this->usuario) and is_object($this->usuario) and ($this->usuario->existe)) { // Hay un usuario por el cual preguntar?
				if (!in_array($this->usuario->nivel, array('ADM', 'OWN'))) { // No es ni owner ni administrador?.
					$where = " LEFT JOIN " . SQLQuote($this->tabla_permisos) . " ON " . SQLQuote($this->tabla_permisos) . ".`contenido_id` = " . SQLQuote($this->tabla) . ".`id` AND " . SQLQuote($this->tabla_permisos) . ".`usuario_id` = " . $this->usuario->id . " WHERE ((`esta_protegido` = 0) OR (" . SQLQuote($this->tabla_permisos) . ".`id` IS NOT NULL)) "; // Entonces ver si tiene permiso para el contenido actual.
				}
			}
			$sql .= $where;

			$sql .= "AND `estado` = 'HAB' AND `parent_id` = " . $id . " ";
			if ($en_menu) {
				$sql .= "AND `en_menu` = 1 ";
			}
			// EchoLog($sql);
			$result = $this->getArray($sql);
			if ($this->error) {
				throw new Exception(__LINE__ . " DBErr: " . $this->errmsg);
			}
			if (CanUseArray($result)) {
				foreach ($result as $key => $value) {
					if (!empty($value['metadata'])) {
						$result[$key]['metadata'] = json_decode($value['metadata']);
					}
				}
			}
		} catch (Exception $e) {
			$result = false;
			$this->SetError(__METHOD__, $e->GetMessage());
		}
		return $result;
	} // GetSiblings
/**
* Summary. Devuelve un registro y nada más.
* @param int $id El id del contenido.
* @return array $result El registro o null en caso de no encontrarlo.
*/
	public function GetRaw($id = null) {
		$result = null;
		try {
			$this->forceParse = true;
			if (SecureInt($id)) { 
				$sql = "SELECT * FROM ".SQLQuote($this->tabla)." WHERE `id` = ".$id." ";
				if ($fila = $this->FirstQuery($sql)) {
					$result = $fila;
				}
			 }
		} catch (Exception $e) {
			$result = false;
			$this->SetError(__METHOD__, $e->GetMessage());
		}
		return $result;
	}
/**
* Summary. Devuelve la lista de ancestro del contenido según el id.
* @param int $id El id del contenido del que se quiere saber sus ancentros.
* @return array $result La lista de ancestros hasta la raíz.
*/
	public function GetParents($id = null) {
		$result = array();
		try {
			if (is_null($id)) { throw new Exception(__LINE__." No se indicó un ID."); }
			$sql = "SELECT * FROM ".SQLQuote($this->tabla)." WHERE `id` = ".$id." ";
			if ($fila = $this->FirstQuery($sql)) {
				while (!empty($fila['parent_id'])) {
					$this->forceParse = true;
					$sql = "SELECT * FROM ".SQLQuote($this->tabla)." WHERE `id` = ".$fila['parent_id']." ";
					if ($fila = $this->FirstQuery($sql)) {
						$fila['metadata'] = json_decode($fila['metadata']);
						$result[] = $fila;
					}
				}
				if (CanUseArray($result)) {
					$result = array_reverse($result);
				}
			}
		} catch (Exception $e) {
			$result = false;
			$this->SetError(__METHOD__, $e->GetMessage());
		}
		return $result;
	}
	/*
	Devuelve un array con los elementos del menú.
	La estructura devuelta es:
		array(
			'id' => int,
			'alias' => varchar,
			'nombre' => varchar,
			'class' => (active | parent_active | ancestor_active),
			'url' => varchar,
			'childs' => array() // con el mismo formato
		)
	@parent indica los hijos a partir de ese elemento.
	@protegidos devolvera solo los elementos en los que `protegido` = 1.
*/
	public function GetMenuItems($parent = 0, $protegidos = false, $escondidos = false)
	{
		$result = array();
		try {
			$sql = "SELECT `" . $this->tabla . "`.`id`, `alias`, `nombre`, `metadata`, `es_default` FROM `" . $this->tabla . "` ";

			$where = "WHERE 1=1 ";
			if (!empty($this->usuario) and is_object($this->usuario) and ($this->usuario->existe)) { // Hay un usuario por el cual preguntar?
				if (!in_array($this->usuario->nivel, array('ADM', 'OWN'))) { // No es ni owner ni administrador?.
					$where = " LEFT JOIN " . SQLQuote($this->tabla_permisos) . " ON " . SQLQuote($this->tabla_permisos) . ".`contenido_id` = " . SQLQuote($this->tabla) . ".`id` AND " . SQLQuote($this->tabla_permisos) . ".`usuario_id` = " . $this->usuario->id . "AND " . SQLQuote($this->tabla_permisos) . ".`puede_ver` = 'SI'  WHERE ((`esta_protegido` = 0) OR (" . SQLQuote($this->tabla_permisos) . ".`id` IS NOT NULL)) "; // Entonces ver si tiene permiso para el contenido actual.
				}
			}
			$sql .= $where;

			$sql .= "AND `estado` = 'HAB' AND `parent_id` = " . $parent . " ";

			if (!$escondidos) {
				$sql .= "AND `en_menu` = 1 ";
			}

			if ($protegidos) {
				$sql .= "AND `esta_protegido` = 1 ";
			}

			$sql .= "ORDER BY `orden` ASC;";

			// echolog($sql);
			$res = $this->Query($sql);
			if ($this->error) {
				throw new Exception(__LINE__ . " DBErr: " . $this->errmsg);
			}

			while ($fila = $this->Next($res)) {
				$alias = $fila['alias'];
				$nombre = $fila['nombre'];
				$es_default = $fila['es_default'];
				$metadata = json_decode($fila['metadata']);

				// Le ponemos la clase si esta activo o alguno de sus hijos
				$class = '';
				if ($this->id == $fila['id']) {
					$class = 'active';
				} else {
					if (!empty($this->parents)) {
						$ultimo = end($this->parents);
						if ($ultimo['id'] == $fila['id']) {
							$class = 'parent_active';
						} else {
							foreach ($this->parents as $ancestro) {
								if ($ancestro['id'] == $fila['id']) {
									$class = 'ancestor_active';
								}
							}
						}
					}
				}
				$url = $this->GetLink($fila['id']);
				$result[] = array(
					'id' => $fila['id'],
					'alias' => $alias,
					'nombre' => $nombre,
					'metadata' => $metadata,
					'es_default' => $es_default,
					'class' => $class,
					'url' => $url
				);
			} // while
			if (count($result) > 0 and $this->withchilds) {
				foreach ($result as $key => $value) {
					$result[$key]['childs'] = $this->GetMenuItems($value['id'], $protegidos, $escondidos);
				}
			}
		} catch (Exception $e) {
			$this->SetError(__METHOD__, $e->GetMessage());
		}
		return $result;
	} // function GetMenuItems

	/*
	Esta función agrega elementos a la cola de CSS y JS.
*/
	public function AgregarAssets($cadena = '', $tipo = null)
	{
		try {
			if (!in_array($tipo, array('css', 'js'))) {
				throw new Exception("El tipo de asset solicitado no es válido.");
			}
			if (empty($cadena)) {
				throw new Exception("El asset a agregar no puede estar vacío.");
			}

			// Corregimos si es un array
			if (is_array($cadena)) {
				$cadena = implode(',', $cadena);
			}

			// Agrego el asset
			$this->$tipo = (empty($this->$tipo)) ? $cadena : $this->$tipo . ',' . $cadena;
		} catch (Exception $e) {
			$result = false;
			$this->SetError(__METHOD__, $e->GetMessage());
		}
	} // function AgregarAssets

	/*
	Agrega o devuleve contenido adicional para agregar al final de <head>
*/
	public function ExtraHeadContent($data = null)
	{
		if ($data === null) {
			return @$this->extrahead;
		}

		if (is_array($data)) {
			$data = implode("\n", $data);
		}

		$this->extrahead = (empty($this->extrahead)) ? $data : $this->extrahead . "\n" . $data;
	} // function ExtraHeadContent


	/*
	Devuelve un array donde cada elemento es un archivo CSS listado en la propiedad css del metadata.
*/
	public function CssList()
	{
		$result = array();
		if (!empty($this->metadata->css)) {
			$aux = explode(",", $this->metadata->css);
			if (count($aux) > 0) {
				foreach ($aux as $key => $value) {
					if (preg_match("/\.css$/im", $value)) {
						$aux[$key] = preg_replace("/\.css$/im", "", $value);
					}
				}
				$result = $aux;
			}
		}
		return $result;
	}
	/*
	Devuelve un array donde cada elemento es un archivo JS listado en la propiedad js del metadata.
*/
	public function JsList()
	{
		$result = array();
		if (!empty($this->metadata->js)) {
			$aux = explode(",", $this->metadata->js);
			if (count($aux) > 0) {
				foreach ($aux as $key => $value) {
					if (preg_match("/\.js$/im", $value)) {
						$aux[$key] = preg_replace("/\.js$/im", "", $value);
					}
				}
				$result = $aux;
			}
		}
		return $result;
	}

	/*
	Devuelve la lista de imágenes para el slider principal.
*/
	public function GetImagenes($id_contenido = NULL)
	{
		$result = array();
		$self = false;
		if ($id_contenido == NULL) {
			$id_contenido = $this->id;
			$self = true;
		}
		$hoy = Date('Y-m-d');
		$sql = "SELECT * FROM `" . $this->tabla_sliders . "` WHERE `id_contenido` = " . $id_contenido . " AND `estado` = 'HAB' AND (((DATE(`desde`) <= '" . $hoy . "') OR (`desde` IS NULL)) AND ((DATE(`hasta`) >= '" . $hoy . "') OR (`hasta` IS NULL)) ) ORDER BY `orden` ASC, `fechahora` DESC;";
		try {

			$this->Query($sql);
			if ($this->error) {
				throw new Exception(__LINE__ . " DBErr: " . $this->errmsg);
			}
			if ($fila = $this->First()) {
				$this->imagenes = array();
				do {
					$result[] = $fila;
				} while ($fila = $this->Next());
				if ($self) {
					$this->imagenes = $result;
				}
			}
		} catch (Exception $e) {
			$this->SetError(__METHOD__, $e->GetMessage());
		}
		return $result;
	}
	/**
	 * Summary. Obtiene el contenido apuntado por el cifid que es el ID del contenido cifrado. Establece las propiedades del objeto.
	 * @param int $cifid. El ID del contenido cifrado.
	 * @return bool/object. Si el contenido no fue encontrado, regresa false, sino un objeto representando el registro leído.
	 */
	public function GetByCifid($cifid)
	{
		$result = false;
		try {
			$id = ($this->refidsalt ^ $cifid);
			$sql = "SELECT * FROM " . SQLQuote($this->tabla) . " WHERE `id` = " . $id;
			if ($this->raw_record = $this->FirstQuery($sql)) {
				$this->ParseRecord();
				$this->parametros = array();
				$result = json_decode(json_encode($this->raw_record));
			}
		} catch (Exception $e) {
			$this->SetError(__METHOD__, $e->GetMessage());
		}
		return $result;
	}

	/**
	 * Summary. Obtiene la url del primer submódulo de un submenú, para asignársela a su padre luego.
	 * @param arr $childs. El array con los children... 
	 * @return bool/object. Si el contenido no fue encontrado, regresa false, sino un objeto representando el registro leído.
	 */
	public function GetFirstChildUrl($children, $objeto_usuario)
	{
		$result = false;
		if (CanUseArray($children) and (count(@$children) > 1)) {
			$child_id = 9999999;
			foreach ($children as $child) {
				if ($objeto_usuario->tienePermiso($child['id'], 'VER') and $child['id'] < $child_id) {
					$result = $child['url'];
				}
			}
		};
		return $result;
	}

/**
* Summary. Actualiza el registro para el contenido actual.
* @param array $data. Los campos y sus valores.
* @return bool.
*/
	public function Save($data) {
		$result = false;
		try {
			if (!$this->existe) { throw new Exception(__LINE__." No hay un contenido activo."); }
			$reg = array();
			$fields = $this->GetColumnsNames($this->tabla);
			foreach($data as $key => $value) {
				if (in_array($key, $fields)) {
					$reg[$key] = $this->RealEscape($value);
				} else {
					$this->SetError(__METHOD__," Campo '".$key."' ignorado.");
				}
			}
			$reg['last_modif'] = Date('Y-m-d H:i:s');
			$this->Update($this->tabla, $reg, "`id` = ".$this->id);
			$result = $this->Get($this->id);
		} catch (Exception $e) {
			$this->SetError(__METHOD__, $e->GetMessage());
		}
		return $result;
	}
	/************************************/
	/*			FIN DE LA CLASE			*/
	/************************************/
} // Fin de la clase cContenido
