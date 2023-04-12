<?php
/*
	Clase básica para las peticiones del API REST versión 2.1
	Created: 2021-05-04 (ver 2.0)
	Author: DriverOp
	Created: 2021-08-20 (ver 2.1)
	Author: DriverOp
		En esta versión el método GetResolver() es diferente al de la versión 2.0
	Updated: 2021-11-18 (ver 2.2)
	Author: DriverOp
		GetResolver() ahora puede devolver un array de archivos. En la definición de la API resolver puede ser una lista de archivos separados por coma.
		La propiedad ->msgerr no estaba definida.
*/

require_once(DIR_includes."class.security.inc.php");
require_once(DIR_includes."ws_constants.inc.php");
require_once(DIR_includes."cypher.inc.php");
require_once(DIR_includes."class.logging.inc.php");
require_once(DIR_model."class.sysparams.inc.php");
require_once(DIR_model."wsv2".DS."class.wsv2Users.inc.php");

class cWebService {

	public $method = 'GET';
	public $params = array();
	public $body = null;
	public $parsedBody = null;
	public $encodeContent = false;
	public $encodeContentPassword = '';
	public $responseSent = false;
	public $transId = null;
	public $backendUserId = null; // ID del usuario del backend enviado desde el backend.
	public $error = false;
	public $error_code = 0;
	public $DebugOutput = false;
	public $log_max_length = 0;
	public $baseDir = DIR_BASE;
	public $continue = true;
	public $headers = array();
	public $theResolver = ''; // Almacena el path absoluto al archivo resolver.
	public $restricted = false; // Indica si el contenido solicitado está restringido (que requiere autorización, token o usuario logeado).
	public $msgerr = null; // Mensaje de error general.
	
	public $actual_file = __FILE__;
	public $usuario = null;


