<?php
/*	
	Clase para abstraer las llamadas a la API de Bind.
	Created: 2020-09-18
	Author: Alcides

	Modif: 2020-11-25
	Author: Juan Galarraga
	Desc:
		Agrego métodos para hacer llamadas a DEBIN

	Modif: 2020-12-21
	Author: DriverOp
	Desc:
		Cambiado método HarvestData para integrar en el objeto cualquier resultado en vez de ser específico (y arriesgarse a perder datos).

	Modif: 2020-12-29
	Author: Juan Galarraga.
	Desc: 
		- Elimino métodos para buscar cuenta mediante CBU. 
		- Agrego métodos para el manejo de transacciones.
		
	Modif: 2020-12-30
	Author: DriverOp
	Desc:
		- Agregado método para determinar el account_id por omisión del sistema.
		- Agregada propiedad $negocio_id.

	Modif: 2020-12-31
	Author: Juan.
	Desc:
		- Agrego método para validación del account id.
*/

define("BIND_USERNAME","jballestero@ombutech.net");
define("BIND_PASSWORD","ViIVJ1V6gK5uaRT");

require_once(DIR_includes.'class.fechas.inc.php');
require_once(DIR_model."class.sysparams.inc.php");
require_once(DIR_integraciones."bind".DS.'class.bind_base.inc.php');
require_once(DIR_integraciones."bind".DS."class.bind_accounts.inc.php");

//Constantes

const MONEDAS_VALIDAS = array (
	'ARS' => "Peso argentino",
	'USD' => "Dólar americano"
);

const CONCEPTOS = array (
	'ALQ' => "Alquiler",	
	'CUO' => "Cuota",	
	'EXP' => "Expensas",	
	'FAC' => "Factura",	
	'PRE' => "Pŕestamo",	
	'SEG' => "Seguro",	
	'HON' => "Honorarios",	
	'HAB' => "Haberes",	
	'VAR' => "Varios"
);

const rxaccount_id = '/^\d{2}-\d-\d{4}-\d-\d$/';
const plchld_origin_id = '%02d%02d%02d%09d'; // Placeholder para el campo origin_id

/***********************************************/

class cBindAPI extends cBindBase {
    
    public $reintentos = 5;
    public $challenge = null;
	public $negocio_id = null;
	public $account_id = null;
	public $view_id = 'owner';



    // Se setea el usuario y la contraseña
    function __construct($username = null, $password = null) {
		global $sysParams;
		parent::__construct($username, $password);
		$this->referer = $sysParams->Get('bind_referer',null);
		$this->responseType = 'object';
    }
    
    public function Ejecutar($tipo = NULL, $data = NULL, $checktoken = true) {
		try {
			$result = false;
			$c = $this->reintentos;
			$tipo = (empty($tipo))?$this->method:$tipo;
			do {
				if ($c < 3) { $this->SetLog(__METHOD__." Está tardando demasiado...".$c);}
				if (parent::Commit($tipo, $data, $checktoken)) {
					$result = $this->parsed_response;
					cLogging::Write(__METHOD__." ".print_r($result,true));
				}
				$c--;
			} while(($this->curl_nroerr == 28) and ($c > 0));
			if ($c < 1) { throw new Exception(__LINE__." Demasiados reintentos después de detectar TIMEOUT. Me doy por vencido."); }
		} catch(Exception $e) {
			$this->SetError(__FILE__,__METHOD__,$e->GetMessage());
		}
		return $result;
    }
	
/**
* Summary. Consultar los datos de un CVU (Clave Virtual Uniforme)
* @param str $alias El Alias en cuestión.
* @return bool.
*/
    public function CuentaPorAlias($alias) {
		$this->ClearData();
		$this->url = 'accounts/alias/'.urlencode(trim($alias));
		$result = $this->Ejecutar('GET');
		if ($this->http_nroerr < 400) {
			$this->HarvestData();
			$result = true;
		} else {
			$this->HarvestError();
		}
		return $result;
	}

/**
* Summary. Consultar los datos de un CVU (Clave Virtual Uniforme)
* @param str $cvu El CVU en cuestión.
* @return bool.
*/
	public function CuentaPorCBU($cbu) {
		$result = false;
		$this->SetLog("Ejecutando ".__METHOD__." CBU: ".print_r($cbu,true));
		$this->ClearData();
		$this->url = 'accounts/cbu/'.urlencode(trim($cbu));
		$this->Ejecutar('GET');
		if ($this->http_nroerr < 400) {
			$this->HarvestData();
			$result = true;
		} else {
			$this->HarvestError();
		}
		return $result;
	}

/**
* Summary. Consultar los datos de un CVU (Clave Virtual Uniforme)
* @param str $cvu El CVU en cuestión.
* @return bool.
*/
	public function CuentaPorCVU($cvu):bool {
		$result = false;
		$this->SetLog("Ejecutando ".__METHOD__." CVU: ".print_r($cvu,true));
		$this->ClearData();
		$this->url = 'accounts/cbu/'.urlencode(trim($cvu));
		$this->Ejecutar('GET');
		if ($this->http_nroerr < 400) {
			$this->HarvestData();
			$result = true;
		} else {
			$this->HarvestError();
		}
		return $result;
	}

