<?php
/*
	Manejo de los usuarios del web service.
	Created: 2020-10-01
	Author: DriverOp
	
	MOdif: 2021-02-05
	Author: DriverOp
	Desc:
		- Agregado método ->ProcessHeaders() para procesar los encabezados de la petición en caso que el cliente quiera indicar cosas de configuración extra para el usuario actual.

	Update: 2021-05-06
	Author: DriverOp
	Desc:
		 Derivación a la versión 2.0 del Core.
*/
require_once(DIR_model."class.sysparams.inc.php");
require_once(DIR_model."class.jwtbase.inc.php");
require_once(DIR_model."class.fundation.inc.php");

class cWsUsuario extends cModels {
	
	const tabla_usuarios = TBL_ws_usuarios;

	public $secretKey = '';
	public $expireTime = 0;
	public $sessionTime = 3600;
	/*
	private $tabla_usuarios_permisos = TBL_ws_usuarios_permisos;
	private $tabla_negocios = TBL_config_negocios;
	*/
	private $jwt = null;
	public $msgerr = null;
	public $token = null;
	public $DebugOutput = false;
	public $usuario_id = null;
	
	
	function __construct() {
		parent::__construct();
		$this->mainTable = self::tabla_usuarios;
		$this->actual_file = __FILE__;
		$this->jwt = new cJWT_base();
		$this->jwt->DebugLevel = 0;
		$this->DebugOutput = false;
		$this->msgerr = null;
		$this->ResetInstance();
	}

/**
* Summary. Obtener un usuario a partir de su ID.
* @param int $id El ID a buscar.
* @return object.
*/
	public function Get(int $id=null):?object {
		$result = null;
		try {
			if (empty($id)) { throw new Exception(__LINE__." ID debe ser un número."); }
			$this->sql = "SELECT *,`config`->>'$.tSession' AS `tSession` FROM ".SQLQuote(self::tabla_usuarios)." AS `usuarios` WHERE `usuarios`.`id` = ".$id;
			$result = parent::Get($id);
		} catch(Exception $e) {
			$this->SetErrorEx($e);
		}
		return $result;
	}
/**
* Summary. Establece la clave de cifrado de token a partir de la configuración del sistema.
*/
	public function SetSecretUserKey() {
		global $sysParams;
		$this->secretKey = $sysParams->Get('ws_users_secret_key','no key');
	}
/**
* Summary. Busca en las cabeceras HTTP el token de autorización.
* @return bool 
* @note Esto puede planchar el usuario actual si existe.
*/
	public function ExtractToken() {
		$result = false;
		try {
			$this->token = '';
			$headers = array_change_key_case(getallheaders());
			if (!isset($headers['authorization'])) { throw new Exception('No se encontró cabecera Authorization.'); }
			if (strtolower(substr($headers['authorization'],0,6)) != 'bearer') { throw new Exception('Authorization no es de tipo bearer.'); }
			$theToken = substr($headers['authorization'],7);
			$this->token = trim($theToken);
			$result = true;
		} catch(Exception $e) {
			$this->SetErrorEx($e);
		}
		return $result;
	}

/**
* Summary. Obtener un usuario a partir de su nombre de usuario.
* @param string $username El nombre de usuario buscado.
* @return object.
*/
	public function GetByUsername($username):?object {
		$result = null;
		$this->encontrado = false;
		try {
			if (empty($username)) { throw new Exception(__LINE__." No se indicó nombre de usuario."); }
			$username = $this->RealEscape(mb_substr($username,0,32));
			
			$sql = "SELECT *,`config`->>'$.tSession' AS `tSession` FROM ".SQLQuote(self::tabla_usuarios)." WHERE `username` = '".$username."'";
			$this->parseRecord = true;
			if ($result = $this->FirstQuery($sql)) {
				$this->encontrado = true;
				if (!empty($this->tSession) and !is_null(SecureInt($this->tSession))) {
					$this->sessionTime = $this->tSession;
				}
			}
		} catch(Exception $e) {
			$this->SetErrorEx($e);
		}
		return $result;
	}
/**
* Summary. Compara la contraseña del usuario actual con la proporcionada para determinar si es válida.
* @param string $password La contraseña a validar.
* @return bool.
* @note Por ahora las contraseñas se almacenan sin cifrar en la tabla de usuarios.
*/
	public function ValidPass($password):bool {
		$result = false;
		try {
			if (!$this->existe) { throw new Exception(__LINE__." Usuario no establecido."); }
			$result = ($password == $this->password);
		} catch(Exception $e) {
			$this->SetErrorEx($e);
		}
		return $result;
	}

/**
* Summary. Fabríca el token para enviar al cliente del WS.
* @return string.
*/
	public function MakeToken():string {
		$this->jwt->secretKey = $this->secretKey;
		$this->expireTime = time()+$this->sessionTime;
		$message = [
			'ident'=>$this->id,
			'expire'=>$this->expireTime
		];
		if (!empty($this->usuario_id)) {
			$message['usuario_id'] = $this->usuario_id;
		}
		return $this->jwt->GenerateToken($message);
	}

/**
* Summary. A partir de un token JWT, determina a qué usuario corresponde.
* @param string $token default empty es el token JWT enviado por el cliente.
* @return bool.
* @note Devuelve true si se encontró un usuario válido a partir del token. Los datos de ese usuario quedan como propiedades del objeto actual. Si no se pasa un token como parámetro se usa la propiedad token.
*/
	public function GetByToken(string $token = ''):bool  {
		$result = false;
		try {
			if (empty($token)) { $token = $this->token; }
			if (empty($token)) { throw new Exception(__LINE__." Token vacío."); }
			$this->jwt->secretKey = $this->secretKey;
			if ($this->jwt->VerifyToken($token)) {
				$this->jwt->GetMessage($token);
				if (!empty($this->jwt->message->ident)) {
					if ($this->Get($this->jwt->message->ident)) {
						$result = true;
						$this->usuario_id = @$this->jwt->message->ident;
					} else {
						cLogging::Write(__CLASS__ ." ".__METHOD__ ." ".__LINE__ ." Se indicó un ID de usuario inexistente.", LGEV_WARN);
					}
				} else {
					throw new Exception(__LINE__." No se indicó ident.");
				}
			} else {
				throw new Exception(__LINE__." ".$this->jwt->msgerr);
			}
		} catch(Exception $e) {
			$this->SetErrorEx($e);
		}
		return $result;
	}

/**
*	Summary. Verifica que el tiempo de expiración contenido en el mensaje en el token esté dentro de los límites.
* @return bool
*/
	public function VerifyExpireTime() {
		$result = false;
		try {
			if (isset($this->jwt->message->expire)) {
				if (is_int($this->jwt->message->expire)) {
					if ($this->jwt->message->expire >= time()) {
						$result = true;
					}
				}
			}
		} catch(Exception $e) {
			$this->SetErrorEx($e);
		}
		return $result;
	}
	/*
	public function tienePermiso($id) {
		$result = true;
		try {
			if ($this->existe) {
				$sql = "SELECT `permiso` FROM ".SQLQuote($this->tabla_usuarios_permisos)." WHERE `ws_contenido_id` = ".$id." AND `ws_usuario_id` = ".$this->id." LIMIT 1;";
				if ($fila = $this->FirstQuery($sql)) {
					$result = ($fila['permiso'] == 'SI');
				}
			}
		} catch(Exception $e) {
			$this->SetError(__METHOD__,$e->getMessage());
		}
		return $result;
	}
	*/

/**
* Summary. Devuelve la clave de cifrado del cuerpo del mensaje. Primero tratando de obtenerla del usuario actual, luego de la configuración global.
* @return string la clave buscada o vacío en caso de no encontrarla.
*/
	public function GetBodyEncodingKey():?string {
		global $sysParams;
		$result = '';
		try {
			if ($this->existe) {
				if (!empty($this->bodyencodingkey)) {
					$result = $this->bodyencodingkey;
				} else {
					$result = $sysParams->Get('body_encoding_key','');
				}
			} else {
				$result = $sysParams->Get('body_encoding_key','');
			}
		} catch(Exception $e) {
			$this->SetError(__METHOD__,$e->getMessage());
		}
		return $result;
	}
/**
* Summary. Atiende las excepciones elevadas en los métodos de esta clase.
* @param exception $e Un objeto de tipo exception.
*/
	function SetErrorEx(Exception $e) {
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
		cLogging::Write($line, $error_level);
	}

/*
	public function GetByUsername($username) {
		$result = false;
		try {
			if () { throw new Exception(__LINE__." ."); }
		} catch(Exception $e) {
			$this->SetError(__METHOD__,$e->getMessage());
		}
		return $result;
	}
*/

} // Class
