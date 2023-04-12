<?php
/*
	Clase base de la implementación del cliente de InfoBip.
	Created: 2020-01-05
	Author: Emiliano Mirkisich
	
	Modif: 2020-01-09
	Author: DirverOp
	Desc:
		Hacer que username, password, url, templateid y appid sean propiedades públicas para no depender de constantes globales dentro del código de la clase.
		Reformado el código para tener en cuenta estas propiedades.
	
	Modif: 2020-11-23
	Author: DriverOp
	Desc:
		Hacer el estado HTTP una propiedad pública $http_code.
		Hacer la URL destino una propiedad pública $base_url.
		Convertir en dinámicas todos los métodos y propiedades estáticos.
		Agregado log básico.

	Modif: 2021-02-23
	Author: Gastón Fernandez
	Desc:
		Movido a nueva carpeta y cambiados metodos, ahora todos los metodos estan en una sola clase y otros arreglos a metodos ya existentes.
		Ahora el curl lo hace un solo metodo.
		Agregado metodo Enviar() para poder derivar a una nueva clase
*/

if (!defined("INFOBIP_LOG_FILE")) {
	define("INFOBIP_LOG_FILE","infobip_sms");
}

require_once(DIR_integraciones."class.integraciones.inc.php");

class IBBase
{
	private $IBSSOToken = null;
	public $base_url = 'http://localhost';
	public $appid = '';
	public $templateid = '';
	public $username = '';
	public $password = '';
	public $http_code = null;
	public $modo_test = 1;//Modo test o no
	public $err = '';
	public $logfile = '';
	public $dblog = null; // Objeto con la instancia de integraciones para hacer el log.
	public $curl_options = array();

	function __construct() {
		$this->logfile = "-".(defined("INFOBIP_LOG_FILE"))?INFOBIP_LOG_FILE:"infobip_sms";
		$this->dblog = new cIntegracion('infobip_log');
	}

	private function obtenerBasicToken() {
		return base64_encode($this->username . ":" . $this->password);
	}

	private function obtenerIBSSOToken() {
		$result = $this->IBSSOToken;
		if (is_null($result)) {
			try {
				$curl = curl_init();
				
				$url = $this->base_url . "/auth/1/session";
				
				$this->curl_options = array(
					CURLOPT_URL => $url,
					CURLOPT_RETURNTRANSFER => true,
					CURLOPT_ENCODING => "",
					CURLOPT_MAXREDIRS => 10,
					CURLOPT_TIMEOUT => 30,
					CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
					CURLOPT_CUSTOMREQUEST => "POST",
					CURLOPT_POSTFIELDS => '{ "username":"'.$this->username.'", "password":"'.$this->password.'" }',
					CURLOPT_HTTPHEADER => array(
					"Content-Type: application/json"
					)
				);
	
				curl_setopt_array($curl, $this->curl_options);
				
					curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
					curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
	
				$this->dblog->Add(['request'=>json_encode($this->curl_options)]);
	
				$response = curl_exec($curl);
				$this->err = curl_error($curl);
				$this->http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

	
				curl_close($curl);
				$this->dblog->Add(['response'=>$response,'http_status'=>$this->http_code]);
				if ($this->err) {
					throw new IBTokenExcepcion("cURL Error #:" . $this->err);
				} else {
					$token = json_decode($response);
					$result = $token->token;
					$this->IBSSOToken = $result;
				}
				$this->DoLog(' URL: '.$url.' Response: '.print_r($response,true));
			} catch (IBExcepcion $e) {
				$this->DoLog($e->getMessage());
				throw new IBExcepcion($e->getMessage());
			}
		}
		return $result;
	}

	public function getAuthenticationHeaderBasic()
	{
		return "Basic " . $this->obtenerBasicToken();
	}

	public function getAuthenticationHeader()
	{
		return "IBSSO " . $this->obtenerIBSSOToken();
	}