	function __construct(cWsUsuario $usuario) {
		$this->usuario = $usuario;
		$this->usuario->SetSecretUserKey();
		$this->method = (!empty($_SERVER['REQUEST_METHOD']))?$_SERVER['REQUEST_METHOD']:$this->method;
		$this->method = strtoupper($this->method);
		$this->body = file_get_contents('php://input');
		if (function_exists("getallheaders"))
		$this->headers = getallheaders();
		$this->parsedBody = new stdClass();
		switch ($this->method) {
			case 'OPTION': // Esto es para cumplir con el protocolo CORS.
				header($_SERVER['SERVER_PROTOCOL'].' 200 '.@HTTP_CODES[200]);
				die(); break;
			case 'OPTIONS': // Esto es para cumplir con el protocolo CORS.
				header($_SERVER['SERVER_PROTOCOL'].' 200 '.@HTTP_CODES[200]);
				die(); break;
			case 'GET':
				$this->params = $_GET;
				break;
			case 'POST':
				$this->params = $_REQUEST;
				break;
			default:
				$this->params = $_REQUEST;
		}
		$this->IsEncoded();
		$this->GetTransId();
		$this->GetBackendUserId();
	}
/**
* Summary. Setter de la propiedad que apunta al usuario actual del web service.
* @param $usuario cWsUsuario.
*/
	public function SetUsuario(cWsUsuario $usuario) {
		$this->usuario = $usuario;
		$this->usuario->SetSecretUserKey();
	}

/**
* Summary. Devuelve una respuesta al cliente.
* @param int $http_status. El status HTTP a devolver
* @param var $data. Los datos a devolver al cliente, puede ser un objeto, un array o un string.
* @param str $msg. Un mensaje textual además de los datos.
* @param int $errorcode. Cualquiera de los códigos de error interno listados en WS_INTERNAL_CODES.
*/
	public function SendResponse(
		$http_status = 200,	// El status HTTP a devolver
		$data = null,		// Los datos a devolver al cliente
		$errorcode = 0,		// El código de error interno
		$msg = ''			// El mensaje
		) 
	{
		try {
			if ($this->responseSent) { throw new Exception(" Respuesta ya enviada."); }
			/* Esto es para almacenar los datos del archivo y la línea donde ya se generaron los encabezados. Es para debug. */
			$hsFile = null;
			$hsLine = null;
			
			if (is_string($errorcode) and !is_numeric($errorcode) and empty($msg)) {
				$msg = $errorcode;
				$errorcode = 0;
			}
			
			$result = array(
				'tenant'=>null,
				'mode'=>'N-A',
				'time'=>time(),
				'transId'=>$this->transId,
				'errnro'=>$errorcode,
				'msg'=>$msg,
				'data'=>''
			);
			if (!DEVELOPE) {
				unset($result['tenant']);
			} else {
				if (in_array(strtolower(DEPLOY),['dev','local'])) {
					$result['tenant'] = DEVELOPE_NAME;
				}
			}
			
			if (!empty($this->usuario) and is_object($this->usuario) and ($this->usuario->existe)) {
				$result['mode'] = (isset($this->usuario->tipo))?$this->usuario->tipo:'TEST';
			}
			if (empty($msg) and !empty(WS_INTERNAL_CODES[$errorcode])) {
				$result['msg'] = WS_INTERNAL_CODES[$errorcode];
			}
			if (empty($data) and (($errorcode > 0) or ($http_status >= 400))) { $data = $result['msg']; }
			if (!empty($data)) {
				$result['data'] = $data;
				if (is_object($result['data'])) {
					$result['data'] = json_decode(json_encode($result['data'],JSON_BIGINT_AS_STRING),true);
				}
				$result['data'] = $this->EnsureUTF8($result['data']);
			}
			
			//$result = CleanArray($result);
			$log = __FILE__." -> ".$http_status." : ";
			if (!headers_sent($hsFile, $hsLine)) {
				header($_SERVER['SERVER_PROTOCOL'].' '.$http_status.' '.@HTTP_CODES[$http_status]);
				header("Content-type: application/json; charset=UTF-8");
			} else {
				if (DEVELOPE)
					$result['headers_sent'] = cLogging::TrimBaseFile($hsFile.':'.$hsLine);
			}
			$salida = json_encode($result,JSON_UNESCAPED_UNICODE+JSON_BIGINT_AS_STRING);
			$cifrado = $salida;
			if ($this->encodeContent and !headers_sent()) {
				header("Content-Encoding: ".strtoupper(CIPHER_METHOD));
				$cifrado = CypherAES($salida, $this->encodeContentPassword);
				$log .= "(ciphered as) ".$cifrado." : ";
			}
			$log .= $salida;
			
			if (!DEVELOPE and ($errorcode > 0) or ($http_status >= 400)) {
				$this->SetLog($log, LGEV_WARN);
			} else {
				$this->SetLog($log, LGEV_INFO);
			}
			echo $cifrado;
			$this->responseSent = true;
			$this->error_code = $errorcode;
		} catch(Exception $e) {
			$this->SetError($e);
		}
		$this->continue = false;
		return false;
		
	}

/**
* Summary. Determina el archivo físico que atiende la petición dada como parámetro.
* @param array $contenido Es un array que es el resultado de cApis. Esto es nuevo en esta versión.
* @return mixed $result 
	string El path absoluto al archivo de resolución.
	array La lista de archivos de resolución, path absoluto en cada uno.
	bool: false en caso que el o alguno de los archivos no exista.
* @note También establece el valor de la propiedad ->theResolver al mismo valor que el result de este método.
*/	
	public function GetResolver(array $contenido) {
		$result = false;
		$this->theResolver = '';
		$this->restricted = true;
		try {
			$this->ParseBody();
			$baseDir = EnsureTrailingSlash($this->baseDir);
			if (isset($contenido['baseDir'])) {
				$baseDir .= EnsureTrailingSlash(cSecurity::NeutralizeDT($contenido['baseDir']));
			}
			if (isset($contenido['restricted']) and ($contenido['restricted'] === false)) { // Si el contenido está marcado como no restringido...
				$this->restricted = false;
			}
			if (!empty($contenido['resolver'])) {
				$aux = explode(',',$contenido['resolver']);
				$result = array();
				foreach($aux as $theFile) {
					$theFile = $baseDir.$theFile.'.php';
					if (!ExisteArchivo($theFile)) { throw new Exception('No se encontró archivo '.$theFile); }
					$result[] = $theFile;
				}
				if (count($result) == 1) { $result = $result[0]; }
				$this->theResolver = $result;
			}
			if (!empty($contenido['parameters']) and is_array($contenido['parameters'])) {
				$this->params = array_merge($contenido['parameters'],$this->params);
			}
		} catch(Exception $e) {
			$this->SetError($e);
			$result = false;
		}
		return $result;
	}
/**
* Summary. Determina quién es el usuario solicitando el contenido actual.
* @return bool.
*/
	public function GetWsUser():bool {
		$result = false;
		try {
			if (!$this->usuario->ExtractToken()) { $this->SendResponse(401, $this->usuario->msgerr,4); throw new Exception('No se indicó un token.'); }
			if (!$this->usuario->GetByToken($this->usuario->token)) { $this->SendResponse(401, 'Token no es válido.', 5); throw new Exception('Token inválido.'); }
			if (!$this->usuario->existe) { $this->SendResponse(401, 'No hay usuario acreditado.',1); throw new Exception('Usuario no loggeado'); }
			$result = true;
		} catch(Exception $e) {
			$this->SetError($e);
		}
		return $result;
	}
/**
* Summary. Convierte el contenido de php://input en un objeto PHP si se puede.
*/
	private function ParseBody() {
		global $sysParams;
		if (empty($this->body)) { return; }
		try {
			if ($this->encodeContent) {
				if (!empty($this->encodeContentPassword)) {
					$this->body = DecypherAES($this->body, $this->encodeContentPassword);
					if (empty($this->body)) {
						$this->msgerr = 'Cifrado de body incorrecto.';
						$this->SendResponse(400,$this->msgerr,105);
						throw new Exception($this->msgerr);
					}
				} else {
					$this->msgerr = 'El servidor no está configurado para recibir mensajes cifrados.';
					$this->SendResponse(500,$this->msgerr,104);
					throw new Exception($this->msgerr);
				}
			}
			if (IsJsonEx($this->body)) {
				$this->parsedBody = json_decode($this->body, false, 512, JSON_BIGINT_AS_STRING);
				$json_error = json_last_error();
				if ($json_error != JSON_ERROR_NONE) {
					$this->parsedBody = new stdClass();
					$this->msgerr = 'Json no es válido: '.(isset(JSON_ERRORS[$json_error])?JSON_ERRORS[$json_error]:'Error de conversión JSON');
					$this->SendResponse(406,$this->msgerr,12);
					throw new Exception($this->msgerr);
				}
				$this->params = array_merge($this->params, (array)$this->parsedBody	);
			} else {
				$aux = array();
				parse_str($this->body, $aux);
				if (CanUseArray($aux)) {
					$this->parsedBody = json_decode(json_encode($aux));
					$this->params = $this->params+$aux;
				}
			}
		} catch(Exception $e) {
			$this->SetError($e);
		}
	}
/**
* Summary. Determina si el cuerpo del mensaje está cifrado mirando si el cliente envió la cabecera correcta.
* @note Esto modifica la propiedad ->encodeContent apropiadamente, no decifra el cuerpo.
*/
	private function IsEncoded() {
		$this->encodeContent = false;
		try {
			$headers = array_change_key_case($this->headers);
			if (isset($headers['content-encoding'])) { // La cabecera indicado contenido cifrado, según el RFC 8188. Debe valer el algoritmo de cifrado usado para cifrar el cuerpo del mensaje, sin embargo nosotros solo aceptamos uno, el declarado en la constante CIPHER_METHOD
				if (strtoupper(trim($headers['content-encoding'])) == strtoupper(CIPHER_METHOD)) {
					$this->encodeContentPassword = $this->usuario->GetBodyEncodingKey();
					$this->encodeContent = true;
				} else {
					$this->SendResponse(412,'No entendemos el algoritmo de cifrado',103); die();
				}
			}
		} catch(Exception $e) {
			$this->SetError($e);
		}
	}
/**
* Summary. Extrar el ID de transacción de la cabecera enviada por el cliente.
*/
	private function GetTransId() {
		$this->transId = null;
		try {
			$headers = array_change_key_case($this->headers);
			if (!empty($headers['transaction-id'])) { // La cabecera con el ID de transacción.
				$this->transId = substr(trim($headers['transaction-id']),0,64); // Que no se zarpen con el tamaño.
			}
		} catch(Exception $e) {
			$this->SetError($e);
		}
	}
/**
* Summary. Extrar el ID del usuario del backend enviado desde e backend.
*/
	private function GetBackendUserId() {
		$this->backendUserId = null;
		try {
			$headers = array_change_key_case($this->headers);
			if (!empty($headers['backend-id'])) { // La cabecera con el ID del usuario.
				//$this->SetLog(__METHOD__ ." backend-id: ".$headers['backend-id']);
				$this->backendUserId = substr(trim($headers['backend-id']),0,12); // Que no se zarpen con el tamaño.
			}
		} catch(Exception $e) {
			$this->SetError($e);
		}
	}
/**
* Summary. Extraer un header arbitrario.
* @param string $header el nombre del header.
* @return string/null
*/
	public function GetHeader($header) {
		$result = null;
		try {
			$headers = array_change_key_case($this->headers);
			$header = strtolower(trim($header));
			if (!empty($headers[$header])) { // La cabecera con el ID del usuario.
				//$this->SetLog(__METHOD__ ." backend-id: ".$headers['backend-id']);
				$result = trim($headers[$header]); // Que no se zarpen con el tamaño.
			}
		} catch(Exception $e) {
			$this->SetError($e);
		}
		return $result;
	}
/**
* Summary. Atiende las excepciones elevadas en los métodos de esta clase.
* @param exception $e Un objeto de tipo exception.
*/
	function SetError(Exception $e) {
		$this->msgerr = $e->GetMessage();
		$this->error = true;
		$trace = debug_backtrace();
		$caller = @$trace[1];
		$file = @$trace[0]['file'];
		if (strpos($file, DIR_BASE) !== false) {
			$file = substr_replace($file, '', strpos($file, DIR_BASE), strlen(DIR_BASE));
		}
		$line = sprintf('%s:%s %s%s', $file, @$e->GetLine(), ((isset($caller['class']))?$caller['class'].'->':null), @$caller['function']);
		$line .= ' '.$this->msgerr;
		$error_level = LGEV_WARN;
		if (DEVELOPE and $this->DebugOutput) { EchoLogP(htmlentities($line)); }
		$this->SetLog($line, $error_level);
	}
/**
* Summary. Alias de function SetError()
*/
	function SetErrorEx(Exception $e) {
		$this->SetError($e);
	}
/**
* Summary. Devuelve, si encuentra, el parámetro apuntado, o el primero que encuentra de la lista.
* @param mixed $param Nombre del parámetro buscado o una lista (array) de nombres.
* @return mixed.
*/
	public function GetParam($param) {
		$result = null;
		$this->param_index = null;
		if (empty($param)) { return null; }
		if (is_scalar($param)) {
			$param = array((string)$param);
		}
		if (is_array($param) and (count($param) > 0) and (count($this->params)>0)) {
			foreach($param as $this->param_index) {
				if (isset($this->params[$this->param_index])) {
					$result = $this->params[$this->param_index];
					break; 
				}
			}
		}
		return $result;
	}
	public function GetParams($param) {
		return $this->GetParam($param);
	}

/**
* Summary. Hace lo mismo que ->GetParam pero en caso que el parámetro sea encontrado, lo elimina de la lista de parámetros.
* @param mixed $param Nombre del parámetro buscado o una lista (array) de nombres.
* @return mixed.
*/
	public function CutParam($param) {
		if ($result = $this->GetParam($param)) {
			unset($this->params[$this->param_index]);
		}
		return $result;
	}

/**
* Summary. Fuerza la codificación de los strings del array pasado como parámetro a UTF-8.
* @param array/string $data.
* @return array/string.
* @note Este método se llama recursivamente.
*/
	private function EnsureUTF8($data){
		$result = $data;
		if(is_array($result) AND CanUseArray($result)){
			foreach($result as $key => $value){
				if(is_string($value)){
					$result[$key] = mb_convert_encoding($value,"UTF-8",mb_detect_encoding($value));
					continue;
				}
				if(is_array($value) AND count($value) > 0){
					$result[$key] = $this->EnsureUTF8($value);
				}
			}			
		}
		
		if(is_string($result)){
			$result = mb_convert_encoding($result,"UTF-8",mb_detect_encoding($result));
		}
		return $result;
	}
/**
* Summary. Escribir una línea en el log de eventos.
*/
	private function SetLog($linea, $error_level) {
		if (($this->log_max_length > 0) and (mb_strlen($linea) > $this->log_max_length)) {
			$linea = mb_substr($linea, 0, $this->log_max_length).'<...truncado...>';
		}
		cLogging::Write($linea, $error_level);
	}
} // Class.