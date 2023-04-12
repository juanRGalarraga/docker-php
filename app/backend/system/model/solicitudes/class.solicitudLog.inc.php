<?php
/**
* Summary. Envía una entrada para el log de solicitudes
* Created: 2021-11-10
* Author: DriverOp
*/


require_once(DIR_wsclient."class.wsv2Client.inc.php");


class cSolicitudLog extends cWsV2Client {
	

	public $solicitudId = null;
	public $origen = 'FRONT';
	public $tag = null;
	public $descripcion = null;
	public $tipo_evento = null;

	public function __construct() {
		parent::__construct();
	}

/**
* Summary. Pide iniciar una nueva solicitud.
* @param mixed $data Los datos de inicialización de la solicitud.
*/
	public function Set($data = null) {
		$result = null;
		if (empty($data)) { $data = array(); }
		try {
			$data->origen = $this->origen;
			if (!empty($this->descripcion)) {
				$data->origen = $this->descripcion;
			}
			if (!empty($this->tag)) {
				$data->tag = $this->tag;
			}
			if (!empty($this->tipo_evento)) {
				$data->tipo_evento = $this->tipo_evento;
			}
			$this->PostQuery('solicitud/log/'.$this->solicitudId, (array)$data);
		} catch(Exception $e) {
			$this->SetLog($e);
		}
		return $result;
	}
/**
* Summary. Obtiene el listado de logs de una solicitud.
* @param int $id el Id de la solicitud.
* @return object
*/
	public function Listado(int $id) {
		$result = null;
		try {
			if ($this->GetQuery('solicitudes/log/'.$id)) {
				$result = $this->theData;
			}
		} catch(Exception $e) {
			$this->SetLog($e);
		}
		return $result;
	}
}