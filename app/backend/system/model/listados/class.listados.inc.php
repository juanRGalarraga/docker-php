<?php
/*
	Clase fundacional para derivar todos los listados que piden cosas al core.
	Created: 2021-10-26
	Author: DriverOp
*/

    require_once(DIR_wsclient."class.wsv2Client.inc.php");

class cListados extends cWsV2Client{
	
	public $listado = null;
	public $header = null;
	public $listid = null;

// Propiedades del paginador.
	public $ItemsPorPagina = 25; // cuántos registros hay en la consulta.
	public $PaginaActual = 1; // el índice de la página actual.
	public $Rango = 3; // cuántos items hay que mostrar en cada página.
	public $CantExtremos = 5; // cuántas páginas mostrar al inicio y al final.
	public $ItemsTotales = 0; // Cantidad de registros totales en la consulta.
	public $ItemsActuales = 0; // La cantidad de registros actualmente mostrados en la página actual.
	public $Paginador = []; // Array con los datos del paginador.

	
    function __construct() {
		parent::__construct();
		$this->listado = new stdClass;
		 $this->header = new stdClass;
	}

/**
* Summary. Sobrecarga del método del ancestro.
* @return array El listado enviado por el core.
* @note El único HTTP request method que devuelve listados es GET.
*/
	public function GetQuery($url = null, $datos = null) {
		$this->Paginador = [];
		$this->listid = null;
		
		$log_max_length = $this->log_max_length;
		$this->log_max_length = 1024; // Los listados pueden ser MUY grandes y no hace falta guardar todo el resultado. Solo sabe que está ahí.
		
		parent::GetQuery($url, $datos);
		
		$this->log_max_length = $log_max_length;
		
		if ($this->CheckForErrors()) { return null; } // Si hubo un error, devolver vacío.
		
		$this->listado = $this->theData->list;
		$this->header = $this->theData->header;
		if (($this->theData->header->listid)) {
			$this->listid = $this->theData->header->listid;
		}
		$this->Paginador();
		$this->Ordenamiento();
		return $this->listado;
	}

/**
* Summary. Ejecuta GetQuery del ancestro transparentemente. Es decir, no asume que se quiere cargar un listado como lo hace GetQuery de esta misma clase.
*/
	public function RawGetQuery($url = null, $datos = null) {
		$result = parent::GetQuery($url, $datos);
		if ($this->http_nroerr >= 400) { $result = null; } // Si hubo un error, devolver vacío.
		return $result;
	}
/**
* Summary. Determina si la respuesta del core es un error u excepción que impida el funcionamiento normal del listado.
*/
	private function CheckForErrors() {
		if ($this->http_nroerr >= 400) { $this->SetLog(__FILE__ ." Condición de excepción $this->http_nroerr enviada por el core.", LGEV_ERROR); return true; }
		if (!isset($this->theData)) { $this->SetLog(__FILE__ ." El core no devolvió 'data'.", LGEV_ERROR); return true; }
		if (!is_object($this->theData)) { $this->SetLog(__FILE__ ." 'data' no es objeto.", LGEV_ERROR); return true; }
		if (!isset($this->theData->list)) { $this->SetLog(__FILE__ ." El core no devolvió 'list'.", LGEV_ERROR); return true; }
		if (!isset($this->theData->header)) { $this->SetLog(__FILE__ ." El core no devolvió 'header'.", LGEV_ERROR); return true; }
		return false;
	}


/**
* Summary. Rellena el array del paginador.
* @return array
* @note La salida es un array donde cada elemento representa un item del paginador. Cada item contiene el número de página "p", una bandera que indica si es la página que se está mostrando "act", y, alternativamente si se trata de una flecha "arr" atrás "prev" o adelante "next". Ej;
[
	["pag"=>2,"act"=>false",arr"=>"prev"], // Apunta a la página 2 porque es la anterior a la 3.
	["pag"=>2,"act"=>false"],
	["pag"=>3,"act"=>true"], // Página actual.
	["pag"=>4,"act"=>false"],
	...
	["pag"=>4,"act"=>false,"arr"=>"next"]  // Apunta a la página 4 porque es la siguiente de la 3.
]
*/
	function Paginador() {
		if (!isset($this->header)) { return; }
		if (!isset($this->header->cant)) { return; }
		if ($this->header->cant < 1) { return; }
		$this->Paginador = [];
		$this->ItemsTotales = intval($this->header->cant);

		if (isset($this->header->rpp)) { 
			$this->ItemsPorPagina = intval($this->header->rpp);
		}
		if (isset($this->header->pag)) { 
			$this->PaginaActual = intval($this->header->pag);
		}
		if (isset($this->header->items)) { 
			$this->ItemsActuales = intval($this->header->items);
		}

		$MedioRango = $this->Rango;
		$TotalDePagina = ceil($this->ItemsTotales / $this->ItemsPorPagina);
		$ContPuntos = false;
		for ($p = 1; $p <= $TotalDePagina; $p++) {
			// do we display a link for this page or not?
			if ($p == 1 and ($TotalDePagina > 1)) {
				if (($this->PaginaActual-1) > 1) {
					$this->Paginador[] = array("p"=>$this->PaginaActual-1,"act"=>false,"arr"=>"prev");
				}
			}
			if (($p <= $this->CantExtremos) or ($p > ($TotalDePagina - $this->CantExtremos)) or
				(($p >= $this->PaginaActual - $MedioRango) and ($p <= $this->PaginaActual + $MedioRango)) or
				($p == $this->CantExtremos + 1 and $p == $this->PaginaActual - $MedioRango - 1) or
				($p == $TotalDePagina - $this->CantExtremos and $p == $this->PaginaActual + $MedioRango + 1 )
				) {
				$ContPuntos = false;
				if ($p == $this->PaginaActual) {
						$this->Paginador[] = array("p"=>$p, "act"=>true);
				} else {
					$this->Paginador[] = array("p"=>$p, "act"=>false);
				}
			// if not, have we already shown the elipses? 
			} elseif ($ContPuntos == false) { 
				$this->Paginador[] = array("p"=>null, "act"=>false);
				$ContPuntos=true; // make sure we only show it once
			}
			if ($p == $TotalDePagina and ($TotalDePagina > 1)) {
				if (($this->PaginaActual+1) < $TotalDePagina) {
					$this->Paginador[] = array("p"=>$this->PaginaActual+1,"act"=>false,"arr"=>"next");
				}
			}
		} // for
		return $this->Paginador;
	} // Paginador

