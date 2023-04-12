<?php
/*
	Clase para acceder a los usuarios del Backend usando la API que el Backend expone.
	Created: 2021-11-17
	Author: DriverOp
*/

require_once(DIR_wsclient."class.wsv2Client.inc.php");

class cBackendUser extends cWsV2Client {
	
	
	function __construct() {
		parent::__construct();
	}
	
	function Get(int $id) {
		$result = $this->GetQuery('user/'.$id);
		if ($this->http_nroerr >= 400) { return false; }
		return $result;
	}

	function GetAll(int $id) {
		$result = $this->GetQuery('user/todo/'.$id);
		if ($this->http_nroerr >= 400) { return false; }
		return $result;
	}
}
