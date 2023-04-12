<?php
/*
	Clase para manejar las APIs estáticas.
	
	Created: 2021-08-20
	Author: DriverOp
	
*/

if (!defined("DIR_BASE_CUSTOM")) {
	define("DIR_BASE_CUSTOM",".".DS);
}

const _apiAuth = array(
	"methods"=>"GET,POST",
	"route"=>"auth/{username}?/{password}?",
	"resolver"=>"checkLogin",
	"restricted"=>false
);

require_once("class.wsv2UriParser.inc.php");

class cApis {
	
	public $scandir = DIR_BASE_CUSTOM.'api';
	public $scanfiles = '*.json';
	public $filelist = [];
	public $apis = [];
	
	public $trimDirBase = false;
	private $target_contents = []; // Esto es para almacenar los resolvers candidatos
	private $URIParser = null; // Placeholder para el objeto que parsea la URL.
	
/**
* Summary. Constructor de la clase. Rastrea los archivos .json, arma la lista de archivos y el array en memoria con los routes disponibles.
*/
	public function __construct($dir = DIR_BASE_CUSTOM.'api') {
		$this->scandir = $dir;
		$this->RefreshList();
		$this->CollectJSON();
		$this->trimDirBase = true;
	}

/**
* Summary. Lee/relee el sistema de archivos buscando los .json para armar la lista de APIs.
*/
	public function RefreshList() {
		$this->filelist = $this->scanForJson($this->scandir.DS.$this->scanfiles);
		if ($this->trimDirBase) {
			$this->filelist = array_map(function ($item) {
				if (strpos($item, DIR_BASE) !== false) {
					$item = substr_replace($item, '', strpos($item, DIR_BASE), strlen(DIR_BASE));
				}
				return $item;
			}, $this->filelist);
		}
		return $this->filelist;
	}


/**
* Summary. Devuelve los routes que tienen el método indicado.
* @param string $method El método a ser filtrado de la lista de routes.
* @return array/null
*/
	public function GetRoutes(string $method = 'GET'):?array {
		$result = [];
		$this->target_contents = [];
		$method = strtoupper($method);
		if (count($this->apis) > 0) {
			$this->URIParser = new cURIParser;
			foreach($this->apis as $key => $item) {
				$item->methods = strtoupper($item->methods);
				if (!preg_match("~(^)?(,|\s)?".$method."(,|\s|($))+~is", $item->methods)) { continue; } // Descartar las APIs que no son $methods
				$result[] = $item;
				$this->target_contents[$item->route] = $this->URIParser->Parse($item->route);
				$this->target_contents[$item->route]['idx'] = $key;
			}
			$this->URIParser = null;
		}
		return $result;
	}

/**
* Summary. Compara la URI contra la lista de APIs aceptadas devolviendo los parámetros encontrados y el contenido. Se detiene en la primera coincidencia.	
* @param string $url La URI tomada por el core.
* @return array Los datos del resolver.
*/
	public function ParseURL(string $url):?array {
		$result = null;
		$matches = [];
		$url = rtrim(trim($url),'/'); // Eliminar la / del final de la URL para que no cause problemas al matchear con los patrones.
		foreach($this->target_contents as $key => $content) {
			//EchoLog($key." => ".print_r($content));
			if (preg_match('~^'.$content['result'].'~i', $url, $matches)) {
				array_shift($matches); // El primer resultado de matches no nos importa.
				$matches = array_map(function ($value) { return ltrim(trim($value),'/'); }, $matches); // Eliminar la / al inicio de los valores de los parámetros, si está ahí.
				$result = array(
					'resolver'=>$this->apis[$content['idx']]->resolver,
					'restricted'=>$this->apis[$content['idx']]->restricted??true
				);
				foreach($content['parameters'] as $k => $v) { // Lista de los nombres de parámetros y sus valores según la URI pasada como parámetro del método.
					$result['parameters'][$v] = $matches[$k]??null;
				}
				break;
			}
		}
		return $result;
	}
/**
* Summary. Extraer los parámetros de la query string.
* @param string $url La URL a tratar.
* @return array Con la lista de parámetros nombre=>valor.
* @note Esto MODIFICA $url haciendo que se quite el query string de la URL.
*/
	private function BreakURL(string &$url = ''):array {
		$result = array();
		$aux = explode('?',$url);
		$uri = $aux[0];
		$querystring = $aux[1]??null;
		if (!empty($querystring)) {
			parse_str($querystring, $result);
		}
		return $result;
	}
/**
* Summary. Con los archivos rastreados arma la lista de routes dejándola lista para ser usada.
*/
	private function CollectJSON() {
		$this->apis[] = (object)_apiAuth;
		if (count($this->filelist) < 1) { return; }
		foreach($this->filelist as $file) {
			if (!is_file($file) or !is_readable($file)) { continue; }
			$json = json_decode(file_get_contents($file));
			if (json_last_error() !== JSON_ERROR_NONE) { throw new Exception("Documento JSON no es correcto: ".$file." se encontró error: ".json_last_error()); }
			if (!is_array($json)) { trigger_error("No es un array."); continue; }
			foreach($json as $idx => $item) {
				if (!is_object($item)) { trigger_error("No es un objeto."); continue; }
				
				// Propiedades obligatorias.
				if (!property_exists($item,'methods')) { trigger_error("Item ".$idx." en ".$this->TrimDirBase($file)." no tiene 'methods'"); continue; }
				if (!property_exists($item,'route')) { trigger_error("Item ".$idx." en ".$this->TrimDirBase($file)." no tiene 'route'"); continue; }
				if (!property_exists($item,'resolver')) { trigger_error("Item ".$idx." en ".$this->TrimDirBase($file)." no tiene 'resolver'"); continue; }
				
				
				// Propiedades opcionales.
				if (!isset($item->restricted)) { $item->restricted = true; }
				$this->apis[] = $item;
			} // foreach
		} // foreach
	} // CollectJSON
	
/**
* Summary. Recursivamente rastrea una rama del sistema de archivos buscando todos los archivos que cumplen el patrón.
* @param string $pattern El patrón del nombre de archivo buscado.
* @param int $flags Las banderas de opciones.
* @return array.
*/
	private function scanForJson($pattern, $flags = 0) {
		$files = glob($pattern, $flags);
		foreach (glob(dirname($pattern).DS.'*', GLOB_ONLYDIR|GLOB_NOSORT) as $dir) {
			$files = array_merge($files, $this->scanForJson($dir.DS.basename($pattern), $flags));
		}
		return $files;
	}

/**
* Summary. Elimina DIR_BASE de la candena.
* @param string $item La cadena objetivo.
* @return string.
*/
	private function TrimDirBase($item) {
		if (strpos($item, DIR_BASE) !== false) {
			$item = substr_replace($item, '', strpos($item, DIR_BASE), strlen(DIR_BASE));
		}
		return $item;
	}
	
}