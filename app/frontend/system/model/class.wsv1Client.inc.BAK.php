<?php
/*
	Implementación del web service V1 de OmbuCredit Core.
	
	Created: 2020-12-12
	Author: DriverOp
*/
require_once(DIR_includes."common.inc.php");
require_once(LoadConfig("wsconfig.inc.php")); // Donde está la configuración para acceder al web service.
require_once(DIR_model."class.wsv1ClientBase.inc.php");
$this_file = substr(__FILE__,strlen(DIR_BASE))." ";

class cWSV1Client extends cWSV1ClientBase {
	
	public $method = 'GET';
	public $reintentos = 5;
	public $errnro = 0;
	
	function __construct() {
		parent::__construct();
		$this->baseURL = (defined("WS_URL"))?WS_URL:"http://localhost/ws/v1/";
		$this->baseURL = EnsureTrailingURISlash($this->baseURL);
		
		$this->defaultoptions[CURLOPT_TIMEOUT] = (defined("WS_TIMEOUT"))?WS_TIMEOUT:30;
		
		$this->response_type = (defined("WS_RESPONSE_TYPE"))?WS_RESPONSE_TYPE:'object';
		
		$this->sessname = (defined("WS_SESSION_NAME"))?WS_SESSION_NAME:'sess_wsv1';
		
		$this->debug_level = (defined("WS_DEBUG_LEVEL"))?WS_DEBUG_LEVEL:2;
		
		$this->referer = (defined("WS_REFERER"))?WS_REFERER:((!empty($_SERVER['HTTP_HOST']))?$_SERVER['HTTP_HOST']:'http://localhost/');
		
		if (defined("WS_LOG_PREFIX") and !empty(WS_LOG_PREFIX))
			$this->logfilename = '_'.WS_LOG_PREFIX.'.log';
		
		$this->logdir = (defined("WS_LOG"))?EnsureTrailingSlash(WS_LOG):'';
		
		$this->echo_debug = (defined("WS_ECHO_LOG"))?WS_ECHO_LOG:$this->echo_debug;
		
		$this->username = (defined("WS_USER"))?WS_USER:'usuario';
		$this->password = (defined("WS_PASS"))?WS_PASS:'contraseña';
		$this->token_ttl = (defined("WS_TOKEN_TTL"))?WS_TOKEN_TTL:3600;
	}
	
