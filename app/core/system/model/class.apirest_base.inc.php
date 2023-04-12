<?php
/*
	Clase fundacional para todas las clases que implementan conexió a una API externa.
	Establece los métodos comunes a todas las clases que necesitan comunicarse al exterior.
	
	Created: 2020-03-04
	Author: DriverOp.

*/

if (!defined('HTTP_CODES')) {
	require_once(DIR_includes."wsclient.msgs.php"); // Aquí están los mensajes de error.
}

if (!defined("API_referer")) {
	define("API_referer",BASE_URL);
}

class cAPIRestBase {
	
	public $msg = null;
	public $parsed_response = array();
	public $data;
	public $response = '';
	public $errores = '';
	public $intl_nroerr = 0;
	public $curl_nroerr = 0;
	public $http_nroerr = 0;
	public $json_nroerr = 0;
	public $api_nroerr = 0;
	public $debug_level = 2;
	public $echo_debug = false;
	public $logdir = DIR_logging;
	public $userAgent = 'Rebrit cURL Client 1.0';
	public $baseURL = 'http://localhost/';
	public $final_url = '';
	private $internal_errors = null;
	private $curl_errors = null;
	private $http_codes = null;
	private $json_errors = null;
	public $log_file_name = 'apirest.log';

	function __construct() {
		global $internal_errors;
		global $curl_errors;
		global $http_codes;
		global $json_errors;
		$this->internal_errors = $internal_errors;
		$this->curl_errors = $curl_errors;
		$this->http_codes = $http_codes;
		$this->json_errors = $json_errors;
	}
	
	public function SetLog($linea) {
		if ($this->echo_debug) {
			echo $linea.FDL;
		}
		umask(0);
	
		$mes = Date('Y-m');
		$dia = Date('Y-m-d');
		
		$dir = $this->logdir.$mes.DIRECTORY_SEPARATOR;

		$archivo = $dir.$dia.'-'.$this->log_file_name;
		if (!file_exists($this->logdir)) {
			mkdir($this->logdir,0777);
		}
	
		if (!file_exists($dir)) {
			mkdir($dir,0777);
		}
		
		$linea = '['.Date('Y-m-d H:i:s').'] '.$linea.PHP_EOL;
		return file_put_contents($archivo, $linea, FILE_APPEND);
	}
/*
	Establece el error interno propio de esta clase.
*/
	protected function SetINTERNALError($nro = 0) {
		
		$this->intl_nroerr = $nro;
		if (isset($this->internal_errors[$nro])) {
			$this->errores = $this->internal_errors[$nro];
		}
		$this->msg = "Internal Error: ".$this->intl_nroerr." ".$this->errores;
		if ($this->debug_level > 0) {
			$this->SetLog(__LINE__." ".__METHOD__." ".$this->msg);
		}
		return $this->msg;
	} // SetINTERNALError
/*
	Interpreta el error devuelto por cURL.
*/
	protected function SetCURLError($link) {
		global $curl_errors;
		$result = false; // Asumir ningún error.
		$this->errores = '';
		$this->curl_nroerr = curl_errno($link);
		if ($this->curl_nroerr != 0) { // Hubo un error?
			$result = true; // Sí!
			$this->errores = $this->curl_nroerr." ".@$curl_errors[$this->curl_nroerr];
			$this->msg = "cRUL Error: ".$this->errores;
			if ($this->debug_level > 0) {
				$this->SetLog(__LINE__." ".__METHOD__." ".$this->msg);
			}
		}
		return $result;
	} // SetCURLError
/*
	Interpretar código de Status HTTP como un error... o no.
*/	
	protected function SetHTTPError($link) {
		$this->msg = null;
		$this->errores = NULL;
		$this->http_nroerr = curl_getinfo($link,CURLINFO_HTTP_CODE);
		if (isset($this->http_codes[$this->http_nroerr])) {
			$this->errores = $this->http_codes[$this->http_nroerr];
		} else {
			$this->errores = 'HTTP code: '.$this->http_nroerr;
		}
		$this->msg = "HTTP Status: ".$this->http_nroerr." ".$this->errores;
		if ($this->debug_level > 0) { 
			$this->SetLog(__LINE__." ".__METHOD__." ".$this->msg);
		}
		return ($this->http_nroerr >= 400); // Cualquier código por encima de 400 (inclusive) es un error.
	} // SetHTTPError
/*
	La respuesta del servidor es siempre JSON.
	Esta función interpreta el error del parser de JSON.
*/	
	protected function SetJSONError($error_code) {
		$result = false; // Asumir ningún error.
		$linea = null;
		$this->errores = NULL;
		$this->json_nroerr = $error_code;
		if ($this->json_nroerr != 0) { // Hubo un error?
			$result = true; // Sí!
			if (isset($this->json_errors[$this->json_nroerr])) { // Cuál?
				$this->errores = $this->json_errors[$this->json_nroerr];
			} else {
				$this->errores = 'Error de JSON no esperado'; // No sé cuál es el error.
			} // else
		} // if
		$this->msg = "JSON Error: ".$this->json_nroerr." ".$this->errores;
		if ($this->debug_level > 0) { 
			$this->SetLog(__LINE__." ".__METHOD__." ".$this->msg);
		}
		return $result;
	} // SetJSONError()
	
	protected function SetError($file, $method, $msg) {
		$this->error = true;
		$this->errores = $msg;
		$this->msg = $msg;
		$line = basename($file)." -> ".$method.". ".$msg;
		if ($this->debug_level > 0) { 
			$this->SetLog(__LINE__." ".__METHOD__." ".$msg);
		}
	} // SetError
/**
* Summary. Asegurar que la URL está bien formada.
*/
	public function FormarURI() {
		$result = $this->baseURL;
		if (!empty($result)) {
			if (substr($result,-1,1) != '/') {
				$result .= '/';
			}
		}
		if (!empty($this->url)) {
			if (substr($this->url,0,1) == '/') {
				$result .= substr($this->url,1,strlen($this->url));
			} else {
				$result .= $this->url;
			}
		}
		return $result;
	}

/**
* Summary. Para saber si se está en modo CLI o web, útil para disponer o no de _SESSION
* @return bool.
*/
	protected function ModoCLI() {
		return (php_sapi_name() == "cli");
	}


}

?>