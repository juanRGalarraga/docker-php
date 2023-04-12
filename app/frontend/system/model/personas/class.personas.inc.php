<?php
/**
* Summary. Clase para extraer datos de personas desde el core.
* Created: 2021-10-01
* Author: DriverOp
*/

require_once(DIR_model."wsclient".DS."class.wsv2Client.inc.php");


class cPersona extends cWsV2Client {


	public function __construct() {
		parent::__construct();
	}

/**
* Summary. Obtener una persona a partir de su ID.
* @param int $id El id de la persona buscada.
* @return mixed.
*/
	public function Get(int $id = null) {
		$result = null;
		try {
			$result = $this->GetQuery('personas/'.$id);
			$this->CheckForErrors();
		} catch(Exception $e) {
			$this->SetLog($e);
		}
		return $result;
	}

/**
* Summary. Obtener una persona a partir de su número de documento.
* @param string $nro_doc El id de la persona buscada.
* @return mixed.
*/
	public function GetByNroDoc(string $nro_doc = null) {
		$result = null;
		try {
			$nro_doc = substr(trim($nro_doc),0,32);
			$result = $this->GetQuery('personas/nrodoc/'.$nro_doc);
			$this->CheckForErrors();
		} catch(Exception $e) {
			$this->SetLog($e);
		}
		return $result;
	}

/**
* Summary. Obtener una persona a partir de un CBU.
* @param string $cbu El CBU de la persona buscada.
* @return mixed.
*/
	public function GetByNroCBU(string $cbu = null) {
		$result = null;
		try {
			$cbu = substr(trim($cbu),0,22);
			$result = $this->GetQuery('checkcbu/'.$cbu);
			$this->CheckForErrors();
		} catch(Exception $e) {
			$this->SetLog($e);
		}
		return $result;
	}
}
