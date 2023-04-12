<?php
/*
	Clase básica del cliente del web service para la versión V2 del core.
	Created: 2021-05-25
	Author: DriverOp

	Rebrit SRL - Chacabuco 59 - Gualeguaychú - Entre Ríos.
	email: info@rebrit.ar

*/
require_once(LoadConfig("wsconfig.inc.php")); // Donde está la configuración para acceder al web service.
require_once(__DIR__.DS."class.wsv2BaseHelpers.inc.php");
require_once(DIR_includes."cypher.inc.php");

class cWsV2ClientBase extends cV2Base {

	public $timeout = 30;
	public $theToken = '';
	public $expireToken = 0;
	public $url = '';
	public $final_url = '';
	public $method = 'GET';
	public $checktoken = false; // Forzar a verficar el token en la petición.
	public $curl_nroerr = 0;
	public $debug_level = 0;
	public $username = '';
	public $password = '';
	public $strictJson = false; // Tratar la respuesta del servidor como todo JSON, de lo contrario buscar lo primero que se parezca a un JSON.
	public $encodeContent = false; // Establece que el mensaje al servidor estará cifrado.
	public $encodeContentPassword = ''; // La contraseña de cifrado.
	public $transId = null; // Identificador de transacción. Se manda al server y el server responde con el mismo ID.
	public $response = null; // Almacena la respuesta (body) del servidor core.
	public $headers = null; // Almacena las cabeceras de la respuesta del servidor.
	public $theData = null; // Almacena el campo data (como objeto) de la respuesta.
	public $referer = 'http://localhost/';
	private $curl = null; // El handler o puntero a la instancia de cURL.
	private $sessname = 'wsv2';

	public $FixedcURLOptions = array(
		CURLOPT_HEADER => false,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_SSL_VERIFYPEER=>false,
		CURLOPT_TIMEOUT => 30,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_AUTOREFERER => true,
	);

