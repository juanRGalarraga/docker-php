<?php
/*
	Clase para lidiar con la sesión de usuario del backend.
	Created: 2021-09-21
	Author: DriverOp
*/

require_once(DIR_includes."class.logging.inc.php");
require_once(DIR_includes."class.fechas.inc.php");
require_once(DIR_includes."cypher.inc.php");
require_once(DIR_model."class.fundation.inc.php");

defined("SESSION_ID_KEY") || define("SESSION_ID_KEY", "Dos más tres no siempre da 5");

class cUsrSession extends cModels {

	const tabla_sesiones = TBL_backend_sesiones;

	private $LogDebug = false;
	public $qMainTable = '';
	public $sessionName = null;
	public $sessionTime = 3600;

	public function __construct() {
		parent::__construct();
		$this->mainTable = self::tabla_sesiones;
		$this->qMainTable = SQLQuote(self::tabla_sesiones);
		$this->ResetInstance();
		$this->sessionName = USR_SESSION_NAME;
		$this->LogDebug = DEVELOPE;
	}

/**
* Summary. Devuelve la sesión activa del usuario que se pasa como parámetro.
* @param int $id El id de la sesión del usuario (NO es el id del usuario).
* @return object/null
*/
	public function Get(int $id = null):? object {
		$result = null;
		if (empty($id)) { $id = $this->id??null; }
		if (empty($id)) { return null; }
		$sql = "SELECT * FROM ".$this->qMainTable." WHERE `id` = ".$id." AND `estado` > 0 ORDER BY `sys_fecha_alta` DESC LIMIT 1;";
		if ($result = $this->FirstQuery($sql)) {
			return $result;
		}
		return $result;
	}

/**
* Summary. Renovar la sesión del usuario indicado.
* @return object/null
*/
	public function Renew():? object {
		if (empty($this->id)) { throw new Exception('No hay una sesión activa, usar ->Get()'); }
		$this->Update(self::tabla_sesiones,['idle'=>date("U")]," `id` =".$this->id);
		$this->EncodeId($this->id);
		return $this->Get($this->id);
	}
/**
* Summary. Determina si existe la cookie de sesión y extrae su valor.
* @result int/null
*/
	public function Exists() {
		if ($this->GetPersistance()) {
			if ($this->id = $this->DecodeId()) {
				return $this->Get();
			} else {
				$this->DoLog(__LINE__ ." ID de sesión no pudo ser decodificada.");
			}
		} else {
			$this->DoLog(__LINE__ ." Cookie no está seteada.");
		}
		return false;
	}
/**
* Summary. Inicia la sesión del usuario actual.
* @param int $usuario_id El id del usuario a loggearse.
* @return object/null
*/
	public function Login(int $usuario_id = 0) {
		$result = false;
		$this->Update(self::tabla_sesiones,array('estado'=>0),"`usuario_id` = ".$usuario_id); // Vencer cualquier otra sesión que el usuario tenga abierta.
		$reg = array(
			'usuario_id'=> $usuario_id,
			'sys_fecha_alta'=>cFechas::Ahora(),
			'estado'=>1,
			'navegador'=>$_SERVER['HTTP_USER_AGENT']??$_SERVER['USERDOMAIN'],
			'idle'=>Date('U'),
			'ip'=>GetIp()
		);
		if ($this->Insert(self::tabla_sesiones,$reg)) {
			$this->EncodeId($this->last_id);
			$result = $this->Get($this->last_id);
		}
		return $result;
	} // Login

/**
* Summary. Termina la sesión del usuario.
*/
	public function Logout() {
		$result = null;
		if (empty($this->id)) {
			$this->id = $this->DecodeId();
		}
		if (empty($this->id)) { cLogging::Write(__FILE__ ." ".__LINE__ ." No hay ninguna sesión activa."); return null; }
		$this->Update(self::tabla_sesiones, ['estado'=>0], "`id` = ".$this->id);
	}

/**
* Summary. Sabe dónde guardar el dato persistente de la sesión.
*/
	private function SetPersistance($value) {
		if (defined("SESSION_MODE") and strtolower(SESSION_MODE) == 'cookie') {
			setcookie($this->sessionName, $value, array(
				'expires' => time()+$this->sessionTime,
				'path' => '/',
				'domain' => 'localhost',
				'secure' => isHttps(), // Está definido en initialize.php
				'httponly' => true,
				'samesite' => 'lax'
			));
		} else {
			unset($_SESSION[$this->sessionName]);
			$_SESSION[$this->sessionName] = $value;
		}
	}
/**
* Summary. Sabe cómo recuperar la sesión guardada.
*/
	private function GetPersistance() {
		$var = (defined("SESSION_MODE") and strtolower(SESSION_MODE) == 'cookie')
				? $_COOKIE[$this->sessionName] ?? null 
				: $_SESSION[$this->sessionName] ?? null;
		return $var;
	}

/**
* Summary. Cifra el ID de la sesión actualmente activa.
* @param int $id El ID de la sesión (NO ES el id del usuario).
*/
	private function EncodeId($id) {
		$this->SetPersistance(CypherAES($id??null, SESSION_ID_KEY));
	}

/**
* Summary. Decodifica el ID de la sesión actualmente activa (si existe).
* @return int/null
*/
	private function DecodeId() {
		return DecypherAES($this->GetPersistance(), SESSION_ID_KEY);
	}
/**
* Summary. Escribe en en log un mensaje para debug.
* @param string $msg El mensaje a escribir.
*/
	private function DoLog(string $msg = '') {
		if (!$this->LogDebug) { return; }
		if (!empty($this->username)) {
			$msg = $this->username." ($this->id) ".$msg;
		}
		$trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS,2);
		if (isset($trace[1])) {
			$msg = $trace[1]['function'].": ".$msg;
		}
		cLogging::Write(__FILE__ ." ".$msg, LGEV_DEBUG);
	}
} // class