	public function Ejecutar($tipo = null, $data = null) {
		try {
			$result = false;
			$tipo = (empty($tipo))?$this->method:$tipo;
			$url = $this->url;
			if (!$this->CheckToken()) {
				$this->GetToken();
			}
			$this->url = $url;
			
			if($this->url != "tienda/login" AND array_key_exists("usuario",$_SESSION) && array_key_exists("token",$_SESSION['usuario']) && !empty($_SESSION['usuario']['token'])){
				$this->header['User_Authorization'] = $_SESSION['usuario']['token'];
			}
			$c = $this->reintentos;
			$this->method = $tipo;
			do {
				$rerun = false;
				if ($c < 5) { $this->SetLog(__METHOD__." ".__LINE__." Está tardando demasiado...".$c,__FILE__);}
				if ($result = parent::Commit($tipo, $data)) {
					if (in_array($this->http_nroerr,[400,401]) and (in_array($this->intl_nroerr,[5,6]))) { // Hay que obtener un nuevo token...
						if (!$this->GetToken()) {
							$result = false;
							throw new Exception(__LINE__." No se pudo obtener un nuevo token.");
						} else {
							$rerun = true;
							$this->url = $url;
						}
					}
				}
				$c--;
				if ($c < 1) { throw new Exception(__LINE__." Demasiados reintentos después de detectar TIMEOUT. Me doy por vencido."); }
			} while(($this->curl_nroerr == 28) and ($c > 0) or ($rerun));
		} catch(Exception $e) {
			$this->SetLog(__METHOD__." ".$e->GetMessage(),__FILE__);
		}
		return $result;
	}

/**
* Summary. Realiza una peticion al API del tipo GET
* @param str $url. La URI de la API a la que se quiere acceder.
* @param array $datos. La lista de parámetros GET
* @return bool/object $result. Puede devolver el payload de la respuesta del servidor.
*/
	public function GetQuery($url=false,$datos=false) {
		$result = false;
		if(!empty($url)){
			$this->url = $url;
			if ($datos) {
				$this->url = $url.(strpos($url, '?') === FALSE ? '?' : ''). http_build_query($datos);
			}
			$result = $this->Ejecutar("GET");
		}else{
			$this->SetLog(__METHOD__." ".__LINE__." No se indicó URL.",__FILE__);
		}
		return $result;
	}

/**
* Summary. Realiza una peticion al API del tipo POST
* @param str $url. La URI de la API a la que se quiere acceder.
* @param array $datos. La lista de parámetros GET
* @return bool/object $result. Puede devolver el payload de la respuesta del servidor.
*/
	public function PostQuery($url=false,$datos=false) {
		$result = false;
		if(!empty($url)){
			$this->url = $url;
			$result = $this->Ejecutar("POST", $datos);
		}else{
			$this->SetLog(__METHOD__." ".__LINE__." No se indicó URL.",__FILE__);
		}
		return $result;
	}

/**
* Summary. Realiza una peticion al API del tipo PUT
* @param str $url. La URI de la API a la que se quiere acceder.
* @param array $datos. La lista de parámetros GET
* @return bool/object $result. Puede devolver el payload de la respuesta del servidor.
*/
	public function PutQuery($url=false,$datos=false) {
		$result = false;
		if(!empty($url)){
			$this->url = $url;
			$result = $this->Ejecutar("PUT", $datos);
		}else{
			$this->SetLog(__METHOD__." ".__LINE__." No se indicó URL.",__FILE__);
		}
		return $result;
	}
/**
* Summary. Develve un plan de préstamo según ID.
* @param int $id. El ID del plan, si es cero se pide el plan por omisión para el negocio actual.
* @return object. Un objeto con los datos del plan o false en caso de error.
*/
	public function GetPlan($id = 0) {
		$result = false;
		try {
			if (!is_numeric($id)) { throw new Exception(__LINE__." ID debe ser un número."); }
			 if ($this->GetQuery('plan/'.$id)) {
				 $result = $this->data;
			 }
		} catch(Exception $e) {
			$this->SetLog(__METHOD__." ".$e->GetMessage(),__FILE__);
		}
		return $result;
	}
/**
* Summary. Pide al ws que calcule una cotización.
* @param array $cotiz. Los datos de la cotización.
* @return object o bool. La cotización calculada.
*/
	public function GetCotiz($cotiz) {
		$result = false;
		try {
			if (!is_array($cotiz) or (count($cotiz) == 0)) { throw new Exception(__LINE__." Sin datos para cotizar."); }
			 if ($this->PostQuery('simulate/',$cotiz)) {
				 $result = $this->data;
			 }
		} catch(Exception $e) {
			$this->SetLog(__METHOD__." ".$e->GetMessage(),__FILE__);
		}
		return $result;
	}
/**
* Summary. Establece una nueva solicitud.
* @param array $cotiz. Los datos de la cotización.
* @return object o bool. La cotización calculada.
*/
	public function SetSolicitud($cotiz) {
		$result = false;
		try {
			$this->SetLog(__METHOD__." Creando solicitud...",__FILE__);
			if (!is_object($cotiz) and (!is_array($cotiz) or (count($cotiz) == 0))) { throw new Exception(__LINE__." Sin datos para enviar."); }
			 if ($this->PostQuery('solicitud/',$cotiz)) {
				 $result = $this->data->solicitudId;
			 }
		} catch(Exception $e) {
			$this->SetLog(__METHOD__." ".$e->GetMessage(),__FILE__);
		}
		return $result;
	}
/**
* Summary. Modifica una solicitud existente.
* @param array $solictud. Los datos a modificar de la solicitud.
* @param int $id. El id de la solicitud a modificar.
* @return object o bool. La cotización calculada.
*/
	public function UpdateSolicitud($solicitud, $id) {
		$result = false;
		try {
			$this->SetLog(__METHOD__." Modificando solicitud...",__FILE__);
			if (!is_numeric($id) or is_null(SecureInt($id))) { throw new Exception(__LINE__." ID debe ser un número."); }
			if (!is_object($solicitud) and (!is_array($solicitud) or (count($solicitud) == 0))) { throw new Exception(__LINE__." Sin datos para enviar."); }
			 if ($this->PutQuery('solicitud/'.$id,$solicitud)) {
				 $result = $this->data;
			 }
		} catch(Exception $e) {
			$this->SetLog(__METHOD__." ".$e->GetMessage(),__FILE__);
		}
		return $result;
	}

/**
* Summary. Envía un pin al número indicado.
* @param str $tel. El número de teléfono.
* @param int $id. El id de la solicitud a modificar.
*/
	public function SendPINSolicitud($tel, $id) {
		$result = false;
		try {
			$this->SetLog(__METHOD__." A punto de enviar PIN...",__FILE__);
			if (!is_numeric($id) or is_null(SecureInt($id))) { throw new Exception(__LINE__." ID debe ser un número."); }
			$tel = trim($tel);
			if (empty($tel)) { throw new Exception(__LINE__." Parámetro teléfono está vacío."); }
			 if ($this->PostQuery('pin/',['tel'=>$tel,'solicitud'=>$id])) {
				 $result = @$this->data->pinId;
			 }
		} catch(Exception $e) {
			$this->SetLog(__METHOD__." ".$e->GetMessage(),__FILE__);
		}
		return $result;
	}
	

/**
* Summary. Verifica que in PIN sea válido contrastando contra el PINID.
* @param str/array $pin. El pin a verificar. Si es un array se pasan como URL virtual.
* @param str $pinid. El pin id contra el cual contrastar.
* @param int $id. El id de solicitud.
*/
	public function CheckPINSolicitud($pin, $pinid = null, $id = null) {
		$result = false;
		try {
			$this->SetLog(__METHOD__." A punto de chequear PIN...",__FILE__);
			if (empty($pin)) { throw new Exception(__LINE__." No se indicó PIN."); }
			$url = 'pin/';
			
			if (is_array($pin) and count($pin) > 0) {
				$url .= implode('/',array_values($pin));
			} else {
				$url .= $pin.'/';
			}
			if (!empty($pinid)) {
				$url .= $pinid.'/';
				if (!empty($id)) {
					if (is_numeric($id)) {
						$url .= $id.'/';
					}
				}
			}
			
			if ($this->GetQuery($url)) {
				$result = @$this->data;
			}
		} catch(Exception $e) {
			$this->SetLog(__METHOD__." ".$e->GetMessage(),__FILE__);
		}
		return $result;
	}
	
