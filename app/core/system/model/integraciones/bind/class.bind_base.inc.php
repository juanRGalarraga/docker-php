<?php
/*
	Clase para encapsular el web service de Bind.
	Created: 2020-09-18
	Author: Alcides
*/

define("BIND_WSURL","https://sandbox.bind.com.ar/v1/"); // La base de la URL del WS de Bind.
define("BIND_TIMEOUT",20); // Cuántos segundos esperar hasta morir la conexión.
define("BIND_LOGFILE","bind.log"); // Cómo se llama el archivo que guardará el log total de la conexión.
//define("BIND_REFERER","http://sephiroth-ii/developement_machine");
define("BIND_sesname","bind");

require_once(DIR_model."class.sysparams.inc.php");
require_once(DIR_model."class.apirest_base.inc.php"); // Aquí están los mensajes de error.
require_once(DIR_model."class.fundation.inc.php");
require_once(DIR_integraciones."class.integraciones.inc.php");


class cBindBase extends cAPIRestBase {
	
	public $url = '';
	public $username = '';
	public $password = '';
	public $debug_level = 1;
	public $baseURL = BIND_WSURL;
	public $final_url = null;
	public $use_ssl = false;
	public $ssl_cert_password = null;
	public $ssl_cert_file = null;
	public $ssl_key_file = null;
	public $referer = null;
	private $curl_defaultoptions = array(
		CURLOPT_HEADER => false,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_TIMEOUT => BIND_TIMEOUT
	);
	private $link;
	private $curl_options = array();
	//private $token_type;
	private $token;
	private $expire;
	public $paginas = 0;
    public $estado;
	public $tabla_log = null;
	public $stress = false;
	public $file_stress = null;
	public $dblog = null;
	public $responseType = 'object';

	function __construct($username = null, $password = null) {
		global $sysParams;
		$this->log_file_name = BIND_LOGFILE;
		parent::__construct();
		if (version_compare(PHP_VERSION, '5.4.0','<')) {
			$this->SetINTERNALError(1);
			return;
		}
		if (!function_exists('curl_init')) {
			$this->SetINTERNALError(2);
			return;
		}

		$this->tabla_log = (defined("BIND_LOGTABLE"))?BIND_LOGTABLE:null;
		// Set username
		if (defined("BIND_USERNAME")) { $this->username = BIND_USERNAME; }
		$this->username = $sysParams->Get('bind_api_username',$this->username);
		if (!empty($username)) { $this->username = $username; }
		if (empty($this->username)) { $this->SetLog(__METHOD__." Cuidado, nombre de usuario está vacío."); }

		// Set password
		if (defined("BIND_PASSWORD")) { $this->password = BIND_PASSWORD; }
		$this->password = $sysParams->Get('bind_api_password',$this->password);
		if (!empty($password)) { $this->password = $password; }
		if (empty($this->password)) { $this->SetLog(__METHOD__." Cuidado, la contraseña está vacía."); }
		
		// Set URL
		if (defined("BIND_WSURL")) { $this->baseURL = BIND_WSURL; }
		$this->baseURL = $sysParams->Get('bind_api_url',$this->baseURL);
		if (empty($this->baseURL)) { $this->SetLog(__METHOD__." Cuidado, la URL está vacía."); }
		
		$this->token = @$_SESSION[BIND_sesname]['token'];
		$this->expire = @$_SESSION[BIND_sesname]['expire_in'];
		if ($sysParams->Get('bind_use_ssl',false))	$this->SetSSL();
		$this->dblog = new cIntegracion('bind_log');
	} // __constructor

/**
* Summary. Fuerza a leer la configuración de SSL.
*/
	public function SetSSL() {
		global $sysParams;
		$this->use_ssl = true;
		$this->ssl_cert_file = $this->GetSSLFiles($sysParams->Get('bind_ssl_cert_file',null));
		$this->ssl_key_file = $this->GetSSLFiles($sysParams->Get('bind_ssl_key_file',null));
		$this->ssl_cert_password = $sysParams->Get('bind_ssl_password',null);
	}


	public function Commit($type = 'GET', $data = array()) {
		$result = true;
		$this->errores = '';
		if ($this->responseType == 'object') {
			$this->parsed_response = new stdClass;
		} else {
			$this->parsed_response = array();
		}
		if (empty($this->token) or ($this->expire <= time())) {
			if(!$this->GetToken()) {
				return false;
			}
		}
		try {
			
			$this->final_url = $this->baseURL.$this->url;
			$this->paginas = 0;
			
			$params = (!empty($data) && isset($data['obp_document_type']))? 'obp_document_type: '.$data['obp_document_type']:NULL;
			
			$this->curl_options = array(
				CURLOPT_USERAGENT => 'RebritGen cURL 1.2',
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_REFERER => (empty($this->referer))?API_referer:$this->referer,
				CURLOPT_URL => $this->final_url,
				CURLOPT_HTTPHEADER => array('Content-type: application/json;charset=UTF-8','Accept: application/json', 'Authorization: JWT '.$this->token, $params),
				CURLOPT_CUSTOMREQUEST => $type,
			);
			
			if ($this->use_ssl) {
				$this->curl_options[CURLOPT_SSL_VERIFYPEER] = false;
				$this->curl_options[CURLOPT_SSLCERT] = $this->ssl_cert_file;
				$this->curl_options[CURLOPT_SSLKEY] = $this->ssl_key_file;
				$this->curl_options[CURLOPT_SSLCERTPASSWD] = $this->ssl_cert_password;
			}
			
			if (in_array($type, array('POST','PUT'))) {
				if (CanUseArray($data)) {
					//$this->curl_options[CURLOPT_POSTFIELDS] = http_build_query($data['body'],'','&');
					$this->curl_options[CURLOPT_POSTFIELDS] = json_encode($data['body']);
					/*if(!empty($data['body']) && isset($data['body'])) $this->curl_options[CURLOPT_POSTFIELDS] = json_encode($data['body']);
					else $this->curl_options[CURLOPT_POSTFIELDS] = http_build_query($data,'','&');*/
				}
			}
			
			$result = $this->ExecCall();
			$a = json_decode($this->response,($this->responseType != 'object'));
			$result = !$this->SetJSONError(json_last_error());
			$this->parsed_response = $a;
			if (($this->debug_level > 0) and ($result)) {
				$this->SetLog(__LINE__." ".__METHOD__.PHP_EOL.print_r($this->parsed_response,true)); 
			}
			

		} catch(Exception $e) {
			$this->SetError(__FILE__,__METHOD__,$e->getMessage());
		}

		return $result;
	}