	function __construct() {
		parent::__construct();
		if (version_compare(PHP_VERSION, '7.2.0','<')) {
			$this->SetLog(__METHOD__ ." ".internal_errors[1],LGEV_FATAL);
			throw new Exception(internal_errors[1]);
			return;
		}
		if (!function_exists('curl_init')) {
			$this->SetLog(__METHOD__ ." ".internal_errors[2],LGEV_FATAL);
			throw new Exception(internal_errors[2]);
			return;
		}
		if (!empty($_SERVER['HTTP_HOST'])) {
			$this->referer = 'http://'.$_SERVER['HTTP_HOST'].'/';
		}
		
		if (defined("WS_TIMEOUT") and (WS_TIMEOUT > 0)) {
			$this->timeout = WS_TIMEOUT;
		}
		
		if (defined("WS_SESSION_NAME") and !empty(WS_SESSION_NAME)) {
			$this->sessname = WS_SESSION_NAME;
		}
		if (!empty($_SESSION[$this->sessname]['token'])) {
			$this->theToken = $_SESSION[$this->sessname]['token'];
		}
		if (!empty($_SESSION[$this->sessname]['expire'])) {
			$this->expireToken = $_SESSION[$this->sessname]['expire'];
		}
		if (defined("WS_DEBUG_LEVEL")) {
			$this->debug_level = WS_DEBUG_LEVEL;
		}
		if (defined("WS_URL") and !empty(WS_URL)) {
			$this->baseURL = EnsureTrailingURISlash(WS_URL);
		}
		if (defined("WS_USER") and !empty(WS_USER)) {
			$this->username = WS_USER;
		}
		if (defined("WS_PASS") and !empty(WS_PASS)) {
			$this->password = WS_PASS;
		}
		if (defined("WS_ENCODE_CONTENT")) {
			$this->encodeContent = WS_ENCODE_CONTENT;
		}
		if (defined("WS_ENCODE_CONTENT_PASSWORD")) {
			$this->encodeContentPassword = WS_ENCODE_CONTENT_PASSWORD;
		}
	} // __construct

/**
* Summary. Arma el request.
* @param string $method. El method a usar (GET, POST, PUT, etc...)
* @param string $uri. La URI, es decir, lo que va después de la dirección del WS, o en otras palabras, el servicio que se quiere invocar.
* @param mixed $params. Los parámetros con los cuales enviar la petición.
* @return mixed bool false cuando no se pudo obtener una respuesta del servidor, un objeto (el campo 'data') de la respuesta del servidor.
*/
	public function Commit($method = '', $uri = '', $params = null) {
		$result = false;
		$this->intl_nroerr = 0;
		$this->curl_nroerr = 0;
		$this->http_nroerr = 0;
		$this->json_nroerr = 0;
		$this->core_nroerr = 0;
		$this->error = false;
		try {
			
			if (empty($this->theToken)) {
				if(!$this->GetToken()) { return false;}
			} else {
				if (time()>$this->expireToken) {
					if(!$this->GetToken()) { return false;}
				}
			}
			
			if (!empty($uri)) {
				$this->url = $uri;
			}
			
			if (!empty($method)) {
				$this->method = $method;
			}
			
			$this->final_url = $this->FormarURI();
			
			if (!empty($method) and in_array(strtoupper($method), ['GET','POST','PUT','DELETE','OPTIONS','HEAD',''])) {
				$this->method = strtoupper($method);
			}
			
			
			$this->cURLOptions = $this->FixedcURLOptions + array(
				CURLOPT_TIMEOUT => $this->timeout,
				CURLOPT_USERAGENT => $this->userAgent,
				CURLOPT_REFERER => $this->referer,
				CURLOPT_URL => $this->final_url,
				CURLOPT_CUSTOMREQUEST => $this->method, // GET POST PUT PATCH DELETE HEAD OPTIONS 
			);
			if (in_array($this->method, ['POST','PUT']) and CanUseArray($params)) {
				if (is_object($params)) {
					$params = json_decode(json_encode($params),true);
				}
				$this->cURLOptions[CURLOPT_POST] = true;
				if ($this->encodeContent) {
					$this->AddHTTPHeader('Content-Type','text/plain');
					$params = json_encode($params);
					$params = CypherAES($params, $this->encodeContentPassword);
					$this->cURLOptions[CURLOPT_POSTFIELDS] = $params;
				} else {
					$this->cURLOptions[CURLOPT_POSTFIELDS] = http_build_query($params);
				}
			}
			if ($this->encodeContent) {
				$this->AddHTTPHeader('Content-Encoding',CIPHER_METHOD); // CIPHER_METHOD está definida en cypher.inc.php
			}
			if (!empty($this->theToken)) {
				$this->AddHTTPHeader('Authorization','Bearer '.$this->theToken);
			}
			if (!empty($this->transId)) {
				$this->AddHTTPHeader('Transaction-id',$this->transId);
			}
			if ($this->ExecRequest()) {
				$this->ParseResponse();
				$result = (!empty($this->theData))?$this->theData:true;
				$this->core_nroerr = -1;
				if (!empty($this->parsedResponse) and is_object($this->parsedResponse)) {
					$this->core_nroerr = (isset($this->parsedResponse->errnro))?$this->parsedResponse->errnro:0;
					$this->core_msg = (isset($this->parsedResponse->msg))?$this->parsedResponse->msg:null;
					$this->core_mode = (isset($this->parsedResponse->mode))?$this->parsedResponse->mode:null;
					$this->core_tenant = (isset($this->parsedResponse->tenant))?$this->parsedResponse->tenant:null;
					$this->core_time = (isset($this->parsedResponse->time))?$this->parsedResponse->time:null;
					$this->core_transId = (isset($this->parsedResponse->transId))?$this->parsedResponse->transId:null;
				}
				
			}
		} catch(Exception $e) {
			$this-SetError($e);
		}
		return $result;
	}
/**
* Summary. Obtener un token nuevo.
* @return bool.
*/
	public function GetToken():bool {
		$result = false;
		$this->error = false;
		try {
			$this->theToken = null;

			$this->cURLOptions = $this->FixedcURLOptions + array(
				CURLOPT_TIMEOUT => $this->timeout,
				CURLOPT_USERAGENT => $this->userAgent,
				CURLOPT_REFERER => $this->referer,
				CURLOPT_URL => $this->baseURL."auth",
				CURLOPT_CUSTOMREQUEST => "POST", // GET POST PUT PATCH DELETE HEAD OPTIONS 
				CURLOPT_POST => true,
			);
			$mensaje = '{"username":"'.$this->username.'","password":"'.$this->password.'"}';
			$this->cURLOptions[CURLOPT_POSTFIELDS] = $mensaje;
			if ($this->encodeContent) {
				$this->AddHTTPHeader('X-Describe-Request','getToken');
				$this->AddHTTPHeader('Content-Encoding',CIPHER_METHOD); // CIPHER_METHOD está definidca en cypher.inc.php
				$this->cURLOptions[CURLOPT_POSTFIELDS] = CypherAES($mensaje, $this->encodeContentPassword);
			}
			if (!$this->ExecRequest()) {
				throw new Exception(__LINE__ ." Fallo al obtener token!");
			}

			$this->ParseResponse();
			if (!empty($this->theData) and is_object($this->theData) and isset($this->theData->token)) {
				$this->theToken = $this->theData->token;
				$_SESSION[$this->sessname]['token'] = $this->theToken;
			} else {
				throw new Exception(__LINE__ ." El servidor no devolvió token!");
			}
			if (!empty($this->theData) and is_object($this->theData) and isset($this->theData->expire)) {
				$this->expireToken = $this->theData->expire;
				$_SESSION[$this->sessname]['expire'] = $this->expireToken;
			} else {
				$this->expireToken = time()+3600;
			}
			$result = true;
		} catch(Exception $e) {
			$this->SetError($e);
		}

		return $result;
	}
/**
* Summary. Ejecutar efectivamente la request preparada.
* @return bool.
*/
	private function ExecRequest():bool {
		$result = true;
		try {
			if ($this->debug_level > 0) {
				$this->SetLog(__METHOD__ ." ".__LINE__ ." ".$this->cURLOptions[CURLOPT_CUSTOMREQUEST].": ".$this->cURLOptions[CURLOPT_URL]." Params: ".((!empty($this->cURLOptions[CURLOPT_POSTFIELDS]))?$this->cURLOptions[CURLOPT_POSTFIELDS]:'(none)'));
			}
			$this->curl = curl_init();
			
			curl_setopt_array($this->curl, $this->cURLOptions);
			//curl_setopt($this->curl, CURLOPT_HEADER, 1); // Incluir las cabeceras en la respuesta. Es necesario para saber si el core respondió con mensaje cifrado luego los headers se parsean con el método GetHeadersBody
			
			/*
				Esto es complicado. Para obtener correctamente los headers del servidor, hay que hacer este galimatias.
				Hay otra forma de hacerlo, ver método GetHeadersBody, pero ésta es la correcta.
			*/
			$headers = [];
			curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($this->curl, CURLOPT_HEADERFUNCTION, 
				function ($curl, $theHeader) use (&$headers) {
					$ln = strlen($theHeader);
					$theHeader = explode(':',$theHeader,2);
					if (is_array($theHeader) and (count($theHeader)>1)) {
						$key = trim($theHeader[0]);
						$value = trim($theHeader[1]);
						if (!empty($value)) {
							$headers[$key] = $value;
						}
					}
					return $ln;
				} // callback
			); // curl_setopt
			
			$this->response = curl_exec($this->curl); // Exec!
			if ($this->debug_level > 1) {
				$this->SetLog(__METHOD__ ." ".__LINE__ ." ".$this->response);
			}
			if($this->SetCURLError()) {
				throw new Exception($this->msgerr);
			}
			
			$this->headers = $headers;

			$this->SetHTTPError();

		} catch(Exception $e) {
			$this->SetError($e);
			$result = false;
		} finally {
			curl_close($this->curl);
		}
		return $result;
	}
/*
	Asegurar que la URL está bien formada.
*/
	public function FormarURI():string {
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
* Summary. Parsear la respuesta del core.
* @
*/
	private function ParseResponse() {
		try {
			
			$this->theData = null;

			if (empty($this->response)) { throw new Exception(__LINE__ ." El servidor devolvió una respuesta vacía."); }
			
			$this->IsEncoded();
			if ($this->encodeContent) {
				$this->response = DecypherAES($this->response, $this->encodeContentPassword);
			}

			$this->parsedResponse = json_decode(($this->strictJson)?$this->response:ExtractJsonEx($this->response), false, 512, JSON_BIGINT_AS_STRING);
			$this->json_nroerr = json_last_error();
			
			if ($this->SetJSONError()) { throw new Exception(__LINE__ ." No se pudo parsear la respuesta del servidor."); }
			
			if (isset($this->parsedResponse->data)) {
				$this->theData = new stdClass();
				$this->theData = $this->parsedResponse->data;
			}

			if (isset($this->parsedResponse->msg)) {
				$this->msgerr = $this->parsedResponse->msg;
			}


		} catch(Exception $e) {
			$this->SetError($e);
		}
	}
/**
* Summary. Agrega o reemplaza una cabecera HTTP a las opciones de la petición cURL.
* @param string $header El nombre del header.
* @param string $value El valor para esa cabecera.
* @note Si se asigna valor nulo a una cabecera, se elimina de las cabeceras a enviar si existe.
*/
	public function AddHTTPHeader(string $header = '', string $value = null) {
		$header = trim($header);
		if (empty($header)) { return; } 
		$header = preg_replace('/\s+/','-',$header);
		$work = (isset($this->cURLOptions[CURLOPT_HTTPHEADER]))?$this->cURLOptions[CURLOPT_HTTPHEADER]:array();
		if (!is_array($work)) { $work = array(); }
		
		$ln = strlen($header);
		$found = false;
		foreach($work as $key => $head) {
			if (strtolower(substr($head,0,$ln)) == strtolower($header)) { $found = true; break; }
		}
		if ($found) {
			unset($work[$key]);
		}
		if (!empty($value)) {
			$work[] = $header.': '.$value;
		}
		$this->cURLOptions[CURLOPT_HTTPHEADER] = $work;
	}
/**
* Summary. Interpreta el error devuelto por cURL.
* @return bool false cuando no hubo error, true cuando sí lo hubo.
*/
	protected function SetCURLError() {
		$result = false; // Asumir ningún error.
		$this->msgerr = '';
		$this->curl_nroerr = curl_errno($this->curl);
		if ($this->curl_nroerr != 0) { // Hubo un error?
			$result = true; // Sí!
			$this->msgerr = (isset(curl_errors[$this->curl_nroerr]))?curl_errors[$this->curl_nroerr]:'cRUL Error: '.$this->curl_nroerr;
			if (($this->debug_level > 0)) {
				$this->SetLog(__METHOD__ ." ".$this->msgerr, LGEV_WARN);
			}
		}
		return $result;
	} // SetCURLError
/**
* Summary. Interpretar código de Status HTTP como un error... o no.
*/	
	protected function SetHTTPError() {
		$this->msgerr = NULL;
		$this->http_nroerr = curl_getinfo($this->curl,CURLINFO_HTTP_CODE);
		$this->msgerr = (isset(http_codes[$this->http_nroerr]))?http_codes[$this->http_nroerr]:$this->http_nroerr;
		$this->msgerr = "HTTP Status: ".$this->http_nroerr." ".$this->msgerr;
		if (($this->debug_level > 0) or ($this->http_nroerr >= 400)) {
			$this->SetLog(__METHOD__ ." ".$this->msgerr, LGEV_WARN);
		}
		$this->error = ($this->http_nroerr >= 400); // Cualquier código por encima de 400 (inclusive) es un error.
		return $this->error;
	} // SetHTTPError

/**
* Summary. La respuesta del servidor es siempre JSON. Esta función interpreta el error del parser de JSON.
*/	
	protected function SetJSONError() {
		$result = false; // Asumir ningún error.
		$this->msgerr = NULL;
		if ($this->json_nroerr != JSON_ERROR_NONE) { // Hubo un error?
			$this->error = true;
			$result = true; // Sí!
			$this->msgerr = 'Error de JSON: '.$this->json_nroerr.' '.((isset(json_errors[$this->json_nroerr]))?json_errors[$this->json_nroerr]:'no esperado: '.$this->json_nroerr);
			if (($this->debug_level > 0)) {
				$this->SetLog(__METHOD__ ." ".$this->msgerr, LGEV_WARN);
			}
		} // if
		return $result;
	} // SetJSONError()
/**
* Summary. Establece el error interno propio de esta clase.
*/
	protected function SetINTERNALError() {
		$result = false; // Asumir ningún error.
		$this->msgerr = NULL;
		if ($this->intl_nroerr != 0) { // Hubo un error?
			$result = true; // Sí!
			$this->error = true;
			$this->msgerr = 'Error Interno: '.$this->intl_nroerr.((isset(internal_errors[$this->intl_nroerr]))?internal_errors[$this->intl_nroerr]:'no esperado: '.$this->intl_nroerr);
			if (($this->debug_level > 0)) {
				$this->SetLog(__METHOD__ ." ".$this->msgerr, LGEV_WARN);
			}
		} // if
		return $result;
	} // SetINTERNALError
/**
* Summary. Determina si el cuerpo del mensaje está cifrado mirando si el servidor envió la cabecera correcta.
* @note Esto modifica la propiedad ->encodeContent apropiadamente, no decifra el cuerpo.
*/
	private function IsEncoded() {
		$this->encodeContent = false;
		try {
			$headers = array_change_key_case($this->headers);
			if (isset($headers['content-encoding'])) { // La cabecera indicado contenido cifrado, según el RFC 8188. Debe valer el algoritmo de cifrado usado para cifrar el cuerpo del mensaje, sin embargo nosotros solo aceptamos uno, el declarado en la constante CIPHER_METHOD
				if (strtoupper(trim($headers['content-encoding'])) != strtoupper(CIPHER_METHOD)) {
					throw new Exception("No entendemos el cifrado '".$headers['content-encoding']."' enviado por el servidor.");
				}
				$this->encodeContent = true;
			}
		} catch(Exception $e) {
			$this->SetError($e);
		}
	}
/**
* Summary. Separa las cabeceras del body de la respuesta del servidor.
* @return array En dos elementos, primero el body, luego las cabeceras.
*/
	private function GetHeadersBody($work) {
		try {
			$this->headers = array();
			$size = curl_getinfo($this->curl, CURLINFO_HEADER_SIZE);
			$headers = substr($work, 0, $size);
			$this->response = substr($work, $size); // Obtiene el body.
			// Los headers son más complicados.
			/*
			$headers = preg_replace("/[\r\n|\n\r|\n|\r]+/","\r\n",$headers);
			$headers = array_filter(explode("\r\n",$headers));
			if (is_array($headers) and (count($headers) > 0)) {
				foreach($headers as $line) {
					list($key, $value) = explode(': ',$line,2);
					$this->headers[$key] = trim($value);
				}
			}
			*/
		} catch(Exception $e) {
			$this->SetError($e);
		}
	}
} // Class.

function DealWithHeaders($curl, $headers) {
	ShowVar($headers);
	return strlen($headers);
}