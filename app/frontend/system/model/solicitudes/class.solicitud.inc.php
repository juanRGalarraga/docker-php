<?php
/**
* Summary. Interfaz con el core para manejar las solicitudes.
* Created: 2021-09-30
* Author: DriverOp
*/

require_once(DIR_model."class.jwtbase.inc.php");
require_once(DIR_model."wsclient".DS."class.wsv2Client.inc.php");
require_once(DIR_model."solicitudes".DS."class.solictemp.inc.php");


class cSolicitud extends cWsV2Client {
	
	public $msgerr = [];
	public $origen = 'FRONT';
	public $sent = false; // bandera para indicar si la solicitud ya ha sido enviada al core.
	public $calculadora = null; // Aquí se referencia al objeto cCalculadora, es para modificar la cotización actual según la solicitud.

	public function __construct() {
		parent::__construct();
		$this->sent = false;
	}

/**
* Summary. Pide iniciar una nueva solicitud.
* @param mixed $data Los datos de inicialización de la solicitud.
* @return int El id de la solicitud devuelta por el core.
*/
	public function InitSolicitud(array $data = null):?int {
		$result = null;
		try {
			$data['origen'] = $this->origen;
			if ($salida = $this->PostQuery('solicitud', $data)) {
				if (!is_object($salida)) {
					return SecureInt($salida, null);
				}
				if (isset($salida->cotizacion) and is_a($this->calculadora, "cCalculadora")) {
					$this->calculadora->SetRespuesta($salida->cotizacion);
				}
				$result = SecureInt($salida->id??null, null);
			}
		} catch(Exception $e) {
			$this->SetLog($e);
		}
		return $result;
	}

/**
* Summary. Modificar una solicitud existente.
* @param int $id El ID en el core de la solicitud a modificar.
* @param mixed $data Los datos de inicialización de la solicitud.
* @return bool Indica si la petición se realizó con éxito o no.
* @note Si el ID está vacío, no hacer nada...
*/
	public function SetSolicitud(int $id = null, array $data = null) {
		$result = null;
		if (empty($id) or !is_numeric($id)) { return $result; }
		try {
			$data['origen'] = $this->origen;
			$salida = $this->PutQuery('solicitud/'.$id, $data);
			$result = !$this->CheckForErrors();
			$this->sent = true;
			if (!is_object($salida)) {
				return SecureInt($salida, null);
			}
			if (isset($salida->cotizacion) and is_a($this->calculadora, "cCalculadora")) {
				$this->calculadora->SetRespuesta($salida->cotizacion);
			}
		} catch(Exception $e) {
			$this->SetLog($e);
		}
		return $result;
	}

/**
* Summary. Pide al core que apruebe la solicitud.
* @param int $id El ID en el core de la solicitud a aprobar.
* @return bool True cuando éxito.
*/
	public function Aprobar(int $id):bool {
		$this->PostQuery('solicitud/aprobar/'.$id);
		$result = !$this->CheckForErrors(); // Devuelve true cuando hay error, por eso hay que negarlo.
		if ($result) {
			$this->sent = true;
		}
		return $result;
	}
}