	private function Ejecutar($method, $data = null, $url = "/2fa/2/applications"){
		$result = false;
		try {
			$curl = curl_init();
			if(empty($url)){
				$url = "/2fa/2/applications";
			}
	
			if($url[0] != "/"){
				$url = "/".$url;
			}
	
			if($method == 'POST' or $method == 'PUT'){		
				curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
			}else{
				if($data != null AND !empty($data) and is_array($data)){
					$url .= http_build_query($data);
				}
			}
			$final_url = $this->base_url.$url;
			$this->curl_options = array(
				CURLOPT_URL => $final_url,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_ENCODING => "",
				CURLOPT_MAXREDIRS => 10,
				CURLOPT_TIMEOUT => 30,
				CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST => $method,
				CURLOPT_HTTPHEADER => array(
					"Authorization: " . $this->getAuthenticationHeader(),
					"Content-Type: application/json"
				)
			);
			curl_setopt_array($curl, $this->curl_options);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
			
			$this->dblog->Add(['request'=>json_encode($this->curl_options)]);
			
			$response = curl_exec($curl);
			$this->err = curl_error($curl);
			$this->http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
	
			curl_close($curl);
			$this->dblog->Add(['response'=>$response,'http_status'=>$this->http_code]);
			if ($this->err) {
				$this->DoLog(' URL: '.$final_url.' Response: Err: '.print_r($this->err,true));
				throw new IBExcepcion("cURL Error #: $this->err");
			} else {
				$result = json_decode($response);
				$this->DoLog(' URL: '.$final_url.' Response: '.print_r($response,true));
			}
		} catch (IBExcepcion $e) {
			$this->DoLog($e->getMessage());
			throw new IBExcepcion($e->getMessage());
		}

		return $result;
	}

	/**
	 * Summary. Devuelve el estado de la conexión con infobip
	 */
	public function estado()
	{
		$result = false;
		$result = $this->Ejecutar("GET", null, "status");
		return $result;
	}

	/**
	 * Summary. Configura la plantilla del pin
	 */
	private function configurar($data = null)
	{
		if (empty($data)) {
		$data = [
			"name" => "APP Basica", "enabled" => true,
			"configuration" => [
			"pinAttempts" => 10,  //numero de intentos posibles
			"allowMultiplePinVerifications" => true, //indica si se permite multiple verificacion
			"pinTimeToLive" => "15m", //tiempo de vida
			"verifyPinLimit" => "1/3s",  //cantidad de verificaciones en un intervalo de tiempo
			"sendPinPerApplicationLimit" => "10000/1d",  //numero total de solicidudes en un intervalo de tiempo usando una app
			"sendPinPerPhoneNumberLimit" => "1/1d" // numero de solicitudes en un intervalo de tiempo para generar un PIN y enviar sms a un telefono    
			]
		]; 
		}

		return $data;
	}

	/**
	 * Summary. Crea la plantilla del pin
	 */
	public function crear($data = null)
	{
		$result = false;
		try {
			$conf = $this->configurar($data);
			$result = $this->Ejecutar("POST", $conf);
		} catch (IBExcepcion $e) {
			$this->DoLog($e->getMessage());
			throw new IBExcepcion($e->getMessage());
		}
		return $result;
	}

	/**
	 * Summary. Lista las plantillas existentes en infobip
	 */
	public function listar()
	{
		$result = false;
		try {
			$result = $this->Ejecutar("GET");
		} catch (IBExcepcion $e) {
			$this->DoLog($e->getMessage());
			throw new IBExcepcion($e->getMessage());
		}
		return $result;
	}

	/**
	 * Summary. Obtiene la plantilla indicada en $appID
	 */
	public function getApp($appID)
	{
		$result = false;
		try {
			if(!empty($appID)){
				$url = "/2fa/2/applications/".$appID;
				$result = $this->Ejecutar("GET",null, $url);
			}
		} catch (IBExcepcion $e) {
			$this->DoLog($e->getMessage());
			throw new IBExcepcion($e->getMessage());
		}
		return $result;
	}

	/**
	 * Summary. Actualiza la plantilla indicada $appID con los datos de $data
	 */
	public function Actualizar($appID, $data)
	{
		$result = false;
		try {
			$conf = $this->Configurar($data);
			///die(json_encode($data));
			if(!empty($appID)){
				$url = "/2fa/2/applications/".$appID;
				$result = $this->Ejecutar("PUT",$conf,$url);
			}
		} catch (IBExcepcion $e) {
			$this->DoLog($e->getMessage());
			throw new IBExcepcion($e->getMessage());
		}
		return $result;
	}

	/**
	 * Summary. Obtiene la primer plantilla de una lista de plantillas
	 */
	public function obtener()
	{
		$result = false;
		try {
			$rs = $this->listar();
			if (!empty($rs)) {
				$app = $this->getApp($rs[0]->applicationId);
				$result = $app;
			}
		} catch (IBExcepcion $e) {
			$this->DoLog($e->getMessage());
			throw new IBExcepcion($e->getMessage());
		}
		
		return $result;
	}