	//Retorna el listado de cuentas
	public function CuentasListado($data = array()){
		$result = false;
		if (!CanUseArray($data)) { $this->SetLog(__METHOD__." No data to transfer."); return $result; }
		$bank_id = urlencode(trim($data['bank_id']));
		$view_id = $this->view_id;
		$this->ClearData();
		$this->url = 'banks/'.$bank_id.'/accounts/'.$view_id;
		$result = $this->Ejecutar('GET', $data);
		if ($result) { $this->HarvestData(); }
		else { $this->HarvestError(); }
		return $result;
	}

	//Retorna una cuenta mediante el account_id

	public function Cuenta($data = array()){
		$result = false;
		if (!CanUseArray($data)) { $this->SetLog(__METHOD__." No data to transfer."); return $result; }
		$bank_id = urlencode(trim($data['bank_id']));
		$view_id = $this->view_id;
		$account_id = $this->GetAccountId($data);
		$this->ClearData();
		$this->url = 'banks/'.$bank_id.'/accounts/'.$account_id.'/'.$view_id;
		$result = $this->Ejecutar('GET', $data);
		if ($result) { $this->HarvestData(); }
		else { $this->HarvestError(); }
		return $result;
	}

	// Si el CUIT está bancarizado, me trae los datos
	public function CuitBancarizado($cuit, $tipo = NULL){
		$data = NULL;
		$this->ClearData(1);
		$this->url = 'persons/'.urlencode(trim($cuit)).'/banks';
		if(!$tipo == NULL) $data['obp_document_type'] = trim($tipo);
		$result = $this->Ejecutar('GET', $data);
		if ($result) { $this->HarvestData(); }
		else { $this->HarvestError(); }
		return $result;
	}

	// Transferencia de pago por: CBU, CVU
	public function TransferenciaPago($data = array()){
		$this->SetLog("Ejecutando ".__METHOD__." Data: ".print_r($data,true));
		if (!CanUseArray($data)) { $this->SetLog(__METHOD__." No data to transfer."); return; }
		$bank_id = urlencode(trim($data['bank_id']));
		$account_id = $this->GetAccountId($data);
		$view_id = $this->view_id;
		$this->ClearData(2);
		$this->url = 'banks/'.$bank_id.'/accounts/'.$account_id.'/'.$view_id.'/transaction-request-types/TRANSFER/transaction-requests';
		
		if(!CanUseArray($data['body'])) { $this->SetLog(__METHOD__." No body to transfer."); return; }
		
		$result = $this->Ejecutar('POST', $data);
		if ($result) { $this->HarvestData(); }
		else { $this->HarvestError(); }
		return $result;
	}

	// Obtiene un listado de las transferencias realizadas desde mis cuentas o hacia mi cuentas.

