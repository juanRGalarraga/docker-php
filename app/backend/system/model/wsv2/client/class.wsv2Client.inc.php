<?php
/*
	Clase con los métodos comunes para acceder al web sevice V2
	Created: 2021-05-25
	Author: DriverOp

	Rebrit SRL - Chacabuco 59 - Gualeguaychú - Entre Ríos.
	email: info@rebrit.ar
*/
require_once(__DIR__.DS."class.wsv2ClientBase.inc.php");
class cWsV2Client extends cWsV2ClientBase {

	public $reintentos = 5;

/**
* Summary. Ejecutar el request.
*/
	public function Ejecutar($params = null) {
		try {
			$c = $this->reintentos;
			do {
				$tryAgain = false;
				$this->Commit(null, null, $params);
				if (in_array($this->http_nroerr,[401,402]) or (in_array($this->core_nroerr,[1,5,6]))) {
					if ($this->GetToken()) {
						$tryAgain = true;
					}
				}
				if ($c == 0) { throw new Exception('Demasiados reintentos, me doy por vencido.'); }
				$c--;
			} while(($this->http_nroerr == 408) or ($this->curl_nroerr == 28) or ($tryAgain));
			
		} catch(Exception $e) {
			$this->SetLog($e);
		}
		return $this->theData;
	}

/**
* Summary. Realiza una peticion al API del tipo GET
* @param str $url. La URI de la API a la que se quiere acceder.
* @param array $datos. La lista de parámetros GET
* @return null/object $result. Devuelve el campo data de la respuesta del servidor.
*/
	public function GetQuery($url = null, $datos = null) {
		$result = null;
		try {
			$params = null;
			if (!empty($datos)) {
				if (is_object($datos)) {
					$datos = (array)$datos;
				}
				$params = http_build_query($datos);
			}
			$this->url = $url.((!empty($params))?'?'.$params:null);
			$this->method = "GET";
			$result = $this->Ejecutar();
		} catch(Exception $e) {
			$this->SetLog($e);
		}
		return $result;
	}
/**
* Summary. Realiza una peticion al API del tipo POST
* @param str $url. La URI de la API a la que se quiere acceder.
* @param array $datos. La lista de parámetros GET
* @return null/object $result. Devuelve el campo data de la respuesta del servidor.
*/
	public function PostQuery($url = null, $datos = null) {
		$result = null;
		try {
			$this->url = $url;
			$this->method = "POST";
			$result = $this->Ejecutar($datos);
		} catch(Exception $e) {
			$this->SetLog($e);
		}
		return $result;
	}
/**
* Summary. Realiza una peticion al API del tipo PUT
* @param str $url. La URI de la API a la que se quiere acceder.
* @param array $datos. La lista de parámetros GET
* @return null/object $result. Devuelve el campo data de la respuesta del servidor.
*/
	public function PutQuery($url=false,$datos=false) {
		$result = null;
		try {
			$this->url = $url;
			$this->method = "PUT";
			$result = $this->Ejecutar($datos);
		} catch(Exception $e) {
			$this->SetLog($e);
		}
		return $result;
	}

} // Clase