	public function GetToken() {
		$result = true;
		$this->token = '';
		$this->expire = 0;
		$this->final_url = $this->baseURL.'login/jwt';
		$body = json_encode(
			array('username'=>$this->username,'password'=>$this->password,
			JSON_HACELO_BONITO)
		);
		
		if ($this->debug_level > 1) {
			$this->SetLog(__METHOD__." Usando URL: ".$this->baseURL." Usuario: ".$this->username." Contraseña: ".$this->password);
		}
		
		$this->curl_options = array(
			CURLOPT_USERAGENT => 'RebritGen cURL 1.2',
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_AUTOREFERER => true,
			CURLOPT_REFERER => (empty($this->referer))?API_referer:$this->referer,
			CURLOPT_URL => $this->final_url,
			CURLOPT_CUSTOMREQUEST => 'POST', // GET POST PUT PATCH DELETE HEAD OPTIONS 
			CURLOPT_HTTPHEADER => array('Content-Type: application/json'),
			CURLOPT_POSTFIELDS => $body
		);
		if ($this->use_ssl) {
			$this->curl_options[CURLOPT_SSL_VERIFYPEER] = false;
			$this->curl_options[CURLOPT_SSLCERT] = $this->ssl_cert_file;
			$this->curl_options[CURLOPT_SSLKEY] = $this->ssl_key_file;
			$this->curl_options[CURLOPT_SSLCERTPASSWD] = $this->ssl_cert_password;
		}
		
		if ($this->debug_level > 0) {
			$this->SetLog(__METHOD__.": ".print_r($this->curl_options, true));
		}
		$result = $this->ExecCall();
		if ($result) {
			$a = json_decode($this->response,true);
			$this->parsed_response = $a;
			if ($this->SetJSONError(json_last_error()) == false) {
				//$this->token_type = @$a['token_type'];
				$this->token = @$a['token'];
				$expires_in = @$a['expires_in'];
				$this->expire = time()+$expires_in;
				//$_SESSION[BIND_sesname]['token_type'] = $this->token_type;
				$_SESSION[BIND_sesname]['token'] = $this->token;
				$_SESSION[BIND_sesname]['expire_in'] = $this->expire;
			} // if
			else {
				$result = false;
			}
		}
		return $result;
	}

	
	private function ExecCall() {
		$result = true;
		try {
			$this->parsed_response = array();
			$this->errores = '';
			$this->link = curl_init();
			curl_setopt_array($this->link, ($this->curl_options + $this->curl_defaultoptions));
			
			$this->dblog->Add(['request'=>json_encode(($this->curl_options + $this->curl_defaultoptions))]);

			$this->SetLog(__METHOD__." URL: ".$this->final_url);
			if (!$this->stress or is_null($this->file_stress)) {
				$this->response = curl_exec($this->link); // Exec!
			} else {
				$this->response = file_get_contents($this->file_stress);
				$this->http_nroerr = 200;
			}
			if ($this->debug_level > 0) {
				if ($this->debug_level > 1) {
					$this->SetLog(__LINE__." ".__METHOD__." RAW Response: ".$this->response);
				} else {
					$this->SetLog(__LINE__." ".__METHOD__." RAW Response: ".mb_substr($this->response,0,2048));
				}
			}
			if (!$this->stress or is_null($this->file_stress)) {
				if ($this->SetCURLError($this->link) == false) { // Si no hubo error en cURL...
					$this->SetHTTPError($this->link);
					if ($this->http_nroerr >= 400) {
						$result = false;
					}
				} else {
					$result = false;
				}
			}
			$this->dblog->Add(['response'=>$this->response,'http_status'=>$this->http_nroerr]);
		} catch (Exception $e) {
			$this->SetError(__FILE__,__METHOD__,$e->getMessage());
		}
		@curl_close($this->link);
		return $result;
	}
/**
* Summary. Determinar si un archivo existe en el repositorio de certificados SSL y devolver su ruta absoluta en caso de encontrarlo.
* @param string $archivo El nombre del archivo.
* @return string/null.
*/
		private function GetSSLFiles(string $archivo):?string {
			if (empty($archivo)) { return null; }
			$result = null;
			try {
				if (defined("SSL_CERT_FILES_DIR")) {
					$archivo = EnsureTrailingSlash(SSL_CERT_FILES_DIR).$archivo;
				}
				$archivo = LoadConfig($archivo);
				if (!ExisteArchivo($archivo)) { throw new Exception("Archivo SSL '".$archivo."' no encontrado."); }
				$result = $archivo;
				if ($this->debug_level > 0) { $this->SetLog('Leído archivo SSL: '.$archivo); }
			} catch(Exception $e) {
				$this->SetError(__FILE__,__METHOD__,$e->getMessage());
			}
			return $result;
		}
}