	public function ObtenerTransferencias($data = array()){
		if (!CanUseArray($data)){ $this->SetLog(__METHOD__." No data to transfer."); return; }
		$bank_id = urlencode(trim($data['bank_id']));
		$account_id = $this->GetAccountId($data);
		$view_id = $this->view_id;

		$this->url = 'banks/'.$bank_id.'/accounts/'.$account_id.'/'.$view_id.'/transaction-request-types/TRANSFER';

		$result = $this->Ejecutar('GET', $data);
		if ($result) { $this->HarvestData(); }
		else {
			$this->HarvestError();
		}
		return $result;
	}

	//Obtiene una transferencia en particular mediante el id de la transacción

	public function ObtenerTransferencia($data = array()){
		if (!CanUseArray($data)){ $this->SetLog(__METHOD__." No data to transfer."); return; }
		if (!$data['transaction_id'] || empty($data['transaction_id']) || is_null($data['transaction_id'])) {
			$this->SetLog(__METHOD__." No transaction id."); 
			return;
		}

		$bank_id = urlencode(trim($data['bank_id']));
		$account_id = $this->GetAccountId($data);
		$view_id = $this->view_id;
		$transaction_id = urlencode(trim($data['transaction_id']));

		$this->url = 'banks/'.$bank_id.'/accounts/'.$account_id.'/'.$view_id.'/transaction-request-types/TRANSFER/'.$transaction_id;

		$result = $this->Ejecutar('GET', $data);
		if ($result) { $this->HarvestData(); }
		else { $this->HarvestError(); }
		
		return $result;
	}

	//Elimina una petición de transferencia en estado PENDING

	public function DeleteTransferencia($data = array()){
		if (!CanUseArray($data)){ $this->SetLog(__METHOD__." No data to transfer."); return; }
		if (!$data['transaction_id'] || empty($data['transaction_id']) || is_null($data['transaction_id'])) {
			$this->SetLog(__METHOD__." No transaction id."); 
			return;
		}

		$bank_id = urlencode(trim($data['bank_id']));
		$account_id = $this->GetAccountId($data);
		$view_id = $this->view_id;
		$transaction_id = urlencode(trim($data['transaction_id']));

		$this->url = 'banks/'.$bank_id.'/accounts/'.$account_id.'/'.$view_id.'/transaction-request-types/TRANSFER/'.$transaction_id;

		$result = $this->Ejecutar('DELETE', $data);
		if ($result) { $this->HarvestData(); }
		else { $this->HarvestError(); }
		return $result;
	}

	/* Métodos para las llamadas a DEBIN  */
	/**************************************/
	
	//Crea una peticion DEBIN 

	public function CreateDEBIN($data = array()){
		if (!CanUseArray($data)){ $this->SetLog(__METHOD__." No data to transfer");}
		$bank_id = urlencode(trim($data['bank_id']));
		$account_id = $this->GetAccountId($data);
		$view_id = $this->view_id;
		$this->ClearData();
		$this->url = 'banks/'.$bank_id.'/accounts/'.$account_id.'/'.$view_id.'/transaction-request-types/DEBIN/transaction-requests';
		if(!CanUseArray($data['body'])) { $this->SetLog(__METHOD__." No body to transfer."); return; }
		$result  = $this->Ejecutar('POST', $data);
		return $result;
	}

	// Obtiene un DEBIN en especìfico mediante el id de la transacción

	public function GetDEBIN($data = array()){
		$result = false;
		if (!CanUseArray($data)){ $this->SetLog(__METHOD__." No data to transfer");}
		$bank_id = urlencode(trim($data['bank_id']));
		$account_id = $this->GetAccountId($data);
		$view_id = $this->view_id;
		$transaction_id = urlencode(trim($data['transaction_id']));
		$this->ClearData();
		$this->url = 'banks/'.$bank_id.'/accounts/'.$account_id.'/'.$view_id.'/transaction-request-types/DEBIN/'.$transaction_id;

		$result = $this->Ejecutar('GET', $data);
		return $result;
	}


	//Obtiene una lista de los todos DEBIN pendientes.

