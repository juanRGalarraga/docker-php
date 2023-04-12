<?php
/*
	Clase para preguntarle al core por parámetros de configuración.
	Created: 2021-11-05
	Author: DriverOp
*/
require_once(DIR_model."wsclient".DS."class.wsv2Client.inc.php");

class cRemoteParams extends cWsV2Client {

	public function __construct() {
		parent::__construct();
	}

/**
* Summary. Pide iniciar una nueva solicitud.
* @param mixed $data Los datos de inicialización de la solicitud.
* @return int El id de la solicitud devuelta por el core.
*/
	public function Get(string $name) {
		$name = strtolower(substr(trim($name),0,64));
		return $this->GetQuery('param/'.$name);
	}

}

$remoteParam = new cRemoteParams;