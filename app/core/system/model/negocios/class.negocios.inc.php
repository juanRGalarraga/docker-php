<?php
/*
	Clase para acceder a los negocios del Backend usando la API que el Backend expone.
	Created: 2021-10-02
	Author: DriverOp
*/

require_once(DIR_wsclient."class.wsv2Client.inc.php");

class cNegocios extends cWsV2Client {
	
	
	function __construct() {
		parent::__construct();
	}
	
	function Get(int $id) {
		$result = $this->GetQuery('negocios/'.$id);
		if ($this->http_nroerr >= 400) { return false; }
		return $result;
	}
}