	private function Ordenamiento(){
		if (!isset($this->header)) { return; }
		if(!isset($this->header->listid)){ return; }
		if(!isset($this->header->fields)){ return; }
		if (!isset($this->header->currentOrd)) { return; }
		if (empty($this->header->currentOrd)) { return; }
		$lid = $this->header->listid;
		$_SESSION[$lid] = array();
		$_SESSION[$lid]['orden'] = (array)$this->header->currentOrd;
		$_SESSION[$lid]['fields'] = (array)$this->header->fields;
	}

	/**
	 * Summary. Obtiene el orden actual dado el ID de un listado 
	 * @param string $lid El ID del listado
	 * @param int $num El número identificador del campo con el cual se ordenara en esta petición
	 * @return null|array Nulo en caso de que no se encontrase ordenamiento para ID del listado dado, un array con el orden actual en caso contrario
	 */
	public function SetOrden(string $lid,int $num):?array{
		if(!isset($_SESSION[$lid]['fields'])){ return null; }
		if(!isset($_SESSION[$lid]['orden'])){ return null; }
		$campos = $_SESSION[$lid]['fields'];
		$ordenamiento = $_SESSION[$lid]['orden'];
		$key = $campos[$num] ?? null;
		if(is_null($key)){ return null; }

		$result = array();
		$result[$key] = "ASC";
		if(isset($ordenamiento[$key])){
			$result[$key] = ($ordenamiento[$key] == 'DESC')? "ASC":"DESC";
		}
		return $result;
	}
} // Class