	/**
	 * Summary. Configura el template utilizada para enviar PINs a los usuarios
	 */
	private function configurarTemplate($data = null)
  	{
		if (empty($data)) {
		$data = [
			"pinType" => "NUMERIC",
			"pinPlaceholder" => "<pin>",
			"messageText" => "<pin> es tu PIN Ombu Credit",
			"pinLength" => 4,
			"senderId" => "OMBU Credit",
			"language" => "es",
			"repeatDTMF" => "1#",
			"speechRate" => 1
		];
		}
		return $data;
  	}

	/**
	 * Summary. Crea el template a utilizar para enviar el PIN a los usuarios
	 */
	public function crearTemplate($appID = null, $data = null)
	{
		$result = false;
		try {
			if (is_null($appID)) {
				$app = $this->IBApp->obtener();
				$appID = $app->applicationId;
			}
	
			$conf = $this->configurarTemplate($data);
			$url = "/2fa/1/applications/".$appID."/messages";
			$result = $this->Ejecutar("POST", $conf, $url);	
		} catch (IBExcepcion $e) {
			$this->DoLog($e->getMessage());
			throw new IBExcepcion($e->getMessage());
		}
		return $result;
	}

	/**
	 * Summary. Obtiene la lista de templates de un $appID
	 */
	private function listarTemplates($appID = null)
	{
		$result = false;
		try {
			if (is_null($appID)) {
				$app = $this->obtener();
				$appID = $app->applicationId;
			}

			$url = "/2fa/1/applications/".$appID."/messages";
			$result = $this->Ejecutar("GET", null, $url);	
		} catch (IBExcepcion $e) {
			$this->DoLog($e->getMessage());
			throw new IBExcepcion($e->getMessage());
		}
		return $result;
	}

	public function getTemplate($templateID, $appID = null)
	{
		$result = false;
		try {
			if (is_null($appID)) {
				$app = $this->obtener();
				$appID = $app->applicationId;
			}

			$url = "/2fa/1/applications/".$appID."/messages/".$templateID;
			$result = $this->Ejecutar("GET", null, $url);	
		} catch (IBExcepcion $e) {
			$this->DoLog($e->getMessage());
			throw new IBExcepcion($e->getMessage());
		}
		return $result;
	}

	public function actualizarTemplate($appID, $templateID, $data)
	{
		$result = false;
		try {
			if (is_null($appID)) {
				$app = $this->obtener();
				$appID = $app->applicationId;
			}

			$conf = $this->configurarTemplate($data);
			$url = "/2fa/1/applications/".$appID."/messages/".$templateID;
			$result = $this->Ejecutar("PUT", $conf, $url);	
		} catch (IBExcepcion $e) {
			$this->DoLog($e->getMessage());
			throw new IBExcepcion($e->getMessage());
		}
		return $result;
	}

	public function obtenerTemplate($appID = null)
	{
		$result = false;
		try {
			$rs = $this->listarTemplates(@$appID);
			if (!empty($rs)) {
				$template = $this->getTemplate($rs[0]->messageId, $appID);
				$result = $template;
			}
		} catch (IBExcepcion $e) {
			$this->DoLog($e->getMessage());
			throw new IBExcepcion($e->getMessage());
		}
		return $result;
	}

	/**
	 * Summary. Metodo utilizado para enviar los mensajes
	 * @param string $method Indica el metodo a utilizar en el curl
	 * @param array $data El array que contiene el mensaje
	 * @param string $url(opcional) La url personalizada que se utilizara para enviar el mensaje
	 */
	public function Enviar($method = 'GET', $data = [], $url = null){
		$result = false;
		$metodos = ['POST','GET','PUT'];
		try {
			if(empty($method) or !in_array($method, $metodos)){
				throw new IBExcepcion(__LINE__." No se indicó el método a utilizar o el método indicado no es válido.");
			}

			$rs = $this->Ejecutar($method, $data, $url);
			if($rs !== false){
				$result = $rs;
			}

		} catch (IBExcepcion $e) {
			$this->DoLog($e->getMessage());
			throw new IBExcepcion($e->getMessage());
		}
		return $result;
	}
/**
* Summary. Escribe en en log un mensaje para debug.
* @param string $msg El mensaje a escribir.
*/
	private function DoLog(string $msg = '') {
		$trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS,2);
		if (isset($trace[1])) {
			$msg = $trace[1]['function'].": ".$msg;
		}
		cLogging::LogToFile(__FILE__ ." ".$msg, $this->logfile, LGEV_DEBUG);
	}
} // class


class IBExcepcion extends Exception
{
}
class IBTokenExcepcion extends IBExcepcion
{
}
class IBEstadoExcepcion extends IBExcepcion
{
}
