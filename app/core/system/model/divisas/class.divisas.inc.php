<?php

require_once(DIR_model."class.sysparams.inc.php");


class cDivisas{

	public $usuario_id = null;
	public $base_url = 'https://api.exchangerate.host/';
	public $money_pibot = 'USD';
	private $directory_base = DIR_includes;
	private $filename = "cotizaciones.json";

	
	public function Search($url){
		$result = false;
		$req_url = $this->base_url.$url.'?base='.$this->money_pibot;
		$response_json = file_get_contents($req_url);
		if($response_json) {
			try {
				$response = json_decode($response_json);
				if($response->success === true) {
					$result = $response;
				}
			} catch(Exception $e) {
				$this->SetError($e);
				return false;
			}
		}
		return $result;

	}

	public function SearchLatestRates() {	
		$result = false;
		try {
			$result = $this->Search('latest');
		} catch(Exception $e) {
			$this->SetError($e);
			return false;
		}
		return $result;
	}

	public function SearchConvertCurrency($from,$to) {	
		$result = false;
		try {
			$url = 'convert?from='.$from.'&to='.$to;
			$result = $this->Search($url);
		} catch(Exception $e) {
			$this->SetError($e);
			return false;
		}
		return $result;
	}

	public function SearchHistoricalRates($date) {	
		$result = false;
		try {
			if(!cCheckInput::Fecha($date)){ 
				cLogging::Write(__FILE__." ".__LINE__." No se indico una fecha o la indicada es invalida");
				return false; 
			}
			$result = $this->Search($date);
		} catch(Exception $e) {
			$this->SetError($e);
			return false;
		}
		return $result;
	}


	public function SearchSymbols() {	
		$result = false;
		try {
			$result = $this->Search('symbols');
		} catch(Exception $e) {
			$this->SetError($e);
			return false;
		}
		return $result;
	}

	public function GetCotizaciones(){
		$result = false;
		try {
			if(!ExisteArchivo($this->directory_base.$this->filename)){
				cLogging::Write(__LINE__." ".__FILE__." No existe el archivo , procedemosa a intentar crearlo");
				$this->ArmarArchivo();
			}
			$result = file_get_contents($this->directory_base.$this->filename);
			if($result){
				$result = json_decode($result);
			}
			
		} catch(Exception $e) {
			$this->SetError($e);
			return false;
		}
		return $result;
	}

	public function ArmarArchivo(){
		$result = false;
		try {
			$cotizaciones = $this->SearchLatestRates();
			if(isJSON($cotizaciones)){
				$cotizaciones = json_decode($cotizaciones,true);
			}
			$base_money = $cotizaciones->base;
			$cotiz = $cotizaciones->rates;
			$cotiz->ultima_modificacion = new stdClass;
			$cotiz->ultima_modificacion = cFechas::Ahora();
			$cotiz->base = new stdClass;
			$cotiz->base = $base_money;
			if(ExisteArchivo($this->directory_base.$this->filename)){
				unlink($this->directory_base.$this->filename);
			}
			$cotiz = json_encode($cotiz,JSON_HACELO_BONITO);
			if(!$result = file_put_contents($this->directory_base.$this->filename,$cotiz)){
				throw new Exception(__LINE__." ".__FILE__." No se pudo guardar el archivo de las cotizaciones");
			}
		} catch(Exception $e) {
			$this->SetError($e);
			return false;
		}
		return $result;
	}

	function ConvertMoneyTo($alias_a_convertir = null,$alias_inicial = null,$monto_a_convertir = null){
		$result = $monto_a_convertir;
		try {
			if (empty($alias_a_convertir)) {
				$alias_a_convertir = $sysParams->Get('tipo_moneda',"ARS");
			}
						
			if(!$alias_inicial or empty($alias_inicial)){ $alias_inicial = $alias_a_convertir; }

			$cotizaciones = $this->GetCotizaciones();

			$alias_a_convertir = mb_strtoupper($alias_a_convertir);
			$alias_inicial = mb_strtoupper($alias_inicial);
								
			if(isset($cotizaciones->$alias_a_convertir)){
				$moneda_a_convertir = $cotizaciones->$alias_a_convertir;
			}else{
				throw new Exception(__LINE__ . " Tipo moneda ".$alias_a_convertir." no existe. ");
			}
			if ($alias_a_convertir != $alias_inicial) {
				
				if(isset($cotizaciones->$alias_inicial)){
					$moneda_inicial = $cotizaciones->$alias_inicial;
				}else{
					throw new Exception(__LINE__ . " valor moneda ".$alias_inicial." no existe. ");
				}

				$precioUSD = 0;
				$valor_oficial = 0;
				if($moneda_inicial && $moneda_inicial < 0){
					throw new Exception(__LINE__ . " valor moneda Argentina no puede estar vacia. ");
				}
				if($moneda_a_convertir && $moneda_a_convertir < 0){
					throw new Exception(__LINE__ . " Tipo moneda a convertir no puede estar vacio. ");
				}
				if($monto_a_convertir && $monto_a_convertir < 0){
					return $monto_a_convertir;
					// throw new Exception(__LINE__ . " Monto no puede ser vacio. ");
				}
				
				$precioUSD = $monto_a_convertir/$moneda_inicial;
				if($precioUSD && $precioUSD > 0){
					$valor_oficial = $precioUSD*$moneda_a_convertir;
					$valor_oficial = round($valor_oficial,2);
					$result = $valor_oficial;
				}
			}
		} catch(Exception $e) {
			$this->SetError($e);
		}
		if(empty($result)){ $result = 0; }
		return $result;
	}

	public function SetError($e) {
		$trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
		$f = array_pop($trace);
		$line = sprintf('%s:%s %s%s', cLogging::TrimBaseFile($f['file']), $f['line'], ((isset($f['class']))?$f['class'].'->':null), $f['function']).' '.$e->GetMessage().PHP_EOL;
		for ($i=count($trace)-1; $i>=0; $i--) {
			$f = $trace[$i];
			$line .= sprintf("\t%s:%s %s%s", cLogging::TrimBaseFile($f['file']), $f['line'], ((isset($f['class']))?$f['class'].'->':null), $f['function']).PHP_EOL;
		}
		if ($this->DebugOutput) { EchoLog(__LINE__);EchoLog($line); }
		cLogging::Write(trim($line));
	}
}