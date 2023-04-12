<?php
/*
	Clase base para el cliente del Web Service V1 de OmbuCredit Core.
	
	Created: 2020-12-12
	Author: DriverOp.
	Rebrit SRL - San Martín 987 - Gualeguaychú - Entre Ríos.
	email: info@fivemedia.com.ar
*/

require_once("wsv1client.msgs.php");

class cWSV1ClientBase {
	
	public $parsed_response = null; // Aquí se almacena la respuesta completa.
	public $data = null; // Aquí se almacena el "payload" de la respuesta.
	public $response = '';
	public $errores = '';
	public $intl_nroerr = 0;
	public $curl_nroerr = 0;
	public $http_nroerr = 0;
	public $json_nroerr = 0;
	public $api_nroerr = 0;
	public $url = '';
	public $debug_level = 2; // Nivel de detalle de los logs
	public $echo_debug = false; // Si además hay que imprimir el log por pantalla.
	public $timeout = 30;
	public $response_type = 'object';
	public $baseURL = 'http://localhost/';
	public $sessname = 'session_wsv1';
	public $username = '';
	public $password = '';
	public $token_ttl = 3600;
	public $referer = 'http://localhost/';
	public $logfilename = 'wsv1';
	public $logdir = '';
	public $lastmsg = '';
	private $token = null;
	private $expire = 0;
	private $final_url = null;
	private $userAgent = 'Rebrit cURL Client 1.0';

	public $defaultoptions = array(
		CURLOPT_HEADER => false,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_SSL_VERIFYPEER=>false,
		CURLOPT_TIMEOUT => 30
	);
	
