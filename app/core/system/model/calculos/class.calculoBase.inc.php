<?php
/*
	Nueva versión de la clase para cálculos de préstamos. 
	Acá se concentran los cálculos financieros de los 7 tipos de préstamos.

		**********************************************
		* Reemplaza a class.calculo_prestamo.inc.php *
		* No contiene los mismos nombres de métodos  *
		* Esta clase NO ES estática 				 *
		**********************************************
		
	Created: 2021-04-10
	Author: DriverOp
	Desc:
		Clase base para todos los cálculos.
*/

interface iCalculo {
// Summary. La función que todas las clases dereivadas deben implementar.
	public function Calcular(array $args = []):bool;
// Summary. El calculador efectivo de intereses. Usar propiedades de la clase para hacer el cálculo, no parámetros!
	public function CalcularIntereses():float;
}


require_once(DIR_includes."core_constants.inc.php");
require_once(DIR_includes."class.logging.inc.php");
require_once("class.calcHelpers.inc.php");
require_once(DIR_model."class.sysparams.inc.php");
require_once(DIR_model."planes".DS."class.planes.inc.php");

class cCalculoBase {
	
	protected $iva_insc = 0; // Este es especial.
	public $plan = null;
	public $tasas_cargos = array(); // Esto almacena la lista de cargos e impuestos a calcular de acuerdo al plan.
	public $tasas_impuestos = array(); // Esto almacena la lista de cargos e impuestos a calcular de acuerdo al plan.
	public $DebugOutput = false;
	public $msgerr = null;
	public $error = false;
	public $capital = 0;
	public $plazo = 0;
	public $totales = null;
	public $tasas = null;
	public $cuotas = null;
	public $overridePlan = false;
	public $calculo = null;