	public function ResendPINSolicitud($pinid, $id) {
		$result = false;
		try {
			$this->SetLog(__METHOD__." A punto de reenviar PIN...",__FILE__);
			if (!is_numeric($id) or is_null(SecureInt($id))) { throw new Exception(__LINE__." ID debe ser un número."); }
			if (empty($pinid)) { throw new Exception(__LINE__." No se indicó PINID."); }
			if ($this->PutQuery('pin/'.$pinid.'/'.$id)) {
				$result = @$this->data;
			}
		} catch(Exception $e) {
			$this->SetLog(__METHOD__." ".$e->GetMessage(),__FILE__);
		}
		return $result;
	}


/**
* Summary. Verifica la corrección y existencia de un CBU.
* @param str $cbu. El CBU a verificar
*/
	public function CheckCBU($cbu) {
		$result = false;
		try {
			$this->SetLog(__METHOD__." A punto de verificar CBU...",__FILE__);
			if (empty($cbu)) { throw new Exception(__LINE__." CBU está vacío."); }
			if ($this->GetQuery('cbu/'.$cbu)) {
				$result = @$this->data;
			}
		} catch(Exception $e) {
			$this->SetLog(__METHOD__." ".$e->GetMessage(),__FILE__);
		}
		return $result;
	}

/**
* Summary. Verifica la existencia de un cliente con el o los datos proporcionados
* @param array $data. Array con los datos para buscar una coicidencia.
* @return bool/array.
*/
	public function CheckData($data) {
		$result = false;
		try {
			$this->SetLog(__METHOD__." A punto de verificar Datos...",__FILE__);
			if (empty($data) or !is_array($data)) { throw new Exception(__LINE__." Data está vacío o no es array"); }
			if ($this->GetQuery('clientes/check',$data)) {
				$result = @$this->data;
			}
		} catch(Exception $e) {
			$this->SetLog(__METHOD__." ".$e->GetMessage(),__FILE__);
		}
		return $result;
	}

/**
* Summary. Verifica la existencia de un nombre de usuario
* @param array $username. El nombre de usuario a verificar
* @return bool/array.
*/
	public function CheckUsername($username) {
		$result = false;
		try {
			$this->SetLog(__METHOD__." A punto de verificar Username...",__FILE__);
			if (empty($username)) { throw new Exception(__LINE__." Username está vacío"); }
			if ($this->GetQuery('micuenta/user/'.$username)) {
				$result = @$this->data;
			}
		} catch(Exception $e) {
			$this->SetLog(__METHOD__." ".$e->GetMessage(),__FILE__);
		}
		return $result;
	}

/**
* Summary. Esto envía al core la petición para que cree el préstamo a partir de un ID de solicitud.
* @param int $id. El ID de la solicitud.
* @return bool.
*/
	public function CrearPrestamo($id) {
		$result = false;
		try {
			$this->SetLog(__METHOD__." A punto de Crear Préstamo...",__FILE__);
			if (!is_numeric($id) or is_null(SecureInt($id))) { throw new Exception(__LINE__." ID debe ser un número."); }
			if ($this->PostQuery('solicitud/aprobar/'.$id)) {
				$result = @$this->data;
			}
		} catch(Exception $e) {
			$this->SetLog(__METHOD__." ".$e->GetMessage(),__FILE__);
		}
		return $result;
	}

/**
* Summary. Verificar la solicitu contra buró.
* @param int $solicitud_id. El ID de la solicitud.
* @param int $buro_id default 0. El id del buro.
* @return array/bool.
*/
	public function CheckBuro($solicitud_id, $buro_id = 0) {
		$result = false;
		try {
			$this->SetLog(__METHOD__." A punto de Crear Préstamo...",__FILE__);
			if (!is_numeric($solicitud_id) or is_null(SecureInt($solicitud_id))) { throw new Exception(__LINE__." Solicitud ID debe ser un número."); }
			if (!is_numeric($buro_id) or is_null(SecureInt($buro_id))) { throw new Exception(__LINE__." Buro ID debe ser un número."); }
			if ($this->GetQuery('buro/'.$buro_id.'?solicitud_id='.$solicitud_id)) {
				$result = @$this->data;
			}
		} catch(Exception $e) {
			$this->SetLog(__METHOD__." ".$e->GetMessage(),__FILE__);
		}
		return $result;
	}


/**
* Summary. Busca traer todos los bancos de la base de datos de afterbanck.
* @return array/bool.
*/
	public function banksBdd(){
		$result = false;
		try{
			$this->SetLog(__METHOD__." A punto de buscar los Bancos...",__FILE__);
			if ($this->GetQuery('bankList/')) {
				$result = @$this->data;
			}
		}catch(Exception $e){
			$this->SetLog(__METHOD__." ".$e->GetMessage(),__FILE__);
		}
		return $result;
	}
} // Class
?>