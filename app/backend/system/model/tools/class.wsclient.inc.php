<?php
/*
	Llamadas al core sueltas, para recuperar cosas que no pertenecen a ninguna entidad particular.
*/

    require_once(DIR_wsclient."class.wsv2Client.inc.php");

class cWsClient extends cWsV2Client{

/**
* Summary. Le pide al core que genere un número de CBU aleatorio.
*/
	public function GetRandomCBU() {
		$result = false;
		try {
			if ($this->GetQuery('cbu/gen')) {
				if ($this->http_status < 400) {
					$result = $this->theData;
				}
			}
		} catch(Exception $e) {
			$this->SetErrorEx($e->GetMessage());
		}
		return $result;
	}
}