	function __construct() {
		$this->Reset();
	}

/**
* Summary. Establece la propiedad plan sin la cual no se pueden hacer cálculos.
*/
	public function SetPlan(cPlanes $plan):bool {
		$result = false;
		try {
			if (!$plan->existe) {throw new Exception('El plan no existe.'); }
			if (empty($plan->data->calculo)) {
				if ($plan->tipo == 'PRESTAMO') {
					if($plan->tipo_pagos == "unico"){
						$plan->data->calculo = 'calcular_prestamo';
					}else{
						$plan->data->calculo = 'calcular_prestamobycuotas';
					}
				}
				if ($plan->tipo == 'REFIN') {
					$plan->data->calculo = 'calcular_refinanciacion';
				}
			}
			$this->plan = $plan;
			// $this->capital = 0;
			// $this->plazo = 0;
			$result = true;
		} catch(Exception $e) {
			$this->SetErrorEx($e);
		}
		return $result;
	}

/**
* Summary. Determina si es posible iniciar los cálculos.
* @return bool
*/
	public function CanCalculate(array $args = []):bool {
		global $sysParams;
		$result = false;
		try {
			if (empty($this->plan)) { throw new Exception('No se estableció plan.'); }
			if (!$this->plan->existe) {throw new Exception('El plan no existe.'); }
			if (!method_exists('cCalculo', $this->plan->data->calculo)) { throw new Exception('El cálculo definido en el plan, no existe.'); }
			
			if (empty($this->capital) or ($this->capital <= 0)) { throw new Exception('Capital no asignado o con valor cero.'); }
			if (empty($this->plazo) or ($this->plazo <= 0)) { throw new Exception('Plazo no asignado o con valor cero.'); }
			
			// Recolectar las tasas de cargos e impuestos. Los impuestos dependen de los cargos y por lo tanto se deben calcular depués de determinar los cargos.
			$this->tasas_cargos = new stdClass();
			$this->tasas_impuestos = new stdClass();

			if (!empty($this->plan->data->porc_iva) and CheckFloat($this->plan->data->porc_iva)) { // La tasa de IVA tiene un tratamiento especial.
				$this->tasas_impuestos->iva_insc = $this->plan->data->porc_iva;
			}


			$lista = $this->plan->GetFullListCargos();
			if (CanUseArray($lista)) {
				foreach($lista as $carimp) {
					$alias = strtolower(trim($carimp->alias));
					if ($carimp->tipo == 'IMP') {
						$this->tasas_impuestos->$alias = $carimp->valor;
					} else {
						$this->tasas_cargos->$alias = $carimp->valor;
					}
				}
			}

			if (empty($this->tasas_impuestos->iva_insc)) $this->tasas_impuestos->iva_insc = $sysParams->Get('porc_iva',0);
			if (empty($this->tasas_impuestos->iva_insc)) $this->tasas_impuestos->iva_insc = $sysParams->Get('mora_porc_iva',0);
			
			$this->tipo_moneda = $this->plan->tipo_moneda??null;
			
			$result = true;
		} catch(Exception $e) {
			$this->SetErrorEx($e);
		}
		return $result;
	}
/**
* Summary. Prepara el registro para guardar en la tabla de préstamos sin guardarla realmente.
* @return array.
* @note Esto calcula los "buckets" del préstamo.
*/
	public function SetReg($cuotas = false):array {
		if(CanUseArray($cuotas)){
			return $cuotas;
		}
		$result = array();
		$result['calculo'] = $this->calculo;
		$result['capital'] = $this->capital;
		$result['periodo'] = $this->plazo;
		$result['plan_id'] = $this->plan->id;
		$result['desembolso'] = (isset($this->totales->desembolso) && !empty($this->totales->desembolso))? $this->totales->desembolso: NULL;
		$result['total_imponible'] = (isset($this->totales->total_imponible) && !empty($this->totales->total_imponible))? $this->totales->total_imponible: NULL;
		$result['interes_capital'] = (isset($this->totales->interes_capital) && !empty($this->totales->interes_capital))? $this->totales->interes_capital: NULL;
		$result['total_intereses'] = $result['interes_capital'];// Se supone que no entra en mora al mismo tiempo que se calcula. Por lo tanto todo el interés es de capital.
		
		$result['porc_iva'] = $this->tasas_impuestos->iva_insc;
		
		
		$result['cargos_administrativos'] = (isset($this->totales->cargos_admin) && !empty($this->totales->cargos_admin))? $this->totales->cargos_admin: NULL;
		$result['cargos_cobranza'] = (isset($this->totales->cargos_cobranza) && !empty($this->totales->cargos_cobranza))? $this->totales->cargos_cobranza: NULL;
		$result['total_cargos'] = $result['cargos_administrativos']+$result['cargos_cobranza'];
		
		
		$result['iva_cargos_administrativos'] = (isset($this->totales->iva_cargos_admin) && !empty($this->totales->iva_cargos_admin))? $this->totales->iva_cargos_admin: NULL;
		$result['iva_cargos_cobranza'] = (isset($this->totales->iva_cargos_cobranza) && !empty($this->totales->iva_cargos_cobranza))? $this->totales->iva_cargos_cobranza: NULL;
		$result['total_iva_cargos'] = $result['iva_cargos_administrativos']+$result['iva_cargos_cobranza'];
		
		$result['iva_interes_capital'] = (isset($this->totales->iva_interes_capital) && !empty($this->totales->iva_interes_capital))? $this->totales->iva_interes_capital: NULL;
		$result['total_iva_interes'] = $result['iva_interes_capital'];

		$result['total_iva'] = $result['total_iva_interes']+$result['total_iva_cargos'];
		
		
		$result['total_impuestos'] = $result['total_iva'];// $result['total_impuestos_1']+$result['total_impuestos_2'];
		
		$result['sobrecargo'] = (isset($this->totales->sobrecargo) && !empty($this->totales->sobrecargo))? $this->totales->sobrecargo: NULL;
		$result['total'] = (isset($this->totales->total) && !empty($this->totales->total))? $this->totales->total: NULL;
		$result['total_a_pagar'] = (isset($this->totales->total) && !empty($this->totales->total))? $this->totales->total: NULL;
		$result['total_a_facturar'] = (isset($this->totales->sobrecargo) && !empty($this->totales->sobrecargo))? $this->totales->sobrecargo: NULL;
		$result['tipo_moneda'] = $this->tipo_moneda;
		$result['tasas'] = $this->tasas;
		$result['tasas_impuestos'] = $this->tasas_impuestos;
		$result['tasas_cargos'] = $this->tasas_cargos;
		return $result;
	}


/**
* Summary. Vuelve a cero todos los montos implicados.
*/
	public function Reset() {
		$this->totales = new stdClass();
		$this->tasas = new stdClass();
		$this->totales->capital = 0;
		$this->totales->desembolso = 0;
		$this->totales->total_imponible = 0;
		$this->totales->total_intereses = 0;
		$this->totales->interes_capital = 0;

		$this->totales->total_impuestos = 0;
		$this->totales->total_iva_interes = 0;
		$this->totales->iva_interes_capital = 0;
		$this->totales->iva_cargos_administrativos = 0;
		$this->totales->iva_cargos_cobranza = 0;

		$this->totales->total_cargos = 0;
		$this->totales->cargos_admin = 0;
		$this->totales->cargos_cobranza = 0;

		$this->totales->sobrecargo = 0;

		if (defined("TASAS_PLACEHOLDER")) {
			foreach(TASAS_PLACEHOLDER as $key => $value) {
				$this->tasas->$key = 0;
			}
		}
	}
/**
* Summary. Calcula el impuesto IVA.
* @param float $monto El monto imponible.
* @return float.
*/
	public function CalcularIVA($monto) {
		return (!empty($this->tasas_impuestos->iva_insc))?$monto*$this->tasas_impuestos->iva_insc/100:0;
	}
/**
* Summary. Calcular el total de intereses de un monto.
* @param float $monto. El monto del cual calcular el interés,
* @param float $tasa. El porcentaje de interés.
* @return float.
*/
	public function CalcularInteresesMonto(float $monto, float $tasa):float{
		$base = 1+$tasa;
		$res = $monto*(pow($base, 1)-1);
		return calcHelpers::DivCien($monto * $tasa);
	}
/**
* Summary. Calcular un cargo sobre el monto.
* @param float $monto. El monto al cual aplicarle el cargo,
* @param float $tasa. El porcentaje del cargo.
* @return float.
*/
	function CalcularUnCargo($monto,$tasa){
		return (calcHelpers::DivCien($tasa)/30)*$monto;
	}

/**
* Summary.  Tasa estimada diaria.
* @return float.
*/
	public function CalcularTed($tasa){
	// Uno más, la tasa de interés dividido 100.
	$base = 1+calcHelpers::DivCien($tasa);
	// Uno dividido el plazo, esto da un número muy pequeño.
	return (pow($base, 1/30)-1)*100;

}

/**
* Summary. Tasa nominal diaria
* @return float.
*/
	public function CalcularTnd($tna){
	// Esto es la tasa nominal anual dividido 100, dividido 360 (días comerciales del año) y multiplicado por 100.
	return calcHelpers::DivCien($tna)/360*100;

}
/**
* Summary. CFT o Costo Financiero Total.
* @return float.
* @note Para realizar el cálculo deben de existir los totales. Sin los cuales no es posible establecer el CFT.
*/
	public function CalcularCft():float {
		$result = 0.0;
		if (isset($this->totales) and is_object($this->totales)) {
			$result = (pow($this->totales->total / $this->totales->capital, 12 / $this->plazo) - 1)*100;
		}
		return $result;
	}
/**
* Summary. Manejador de las excepciones.
* @param Exception $e Una instancia de una excepción.
* @return null.
*/
	function SetErrorEx(Exception $e) {
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
		if (DEVELOPE and $this->DebugOutput) { EchoLogP(htmlentities($line)); }
		cLogging::Write($line, $error_level);
	}

}

/*
	public function SetPlan(cPlan $plan):bool {
		$result = false;
		try {
			throw new Exception();
			$result = true;
		} catch(Exception $e) {
			$this->SetErrorEx($e);
		}
		return $result;
	}
*/