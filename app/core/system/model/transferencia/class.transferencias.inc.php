<?php
/*
	Esta clase refunde la lógica de las transferencia de capital.
	Por ahora solo BIND como proveedor.
	
	Created: 2021-11-09
	Author: DriverOp
*/

require_once(DIR_model."solicitudes". DS ."class.aprSolicitud.inc.php");
require_once(DIR_model."prestamos".DS."class.prestamos.inc.php");
require_once(DIR_model."prestamos".DS."class.prestamos_hist.inc.php");
require_once(DIR_model."personas".DS."class.personas.inc.php");
require_once(DIR_integraciones."bind".DS."class.bind.inc.php");


class cTransferencia {



	public $solicitud = null; // Debe ser una solicitud aprobada.
	public $prestamo = null; // El préstamo sobre el cual trabajar.
	public $persona = null; // La persona titular del préstamo.
	public $bind = null; // Broker de transferencia.
	public $cbu = null;
	public $msgerr = null;
	public $logdir = DIR_logging;
	public $log_file_name = 'transf.log';
	public $echo_debug = false;
	public $error = false;
	public $data = null; // El objeto que se usará como datos de transferencia.


	function __construct() {
		$this->solicitud = new cAprSolicitud;
		$this->prestamo = new cPrestamos;
		$this->prestamoHist = new cPrestamosHist;
		$this->persona = new cPersonas;
		$this->bind = new cBindAPI();
	}

/**
* Summary. Controla que la solicitud sea apta para ser transferencia.
* @param cAprSolicitud $solicitud Una instancia de la clase AprSolcitud.
* @return bool
*/
	public function SetSolicitud(cAprSolicitud $solicitud = null):?bool {
		if (empty($solicitud)) { $this->msgerr = 'Solicitud nula.'; return null; }
		$result = false;
		try {
			if ($solicitud->estado != 'HAB') { throw new Exception("La solicitud no está habilitada."); }
			if ($solicitud->estado_solicitud != 'APRO') { throw new Exception("Lo siento, solo puedo usar solicitudes APRObadas."); }
			if (empty($solicitud->prestamo_id)) { throw new Exception("Solicitud sin préstamo asociado."); }
			if (empty($solicitud->persona_id)) { throw new Exception("Solicitud sin persona asociada."); }
			$this->solicitud = $solicitud;
			if (!$this->prestamo->Get($this->solicitud->prestamo_id)) {
				throw new Exception('Préstamo no se pudo leer intentando transferir.');
			}
			
			if (!$this->persona->Get($this->prestamo->persona_id)) {
				throw new Exception('Persona cliente no se pudo leer intentando transferir.');
			}
			
			$this->cbu = $this->persona->GetCBU();
			if (empty($this->cbu)) {
				throw new Exception('No se pudo transferir, persona no tiene CBU.');
			}
			
			$this->bind->negocio_id = $this->solicitud->negocio_id;
			$result = true;
		} catch(Exception $e) {
			$this->SetError($e);
		}
		return $result;
	}

/**
* Summary. Recolecta los datos necesarios para realizar la transferencia.
* @param string $desc_transfer Descripción textual de la transferencia.
* @return bool Pudo o no se pudo hacer.
*/
	public function SetTransfData(string $desc_transfer = null):?bool {
		$result = false;
		try {
			if (is_null($this->solicitud) or !is_a($this->solicitud, "cAprSolicitud")) {
				throw new Exception('Solicitud no establecida.');
			}
			if (is_null($this->prestamo) or !is_a($this->prestamo, "cPrestamos")) {
				throw new Exception('Prestamo no establecido.');
			}
			if (!$this->bind->GetAccountId()) {
				$this->solicitud->data->comentario = 'No se pudo acreditar porque BIND no tiene account_id';
				$this->solicitud->data = $this->solicitud->data;
				$this->solicitud->Set();
				throw new Exception('No se pudo transferir. No se encontró AccountId de BIND.');
			}
			$this->data = array(
				'bank_id'=>322,
				'body'=>array(
					'origin_id'=>$this->bind->MakeOriginId($this->prestamo->id),
					'to'=>array(
						'cbu'=>$this->cbu,
					),
					'value'=>array(
						'currency'=>'ARS',
						'amount'=>number_format($this->prestamo->capital,2,'.','')
					),
					'description'=>$desc_transfer.' '.str_pad($this->prestamo->id,7,'0',STR_PAD_LEFT),
					'concept'=>'VAR',
					'emails'=>array($this->persona->email)
				)
			);
			$result = true;
		} catch(Exception $e) {
			$this->SetError($e);
		}
		return $result;
	}
/**
* Summary. Ejecutar la transferencia.
*/
	public function Execute() {
		$result = false;
		$this->error = false;
		try {
			if (empty($this->data)) { throw new Exception("No se ha establecido data con los datos de transferencia"); }
			$res = $this->bind->TransferenciaPago($this->data);
			if ($this->bind->http_nroerr >= 400) {
				$this->SetDataTransferenciaFail($res);
				$this->msgerr = @$this->bind->message;
				$this->SetLog($this->msgerr);
				$this->error = true;
				return false;
			}

			if (!isset($res->status)) {
				$this->msgerr = "Bind no devolvió status";
				$this->SetLog($this->msgerr);
				$this->error = true;
				return false;
			}
			
			if (strtoupper(trim($res->status??null)) == 'COMPLETED') {
				$this->SetDataTransferenciaOk($res);
			} else {
				$this->SetDataTransferenciaFail($res);
			}
			$result = true;
			
		} catch(Exception $e) {
			$this->SetError($e);
		}
		return $result;
	}

/**
* Summary. Establece los datos de la trasferencia exitosa.
* @param object $res Los datos de la transferencia realizada.
*/
	function SetDataTransferenciaOk(object $res) {
		$res->transferido = true;
		$res->transferido_fechahora = cFechas::Ahora();
		$this->prestamo->data_transferencia = $res;
		$this->prestamo->data_transferencia = $this->prestamo->data_transferencia;
		$this->prestamo->Set();
		$this->solicitud->data->transferido = true;
		$this->solicitud->data->transferido_fechahora = cFechas::Ahora();
		$this->solicitud->data = $this->solicitud->data;
		$this->solicitud->Set();
		$this->SetLog("Transferencia completada account_id: ".$this->bind->account_id." prestamo_id: ".$this->prestamo->id, LGEV_INFO);
	}

/**
* Summary. Establece los datos de la trasferencia fallida.
* @param object $res Los datos de la transferencia realizada.
*/
	function SetDataTransferenciaFail(object $res = null) {
		if (empty($res)) { $res = new stdClass; }
		$res->transferido = false;
		$res->transferido_fechahora = cFechas::Ahora();
		$this->prestamo->data_transferencia = $res;
		$this->prestamo->data_transferencia = $this->prestamo->data_transferencia;
		$this->prestamo->Set();
		$this->solicitud->data->transferido = false;
		$this->solicitud->data->transferido_fechahora = cFechas::Ahora();
		$this->solicitud->data = $this->solicitud->data;
		$this->solicitud->Set();
		$this->SetLog("Transferencia fallida account_id: ".$this->bind->account_id." prestamo_id: ".$this->prestamo->id, LGEV_WARN);
	}
/**
* Summary. Atiende las excepciones elevadas en los métodos de esta clase.
* @param exception $e Un objeto de tipo exception.
*/
	function SetError(Exception $e) {
		$this->msgerr = $e->GetMessage();
		$this->error = true;
		$trace = debug_backtrace();
		$caller = @$trace[1];
		$file = @$trace[0]['file'];
		if (strpos($file, DIR_BASE) !== false) {
			$file = substr_replace($file, '', strpos($file, DIR_BASE), strlen(DIR_BASE));
		}
		$line = sprintf('%s:%s %s%s', $file, @$e->GetLine(), ((isset($caller['class']))?$caller['class'].'->':null), @$caller['function']);
		$line .= ' '.$this->msgerr;
		$error_level = LGEV_WARN;
		if (DEVELOPE and $this->echo_debug) { EchoLogP(htmlentities($line)); }
		$this->SetLog($line, $error_level);
	}

	public function SetLog($linea) {
		if ($this->echo_debug) {
			echo $linea.FDL;
		}

		umask(0);
	
		$mes = Date('Y-m');
		$dia = Date('Y-m-d');
		
		$dir = $this->logdir.$mes.DIRECTORY_SEPARATOR;

		$archivo = $dir.$dia.'-'.$this->log_file_name;
		if (!file_exists($this->logdir)) {
			mkdir($this->logdir,0777);
		}
	
		if (!file_exists($dir)) {
			mkdir($dir,0777);
		}
		
		$linea = '['.Date('Y-m-d H:i:s').'] '.$linea.PHP_EOL;
		return file_put_contents($archivo, $linea, FILE_APPEND);
	}
}