	function __construct() {
		global $internal_errors;
		if (version_compare(PHP_VERSION, '5.4.0','<')) {
			$this->SetINTERNALError(1);
			echo $internal_errors[1];
			return;
		}
		if (!function_exists('curl_init')) {
			$this->SetINTERNALError(2);
			echo $internal_errors[2];
			return;
		}
		$this->referer = 'http://'.@$_SERVER['HTTP_HOST'].'/';
		$this->defaultoptions[CURLOPT_TIMEOUT] = $this->timeout;
		if (!empty($_SESSION[$this->sessname]['token'])) {
			$this->token = $_SESSION[$this->sessname]['token'];
		}
		if (!empty($_SESSION[$this->sessname]['expire'])) {
			$this->expire = $_SESSION[$this->sessname]['expire'];
		}
		
		$this->logfilename = '_'.$this->logfilename.'.log';
	} // __constructor


/**
* Summary. Envía una petición al servidor vía cURL.
* @param str $type. El tipo de petició GET, POST, PUT, etc...
* @param array $data. Los datos a enviar al servidor.
* @param bool default false $checktoken. Fuerza a buscar un token antes de realizar la petición 
* @return bool. Pudo o no pudo realizar la petición.
*/
	public function Commit($type = 'GET', $data = array(), $checktoken = false) {
		$result = true;
		$this->errores = '';
		$this->parsed_response = ($this->response_type != 'object')?array():new stdClass();
		if ($checktoken) {
			$result = $this->GetToken(); // ... buscar un nuevo token
		} else {
			if (empty($this->token) or ((time() > (int)$this->expire))) {
				$result = $this->GetToken(); // ... buscar un nuevo token
			}
		}
	
		$this->final_url = $this->FormarURI();
		if (empty($this->final_url)) {
			$this->final_url = 'http://localhost';
		}
		
		if (is_object($data)) {
			$data = json_decode(json_encode($data),true);
		}

		if ($this->debug_level > 0) {
			$msg = __LINE__." ".__METHOD__." ".$type.": URL: ".$this->final_url;
			if ($this->debug_level > 1) {
				$this->SetLog($msg." ".print_r($data,true),__FILE__);
			} else {
				$this->SetLog($msg,__FILE__);
			}
		}
		if (!$result) {
			$this->SetLog(__LINE__." ".__METHOD__.': Abortado, no se pudo establecer el token.',__FILE__);
			return false;
		}

		$link = curl_init();
		
		$options = array(
			CURLOPT_USERAGENT => $this->userAgent,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_AUTOREFERER => true,
			CURLOPT_REFERER => $this->referer,
			CURLOPT_URL => $this->final_url,
			CURLOPT_CUSTOMREQUEST => $type, // GET POST PUT PATCH DELETE HEAD OPTIONS 
		);
		
		if (CanUseArray($data)) {
			if ($type=='PUT') {
				$options[CURLOPT_POST] = true;
				$options[CURLOPT_POSTFIELDS] = http_build_query($data);
			}
			if ($type=='POST') {
				$options[CURLOPT_POST] = true;
				$options[CURLOPT_POSTFIELDS] = http_build_query($data,'','&');
			}
		}
		
		curl_setopt_array($link, ($options + $this->defaultoptions));

		curl_setopt($link, CURLOPT_HTTPHEADER, array(
			'Authorization: Bearer '.$this->token
		));
		//curl_setopt($link, CURLOPT_HEADER, 1); // Incluye los encabezados del servidor en la respuesta, lo cual destruye el formato del JSON

		if ($this->debug_level > 0) {
			$msg = __LINE__." ".__METHOD__." Resultado de la petición: ".$type." URL: ".$this->final_url." Data: ".((empty($data))?'(ninguno)':print_r($data,true));
			if ($this->debug_level > 1) {
				$this->SetLog($msg." Token: ".$this->token,__FILE__);
			} else {
				$this->SetLog($msg,__FILE__);
			}
		}
		$this->data = false;

		try {
			$this->response = curl_exec($link); // Exec!
			if($this->SetCURLError($link)) {
				throw new Exception(__LINE__.' cURL error: '.$this->curl_nroerr);
			}
			if ($this->debug_level > 0) {
				if ($this->debug_level > 1) {
					$this->SetLog($this->response);
				} else {
					$this->SetLog(mb_substr($this->response,0,2048));
				}
			}
			$this->SetHTTPError($link);
		} catch(Exception $e) {
			$this->SetLog($e->GetMessage());
			$result = false;
		} finally {
			curl_close($link);
		}
		$this->parsed_response = json_decode($this->response); // La respuesta del servidor es siempre un JSON...
		$this->json_nroerr = json_last_error();
		if (!$this->SetJSONError()) { // Si no hay error de JSON.
			foreach($this->parsed_response as $key => $value) {
				if (strtolower($key) != 'data') {
					$this->$key = $value;
				} else {
					$this->data = $value;
				}
			}
			if ($this->http_nroerr < 400) {
				if (is_object($this->data) or is_array($this->data)) {
					foreach($this->data as $key => $value) {
						$this->$key = $value;
					}
				}
			} else {
				$result = false;
			}
		} else {
			$result = false;
		}
		if (!$result and ($this->debug_level > 0)) {
			$msg = __METHOD__." intl_nroerr:".$this->intl_nroerr.", curl_nroerr:".$this->curl_nroerr.", http_nroerr:".$this->http_nroerr." json_nroerr:".$this->json_nroerr.", result:".(($result)?'exito':'error');
			$this->SetLog($msg,__FILE__);
		}
		return $result;
	}

/**
* Summary. Verifica la validez del token sin preguntarle al servidor.
* @return bool.
*/
	public function CheckToken() {
		$result = false;
		if (!$this->ModoCLI()) {
			if (!empty($_SESSION[$this->sessname]['token'])) {
				$this->token = $_SESSION[$this->sessname]['token'];
			}
			if (!empty($_SESSION[$this->sessname]['expire'])) {
				$this->expire = $_SESSION[$this->sessname]['expire'];
			}
		} else {
			if (ExisteArchivo($this->username.".json")) {
				$a = json_decode(file_get_contents($this->username.".json"));
				$this->token = @$a->token;
				$this->expire = @$a->expire;
			}
		}
		if (!empty($this->token) and ((time() < (int)$this->expire))) {
			$result = true;
		}
		return $result;
	}




/**
* Summary. Pedir un nuevo token al WS.
* @return bool.
*/	
	public function GetToken() {
		$this->parsed_response = new stdClass;
		$this->data = null;
		$this->errores = '';
		$result = true;
		$this->token = null;
		$this->expire = 0;
		$link = curl_init();
		$post_query = array(
			'username'=>$this->username,
			'password'=>$this->password
		);
			
		$options = array(
			CURLOPT_USERAGENT => $this->userAgent,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_AUTOREFERER => true,
			CURLOPT_REFERER => $this->referer,
			CURLOPT_URL => $this->baseURL.'auth/',
			CURLOPT_CUSTOMREQUEST => 'POST', // GET POST PUT PATCH DELETE HEAD OPTIONS 
			CURLOPT_POSTFIELDS => http_build_query($post_query,'','&')
		);
		curl_setopt_array($link, ($options + $this->defaultoptions));

		if ($this->debug_level > 0) {
			$this->SetLog(__METHOD__." URL: ".$this->final_url,__FILE__);
		}
		if ($this->debug_level > 1) {
			$this->SetLog('POST: '.print_r($post_query,true));
		}
		try {
			$this->response = curl_exec($link); // Exec!
			if($this->SetCURLError($link)) {
				throw new Exception(__LINE__.'cURL error: '.$this->curl_nroerr);
			}
			if ($this->debug_level > 1) {
				$this->SetLog($this->response);
			}
			$this->SetHTTPError($link);
		} catch(Exception $e) {
			$this->SetLog($e->GetMessage());
			$result = false;
		} finally {
			curl_close($link);
		}
		$this->parsed_response = json_decode($this->response); // La respuesta del servidor es siempre un JSON...
		$this->json_nroerr = json_last_error();
		if (!$this->SetJSONError()) { // Si no hay error de JSON.
			foreach($this->parsed_response as $key => $value) {
				if (strtolower($key) != 'data') {
					$this->$key = $value;
				} else {
					$this->data = $value;
				}
			}
			if ($this->http_nroerr < 400) {
				foreach($this->data as $key => $value) {
					$this->$key = $value;
				}
				if (empty($this->data->token)) {
					$this->SetLog(__LINE__." ".__METHOD__." El servido no devolvió TOKEN: ".$this->http_nroerr);
					$result = false;
				} else {
					$this->token = $this->data->token;
					$this->expire = $this->data->expire;
					if (!$this->ModoCLI()) {
						$_SESSION[$this->sessname]['token'] = $this->token;		// Esto sirve para renovar la sesión local al cliente...
						$_SESSION[$this->sessname]['expire'] = $this->expire;
					} else {
						file_put_contents($this->username.".json", json_encode($this->data));
					}
				}
			} else {
				$this->SetLog(__LINE__." ".__METHOD__." No se pudo establecer el token porque el servidor devolvió un estado de error ".$this->http_nroerr);
				$result = false;
			}
		} else {
			$result = false;
		}
		return $result;
	} // GetToken







/*
	Asegurar que la URL está bien formada.
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
* Summary. Escribe una entrada en el log.
* @param str $linea. La línea de texto.
* @param str $source_file. El nombre del archivo desde donde se genera la entrada en el log. El método recorta la ruta de directorios hasta la raíz del desarrollo.
*/
	public function SetLog($linea, $source_file = null) {
		if (!empty($source_file)) {
			$source_file = substr($source_file,strlen(DIR_BASE))." ";
			$linea = $source_file." ".$linea;
		}
		
		if ($this->echo_debug) {
			echo $linea.FDL;
		}

		umask(0);
	
		$mes = Date('Y-m');
		$dia = Date('Y-m-d');
		
		$dir = $this->logdir.$mes;
		
		$archivo = $dir.DIRECTORY_SEPARATOR.$dia.$this->logfilename;
		
		if (!file_exists($this->logdir)) {
			mkdir($this->logdir,0777);
		}
	
		if (!file_exists($dir)) {
			mkdir($dir,0777);
		}
		
		$this->lastmsg = $linea;
		$linea = '['.Date('Y-m-d H:i:s').'] '.$linea.PHP_EOL;
		return file_put_contents($archivo, $linea, FILE_APPEND);
	}
/*
	Establece el error interno propio de esta clase.
*/
	private function SetINTERNALError($nro = 0) {
		global $internal_errors;
		$this->intl_nroerr = $nro;
		if (isset($internal_errors[$nro])) {
			$this->errores = $internal_errors[$nro];
		}
		$msg = "Internal Error: ".$this->intl_nroerr;
		if ($this->debug_level > 0) {
			if ($this->debug_level > 1) {
				$msg .= " ".$this->errores;
			}
			$this->SetLog($msg,__FILE__);
		}
		return $msg;
	} // SetINTERNALError
/*
	Interpreta el error devuelto por cURL.
*/
	private function SetCURLError($link) {
		global $curl_errors;
		$result = false; // Asumir ningún error.
		$this->errores = '';
		$this->curl_nroerr = curl_errno($link);
		if ($this->curl_nroerr != 0) { // Hubo un error?
			$result = true; // Sí!
			if (isset($curl_errors[$this->curl_nroerr])) {
				$this->errores = $curl_errors[$this->curl_nroerr];
			}
			$msg = "cRUL Error: ".$this->curl_nroerr;
			if ($this->debug_level > 1) {
				$msg .= " ".$this->errores;
			}
			$this->SetLog($msg,__FILE__);
		}
		return $result;
	} // SetCURLError
/*
	Interpretar código de Status HTTP como un error... o no.
*/	
	private function SetHTTPError($link) {
		global $http_codes;
		$msg = null;
		$this->errores = NULL;
		$this->http_nroerr = curl_getinfo($link,CURLINFO_HTTP_CODE);
		if (isset($http_codes[$this->http_nroerr])) {
			$this->errores = $http_codes[$this->http_nroerr];
		} else {
			$this->errores = 'HTTP code: '.$this->http_nroerr;
		}
		$msg = "HTTP Status: ".$this->http_nroerr;
		if (($this->debug_level > 0) or ($this->http_nroerr >= 400)) {
			if ($this->debug_level > 1) {
				$msg .= " ".$this->errores;
			}
			$this->SetLog($msg,__FILE__);
		}
		return ($this->http_nroerr >= 400); // Cualquier código por encima de 400 (inclusive) es un error.
	} // SetHTTPError
/*
	La respuesta del servidor es siempre JSON.
	Esta función interpreta el error del parser de JSON.
*/	
	public function SetJSONError() {
		global $json_errors;
		$result = false; // Asumir ningún error.
		$linea = null;
		$this->errores = NULL;
		if ($this->json_nroerr != JSON_ERROR_NONE) { // Hubo un error?
			$result = true; // Sí!
			if (isset($json_errors[$this->json_nroerr])) { // Cuál?
				$this->errores = $json_errors[$this->json_nroerr];
			} else {
				$this->errores = 'Error de JSON no esperado'; // No sé cuál es el error.
			} // else
		} // if
		$msg = "JSON Error: ".$this->json_nroerr;
		if (($this->debug_level > 0) or ($this->json_nroerr != JSON_ERROR_NONE)) {
			if ($this->debug_level > 1) {
				$msg .= " ".$this->errores;
			}
			$this->SetLog($msg,__FILE__);
		}
		return $result;
	} // SetJSONError()
	
/**
* Summary. Para saber si se está en modo CLI o web, útil para disponer o no de _SESSION
* @return bool.
*/
	private function ModoCLI() {
		return (php_sapi_name() == "cli");
	}
}
?>