	public function GetListaDEBIN($data = array()){
		$result = false;
		if (!CanUseArray($data)){ $this->SetLog(__METHOD__." No data to transfer");}
		$bank_id = urlencode(trim($data['bank_id']));
		$account_id = $this->GetAccountId($data);
		$view_id = $this->view_id;
		$this->ClearData();
		$this->url = 'banks/'.$bank_id.'/accounts/'.$account_id.'/'.$view_id.'/transaction-request-types/DEBIN';

		$result = $this->Ejecutar('GET', $data);
		return $result;
	}

	//Elimina una petición DEBIN mediante el id de la transacción

	public function DeleteDEBIN($data = array()){
		$result = false;
		if (!CanUseArray($data)){ $this->SetLog(__METHOD__." No data to transfer");}
		$bank_id = urlencode(trim($data['bank_id']));
		$account_id = $this->GetAccountId($data);
		$view_id = $this->view_id;
		$transaction_id = urlencode(trim($data['transaction_id']));
		$this->ClearData();
		$this->url = 'banks/'.$bank_id.'/accounts/'.$account_id.'/'.$view_id.'/transaction-request-types/DEBIN/'.$transaction_id;

		$result = $this->Ejecutar('DELETE', $data);
		return $result;
	}	

	public function GetCuentaVendedor($data = array()){
		$result = false;
		if (!CanUseArray($data)){ $this->SetLog(__METHOD__." No data to transfer");}
		$bank_id = urlencode(trim($data['bank_id']));
		$account_id = $this->GetAccountId($data);
		$view_id = $this->view_id;
		// $this->ClearData();
		$this->url = 'banks/'.$bank_id.'/accounts/'.$account_id.'/'.$view_id.'/transaction-request-types/DEBIN/info';
		$result = $this->Ejecutar('GET', $data);
		return $result;
	}

	/**   
	*	Alta y baja de cuentas DEBIN para vendedores. 
	*/

	public function CuentaDEBINVendedor($data = array()){
		$result = false;
		if (!CanUseArray($data)){ $this->SetLog(__METHOD__." No data to transfer");}
		$bank_id = urlencode(trim($data['bank_id']));
		$account_id = $this->GetAccountId($data);
		$view_id = $this->view_id;
		// $this->ClearData();
		$this->url = 'banks/'.$bank_id.'/accounts/'.$account_id.'/'.$view_id.'/transaction-request-types/DEBIN';
		if(!CanUseArray($data['body'])) { $this->SetLog(__METHOD__." No body to transfer."); return; }
		$result = $this->Ejecutar('PUT', $data);
		return $result;
	}

	
	// Crea o actualiza una recurrencia para un comprador.

	public function SuscripcionDEBIN($data = array()){
		$result = false;
		if(!CanUseArray($data)){ $this->SetLog(__METHOD__." No data to transfer"); }
		$bank_id = urlencode(trim($data['bank_id']));
		$view_id = $this->view_id;
		$this->ClearData();
		$this->url = 'banks/'.$bank_id.'/'.$view_id.'/transaction-request-types/DEBIN-SUBSCRIPTION/transaction-requests';
		if(!CanUseArray($data['body'])) { $this->SetLog(__METHOD__." No body to transfer."); return; }
		$result = $this->Ejecutar('POST', $data);
		return $result;
	}

	// Obtiene los datos de una suscripcion DEBIN mediante el transaction_id

	public function GetSuscripcionDEBIN($data = array()){
		$result = false;
		if(!CanUseArray($data)){ $this->SetLog(__METHOD__." No data to transfer"); }
		$bank_id = urlencode(trim($data['bank_id']));
		$account_id = $this->GetAccountId($data);
		$view_id = $this->view_id;
		$transaction_id = urlencode(trim($data['transaction_id']));
		$this->ClearData();
		$this->url = 'banks/'.$bank_id.'/accounts/'.$account_id.'/'.$view_id.'/transaction-request-types/DEBIN-SUBSCRIPTION/'.$transaction_id;

		$result = $this->Ejecutar('GET', $data);
		return $result;
	}


