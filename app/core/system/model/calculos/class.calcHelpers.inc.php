<?php
/*
	Funciones de ayuda para los cálculos financieros.
	Author: DriverOp
	
	Modif: 2020-09-04
	Desc:
		Agegadas funciones PAGOINT y PAGOPRIN. Agregado documentación de cada función.
*/

require_once(DIR_includes."class.logging.inc.php");



class calcHelpers {
	
	/**
	* Summary. Calcular una cuota pura sobre un capital y interés anual.
	* @param float $capital. El capital solicitado de préstamo.
	* @param int $meses default 1. El número total de pagos.
	* @param float $interes_anual default 10. El interés anual a usar en el cálculo.
	* @return float. La cuota pura.
	*/
	static function CalcularCuota($capital, $meses = 1, $interes_anual = 10) {
		$result = 0;
		try {
			$interes = ($interes_anual / 100)/12;
			$pot=1+$interes;
			if (PHP_VERSION >= '5.6') {
				$mult = $pot**(-$meses);
			} else {
				$mult=pow($pot,-$meses);
			}
			$result = ($capital*$interes)/(1-$mult);
		} catch(Exeption $e) {
			if (DEVELOPE) { EchoLog($e->GetMessage()); }
			cLogging::Write("class.calcHelpers.inc.php ".__METHOD__." ".$e->GetMessage());
		}
		return $result;
	}

	static function CalcularInterestPart ($va, $pmt, $tasa, $periodo) {
		return -($va * pow(1 + $tasa, $periodo) * $tasa + $pmt * (pow(1 + $tasa, $periodo) - 1));
	}

	static function CalcularPVIF ($tasa, $nro_periodos)	{
		return (pow(1 + $tasa, $nro_periodos));
	}

	static function CalcularFVIFA ($tasa, $nro_periodos) {
		// Removable singularity at rate == 0
		if ($tasa == 0)
			return $nro_periodos;
		else
			// FIXME: this sucks for very small rates
		return (pow(1 + $tasa, $nro_periodos) - 1) / $tasa;
	}

	static function CalcularPMT ($tasa, $nro_periodos, $va, $vf, $tipo) {
		$vaif = self::CalcularPVIF ($tasa, $nro_periodos);
		$vfifa = self::CalcularFVIFA ($tasa, $nro_periodos);
			
		return ((-$va * $vaif - $vf ) / ((1.0 + $tasa * $tipo) * $vfifa));
	}
	/**
	* Summary. Imita la función de Excel. Devuelve el interés pagado en un período específico por una inversión basándose en pagos periódicos constantes y en una tasa de interés constante.
	* @param float $tasa. Es la tasa de interés.
	* @param int $periodo. Es el período para el que desea calcular el interés; debe estar comprendido entre 1 y el argumento nper.
	* @param int $nro_periodos. Es el número total de períodos de pago en una anualidad.
	* @param float $va. Valor Actual o la suma total de una serie de futuros pagos.
	* @param float $vf default 0. Es el valor futuro o saldo en efectivo que desea lograr después de efectuar el último pago. 
	* @param int $tipo default 0. Debe valer 0 o 1; por omisión 0. Indica cuándo vencen los pagos. 0 al final. 1 al principio.
	*/
	static function PAGOINT($tasa, $periodo, $nro_periodos, $va, $vf = 0.0, $tipo = 0) {
		if (($periodo < 1) || ($periodo >= ($nro_periodos + 1)))
			return null;
		else {
			$pmt = self::CalcularPMT ($tasa, $nro_periodos, $va, $vf, $tipo);
			$ipmt = self::CalcularInterestPart ($va, $pmt, $tasa, $periodo - 1);
			return (is_finite($ipmt) ? $ipmt: null);
		}
	}

