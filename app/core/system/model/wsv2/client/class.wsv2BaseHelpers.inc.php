<?php
/*
	Clase fundacional del cliente del web service.
	Esto contiene métodos y propiedades básicas pero no accesos al ws o llamadas a cURL.

	Rebrit SRL - Chacabuco 59 - Gualeguaychú - Entre Ríos.
	email: info@rebrit.ar

*/
if (!defined('http_codes')) {
	require_once(DIR_includes."wsclient.msgs.php"); // Aquí están los mensajes de error.
}

require_once(DIR_includes."class.logging.inc.php"); // Aquí están las constantes de logging.

class cV2Base {

	public $userAgent = 'Rebrit cURL Client 2.0';
	public $baseURL = 'http://localhost/';
	public $logdir = WS_LOG;
	public $echo_debug = WS_ECHO_LOG;
	public $log_file_name = WS_LOG_PREFIX.'.log';
	public $log_max_length = 0;
	public $msgerr = '';
	public $intl_nroerr = 0;
	public $curl_nroerr = 0;
	public $http_nroerr = 0;
	public $json_nroerr = 0;
	public $core_nroerr = 0;
	public $acceptCookies = true;
	public $cookiesjar = null; // Path al archivo de las cookies.
	public $error = false;

	function __construct() {
		
	}


	public function SetLog($linea, $error_level = LGEV_DEBUG) {
		if (strpos($linea, DIR_BASE) !== false) {
			$linea = substr_replace($linea, '', strpos($linea, DIR_BASE), strlen(DIR_BASE));
		}

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
		
		if (($this->log_max_length > 0) and (mb_strlen($linea) > $this->log_max_length)) {
			$linea = mb_substr($linea,0,$this->log_max_length).'<...truncado...>';
		}

		$linea = '['.Date('Y-m-d H:i:s').'] '.$error_level.' '.$linea.PHP_EOL;
		return file_put_contents($archivo, $linea, FILE_APPEND);
	}

	protected function SetError(Exception $e) {
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
		$this->SetLog($line, LGEV_WARN);
	}
	
	public function SetErrorEx(Exception $e) {
		$this->SetError($e);
	}

	/**
	* Summary. Crea y mantiene el directorio temporal donde almacenar datos de la sesión de usuario del backend cuando interactúa con el core.
	*/
	public function SetTempDir(string $username = null) {
		$result = DIR_temp;
		if (!empty($username)) {
			$theDir = EnsureTrailingSlash(DIR_temp.$username);
			if (!ExisteCarpeta($theDir)) {
				cSideKick::EnsureDirExists($theDir);
			}
			$this->userDir = $theDir;
			$result = $theDir;
		}
		return $result;
	}
	/**
	* Summary. Obtener las cookies almacenadas en el directorio del usuario del backend.
	*/
	public function GetUserCookies() {
		$theFile = $this->userDir."cookiesjar.txt";
		if (!ExisteArchivo($theFile)) {
			file_put_contents($theFile,'');
		}
		return $theFile;
	}
} // Class.