    private function HarvestData($tipo = NULL) {
		if (!is_object(@$this->parsed_response)) { $this->SetLog(__METHOD__ ." No data to harvest."); return; }
		foreach($this->parsed_response as $key => $value) {
			$this->$key = $value;
		}
	} // HarvestData

	private function HarvestError() {
		if (!is_object(@$this->parsed_response)) { $this->SetLog(__METHOD__ ." No data to harvest."); return; }
		foreach($this->parsed_response as $key => $value) {
			$this->$key = $value;
		}
	}
	/*
		LIMPIA LAS VARIABLES:
		$tipo: 1 (Limpia datos de un CUIT bancarizado)
			   2 (Limpia datos para realizar transferencia)
			   OTRO (Limpia datos de cuenta por alias o por CBU)
	*/
    private function ClearData($tipo = NULL) {
		if($tipo==1){
			$this->has_any_account = NULL;
			$this->person = NULL;
		}
		elseif($tipo==2){
			$this->id = NULL;
			$this->type = NULL;
			$this->from = NULL;
			$this->counterparty = NULL;
			$this->details = NULL;
			$this->transaction_ids = NULL;
			$this->status = NULL;
			$this->start_date = NULL;
			$this->end_date = NULL;
			$this->challenge = NULL;
			$this->charge = NULL;
		}
		else{
			$this->owner = NULL;
			$this->type = NULL;
			$this->is_active = NULL;
			$this->currency = NULL;
			$this->label = NULL;
			$this->account_routing = NULL;
			$this->bank_routing = NULL;
		}
	}

/**
* Summary. Busca por cualquier medio tratar de encontras cuál es el account_id a usar en la transferencia a CBU.
* @param midex $data. Un array o un str o un object que puede o no contener el account_id
*/
	public function GetAccountId($data = null) {
		$result = false;
		try {
			if (is_array($data) and isset($data['account_id'])) {
				return $data['account_id'];
			}
			if (is_object($data) and isset($data->account_id)) {
				return $data->account_id;
			}
			if (!empty($this->account_id)) {
				return $this->account_id;
			}
			$bind_accounts = new cBindAccounts();
			if ($bind_accounts->GetDefault($this->negocio_id)) {
				return $bind_accounts->account_id;
			}
			$sysparams = new cSysParams();
			$result = $sysparams->Get('bind_accound_id',null);
			if (is_null($result)) {
				$cuenta = null;
				$cuentas = $this->CuentasListado(['bank_id'=>322]);
				if (CanUseArray($cuentas)) {
					reset($cuentas);
					$cuenta = current($cuentas);
					$c = $cuenta;
					do {
						if ((strtolower(trim($c->type??null)) == 'cuenta corriente') and (strtoupper(trim($c->status??null)) == 'NORMAL')) { $cuenta = $c; break; }
					}while($c = next($cuentas));
				}
				if (!is_null($cuenta)) {
					$result = $cuenta->id??null;
					$sysparams->Set('bind_accound_id',$result);
				}
			}
			$this->account_id = $result;
		} catch(Exception $e) {
			$this->SetLog(__METHOD__);
		}
		return $result;
	}
/**
* Summary. Fabrica el dato para el campo origin_id.
* @param int $prestamo_id El ID del préstamo que se transfiere.
* @return string.
*/
	public function MakeOriginId(int $prestamo_id):?string {
		$cad = DEVELOPE_NAME.DEPLOY;

		$sum = 0;
		for($i=0; $i<strlen($cad);$i++) {
			$sum = $sum + ord($cad[$i]);
		}
		while ($sum > 100) {
			$sum = round($sum / 10);
		}
		return sprintf(plchld_origin_id, $sum, $this->negocio_id, rand(1,99), $prestamo_id);
	}
	//Verifica que el formato del account id sea válido.

	public static function CheckAccountID($account_id){
		$result = false;
		
		if (preg_match(rxaccount_id, $account_id)) {
			$result = true;
		}

		return $result;
	}


} //Fin de la clase cBindAPI

?>