	/**
	* Summary. Imita la función de Excel. Devuelve el pago sobre el capital de una inversión durante un período determinado basándose en pagos periódicos y constantes, y en una tasa de interés constante.
	* @param float $tasa. Es la tasa de interés.
	* @param int $periodo. Es el período para el que desea calcular el interés; debe estar comprendido entre 1 y el argumento nper.
	* @param int $nro_periodos. Es el número total de períodos de pago en una anualidad.
	* @param float $va. Valor Actual o la suma total de una serie de futuros pagos.
	* @param float $vf default 0. Es el valor futuro o saldo en efectivo que desea lograr después de efectuar el último pago. 
	* @param int $tipo default 0. Debe valer 0 o 1; por omisión 0. Indica cuándo vencen los pagos. 0 al final. 1 al principio.
	*/
	static function PAGOPRIN($tasa, $periodo, $nro_periodos, $va, $vf = 0.0, $tipo = 0) {
		if (($periodo < 1) || ($periodo >= ($nro_periodos + 1)))
			return null;
		else {
			$pmt = self::CalcularPMT ($tasa, $nro_periodos, $va, $vf, $tipo);
			$ipmt = self::CalcularInterestPart ($va, $pmt, $tasa, $periodo - 1);
			return ((is_finite($pmt) && is_finite($ipmt)) ? $pmt - $ipmt: null);
		}
	}

	/*
		Calcula un pago con intereses.
		$tasa es el coeficiente de interés por cada cuota.
		$cuotas es la cantidad de cuotas a pagar.
		$valorafinanciar es el capital total prestado.
	*/
	static function Pago($tasa, $cuotas, $valorafinanciar) {
		$capital = $valorafinanciar;
		$prorat = ((1 - (1 / (pow(1 + $tasa, $cuotas)))) / $tasa);
		return $capital / $prorat;
	}
	/**
	* Summary. Calcula el monto del Impuesto al Valor Agregado sobre el monto total.
	* @param float $porc_iva. El valor del IVA en porcentaje.
	* @param float $monto. El monto sobre el cual calcular el monto IVA.
	* @return float. El monto del IVA calculado.
	*/
	static function IVA($porc_iva, $monto) {
		return ($monto/100*$porc_iva);
	}
	/**
	* Summary. TIR: Tasa Interna de Retorno. Calcula la tasa de interés usada según una lista de pagos periódicos.
	* @param array $lista. La lista de pagos. Cada elemento debe ser un float.
	* @param float $guess default 0.01. El "ajuste" que se desea encontrar o qué tan preciso debe ser el interés calculado.
	* @return float. La tasa de interés calculado.
	*/
	static function TIR($lista, $guess = 0.01) {
		$used_guess = $guess;
		$x = $used_guess;
		$next_x = null;
		if ($used_guess == -1.0) {
			$x = 0.1;
		}
		$max_iterations = 20;
		$iterations_done = 0;
		$wanted_precision = 0.00000001;
		$current_diff = PHP_INT_MAX;
		$current = null;
		$above = null;
		$below = null;
		$index = null;
		
		while (($current_diff > $wanted_precision) and ($iterations_done < $max_iterations)) {
			$index = 0;
			$above = 0.0;
			$below = 0.0;
			reset($lista);

			foreach($lista as $key => $current) {
				$a = pow(1.0 + $x, $index);
				$above += $current / $a;

				$b = pow(1.0 + $x, $index + 1.0);
				
				$below += -$index * $current / $b;
				
				$index++;
				
			}

			$next_x = $x - $above / $below;
			$iterations_done++;
			$current_diff = abs($next_x - $x);
			$x = $next_x;
		}

		if (($used_guess == 0.0) and (abs($x) < $wanted_precision)) {
			$x = 0.0;
		}
		if ($current_diff < $wanted_precision) {
			return $x;
		}
		else {
			return NULL;
		}
	}

	/**
	* Summary. Tomando un array de números, devuelve su sumatoria.
	* @param array $sumas. Array de números int/float
	* @return float.
	*/
	static function Sumar($sumas){
		$total = 0;
		if(CanUseArray($sumas)){
			for ($i=0; $i < count($sumas); $i++) { 
				$total = $total + $sumas[$i];
			}
		}
		return $total;
	}

	/**
	* Summary. Divide un número en 100. Esto es para tratar porcentajes.
	* @param float $numero.
	* @return float.
	*/
	static function DivCien($numero){
		return $numero/100;
	}

}

?>