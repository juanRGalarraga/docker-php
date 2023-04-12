<?php
/*
	Implementación de cálculos comunes a cualquier tenant.
	Esta es la clase de fall back.
*/

require_once(DIR_model."calculos".DS."class.calculoBase.inc.php");

class cCalculo extends cCalculoBase implements iCalculo {

	public $porc_tna_publico = 0;
	public $porc_gastos_admin_publico = 0;

/**
* Summary. Ejecuta el cálculo y devuelve los resultados.
* @param array $args. La lista de argumentos para pasar al cálculo.
* @return bool.
* @note Dependiendo de qué tipo de cálculo hay que ejecutar, los resultados se almacenan en las propiedades ->totales ->tasas ->cuotas
*/
	public function Calcular(array $args = []):bool {
		$result = false;
		if (!$this->CanCalculate($args)) { return $result; }
		try {
			$this->calculo = @$this->plan->data->calculo;
			//$this->calcular_prestamo($args); // Es solo por consistencia. Los cálculos en sí, deberían estar en este método.
			
			call_user_func([$this,$this->calculo],$args);
			$result = true;
		} catch(Exception $e) {
			$this->SetErrorEx($e);
		}
		return $result;
	}
/**
* Summary. Calcula el total de intereses a cobrar. Tener en cuenta que esto modifica $this->totales y usa los datos en él para hacer el cálculo.
* Los parámetros son solo para cumplir con la interface.
* @return float El monto de interés a cobrar.
*/
	public function CalcularIntereses():float {
	
		$this->tasas->TEM = (pow(1+$this->tasas->TNA,1/12)-1);	
		$aux = (pow(1+$this->tasas->TEM,1/30)-1);
		
		$sumatoria_coeficientes = 0;
		
		for ($i=1;$i<=$this->plazo;$i++) {
			$sumatoria_coeficientes += 1/pow(1+$aux,$i);
		}
		
		$periodo_total = ($this->totales->total_imponible/$sumatoria_coeficientes)*$this->plazo;
		return ($periodo_total-$this->totales->total_imponible);
	}

/**
* Summary. Sobrecarga del mismo método que está en el ancestro.
*/
	public function CanCalculate(array $args = []):bool {
		$result = parent::CanCalculate($args);
		if ($result) {
			// O está en el plan, o viene dado por la propiedad del objeto.
			if (isset($this->plan->data))
				$this->porc_tna_publico = (isset($this->plan->data->TNA_Publico->valor))?$this->plan->data->TNA_Publico->valor:$this->porc_tna_publico;
			$this->porc_tna_publico = (isset($args['porc_tna_publico']))?$args['porc_tna_publico']:$this->porc_tna_publico;
			if ($this->porc_tna_publico > 0)
				$this->porc_gastos_admin_publico = 100-$this->porc_tna_publico;
			/* Recuperar los cargos e impuestos del plan */
			$this->GetAllAditionals();
		}
		return $result;
	}
/**
* Summary. Calcula un préstamo simple. CASO 1.
* @param array $args. La lista de argumentos para pasar al cálculo.
* $return bool.
*/
	public function calcular_prestamo(array $args = []):bool {
		$args = array_change_key_case($args);
		if (empty($args['tna'])) {
			// El TNA está en el Plan...
			$args['tna'] = $this->plan->tasa_nominal_anual;
		}
		$args['tnm'] = $args['tna']/12;
		
		$this->tasas->TNA = $args['tna']; // Tasa Nominal Anual
		$this->tasas->TNM = $args['tnm']; // Tasa Nominal Mensual
		$this->tasas->TND = $this->CalcularTnd($this->tasas->TNA); // Tasa Nominal Diaria (Se parte de la TNA).
		$this->tasas->TED = $this->CalcularTed($this->tasas->TNM); // Tasa Efectiva Estimada Diaria (Se parte de la TNM).

		// Solo para que las propiedades tengan valor inicial, se igualan a ->capital.
		$this->totales->capital = $this->capital;
		$this->totales->desembolso = $this->totales->capital;
		$this->totales->total_imponible = $this->capital;
		
		$this->totales->interes_capital = 0;
		$this->totales->iva_interes_capital = 0;
		$this->totales->cargos_admin = 0;
		$this->totales->iva_cargos_admin = 0;
		$this->totales->total_iva_cargos = 0;
		$this->totales->cargos_admin = 0;
		$this->totales->cargos_cobranza = 0;

		$this->totales->total_cargos = 0;

		$this->totales->interes_capital = $this->CalcularIntereses();
		$this->totales->total_intereses = $this->totales->interes_capital; // Se supone que el préstamo no está vencido en este momento...

			// Cálculo de los cargos fijos. Son dos casos, incluidos en el capital (caso 1), o bién sobre el capital (caso 2).
			if (CanUseArray($this->cargos)) {
				foreach($this->cargos as $key => $cargo) {
					if ($cargo['aplicar'] == 'CAPITALMAS') { // Caso 1.
						$this->totales->cargos_admin += $this->CalcularUnCargo($this->capital, $cargo['valor'])*$this->plazo;
					} else { // Si no es caso 1, necesariamente es caso 2
						$this->totales->cargos_cobranza += $this->CalcularUnCargo($this->capital, $cargo['valor'])*$this->plazo;
					}
				}
			}
		$this->totales->total_cargos = $this->totales->cargos_admin+$this->totales->cargos_cobranza;
		// Cálculo de los impuestos aplicados según el plan. Excluido el IVA.
		$this->totales->total_impuestos = $this->Calcular_Impuestos();

		if ($this->tasas_impuestos->iva_insc > 0) {
			$this->totales->iva_interes_capital = $this->CalcularIVA($this->totales->interes_capital);
			$this->totales->total_iva_interes += $this->totales->iva_interes_capital;
			/* En Ombucredit, los cargos no devengan IVA.
				$this->totales->iva_cargos_admin = $this->totales->cargos_admin*($this->tasas_impuestos->iva_insc/100);
				$this->totales->iva_cargos_cobranza = $this->totales->cargos_cobranza*($this->tasas_impuestos->iva_insc/100);
				$this->totales->total_iva_cargos = $this->totales->iva_cargos_admin+$this->totales->iva_cargos_cobranza;
			*/
			$this->totales->total_impuestos = $this->totales->total_impuestos+ // total_impuestos puede traer un monto previo debido a ->Calcular_Impuestos
				$this->totales->total_iva_interes+$this->totales->total_iva_cargos;
		}

		
		$this->totales->sobrecargo = $this->totales->total_intereses+$this->totales->total_cargos+$this->totales->total_impuestos;

		$this->totales->total = $this->capital+$this->totales->sobrecargo;
		
		$this->tasas->CFT = $this->CalcularCft();
		$this->SetReg();

		return true;
	}

	public function calcular_prestamobycuotas(){
		$this->tasas->TNA = $this->plan->tasa_nominal_anual; // Tasa Nominal Anual
		$this->tasas->TNM = $this->plan->tasa_nominal_anual/12; // Tasa Nominal Mensual
		$this->tasas->TND = $this->CalcularTnd($this->tasas->TNA); // Tasa Nominal Diaria (Se parte de la TNA).
		$this->tasas->TED = $this->CalcularTed($this->tasas->TNM); // Tasa Efectiva Estimada Diaria (Se parte de la TNM).
		$dias = 1;
		// Showvar($this->plan->tipo_pagos);
		switch ($this->plan->tipo_pagos) {
			case 'semanal':
				$dias = 7;
			break;
			case 'quicenal':
				$dias = 15;
			break;
			case 'mensual':
				$dias = 30;
			break;
		}

		if(isset($this->fecha_contrato) && !empty($this->fecha_contrato) && cCheckInput::Fecha($this->fecha_contrato)){
			$fecha_inicio = $this->fecha_contrato;
		}else{
			$fecha_inicio = cFechas::Sumar(cFechas::Hoy(),$dias);
		}
		
		if($this->plan->tipo_pagos == "mensual"){
			$fecha_venc = cFechas::SetDayInitProxMes($fecha_inicio);
		}else{
			$fecha_venc = $fecha_inicio;
		}
		
		$total_interes = 0;
		$total_iva = 0;
		$valor_prestamo = $this->capital;
		$saldo_capital = $valor_prestamo;
		$saldo_capital_final_periodo = $valor_prestamo;
		$tna = ($this->plan->tasa_nominal_anual/100);
		$plazo = $this->plazo;
		$iva = ($this->tasas_impuestos->iva_insc/100);
		
		$cuotas = array();
		$tasa_calculo = $tna*($iva+1);
		$tasa_calculo = $tna;
		
		for ($i=1; $i <= $plazo; $i++) {
			
			$cuotas[$i]['cuota_nro'] = $i;
			$cuotas[$i]['tipo_moneda'] = $this->tipo_moneda;

			$cuotas[$i]['saldo_inicio_periodo'] = number_format($saldo_capital,2,'.','');
			
			// Armo las fechas para que arranquen al principio del mes siguente a los 30 dias transucurridos
			$fecha_explode = explode("-",$fecha_venc);
			
			if($this->plan->tipo_pagos == "mensual"){
				$dias_transcurridos_entre_mes = cal_days_in_month(CAL_GREGORIAN,$fecha_explode[1],$fecha_explode[0]);
			}else{
				//En el caso de no ser mensual, le ponemos los mismos dias
				$dias_transcurridos_entre_mes = $dias;
			}
			$fecha_venc = ($i > 1) ? cFechas::Sumar($fecha_venc,$dias_transcurridos_entre_mes) : $fecha_venc;
			$cuotas[$i]['fecha_venc'] = $fecha_venc;
			$cuotas[$i]['dias'] = $dias_transcurridos_entre_mes;
			
			// Calculo , formateo el capital y lo resto al capital total restante
			$capital = calcHelpers::PAGOPRIN($tasa_calculo/12,$i,$plazo,($valor_prestamo*-1));
			$cuotas[$i]['capital'] = number_format($capital,2,'.','');
			$saldo_capital = $saldo_capital-$capital;
			
			// Calculo , formateo el interes y lo sumo al interes total
			$interes = (calcHelpers::PAGOINT($tasa_calculo/12,$i,$plazo,(($valor_prestamo*-1)))/(1+$iva));
			$cuotas[$i]['interes_cuota'] = number_format($interes,2,'.','');
			$total_interes = $total_interes + $interes;

			// Calculo, sumo y formateo el iva
			$monto_iva = $interes*$iva;
			$cuotas[$i]['iva_interes_cuota'] = number_format($monto_iva,2,'.','');
			$total_iva = $total_iva + $monto_iva;
			// Sumo los montos calculados anteriores para conseguir el total de la cuota
			$cuotas[$i]['monto_cuota'] = number_format(round($cuotas[$i]['capital']+$cuotas[$i]['interes_cuota']+$cuotas[$i]['iva_interes_cuota']),2,'.','');
			$cuotas[$i]['saldo_final_periodo'] = number_format((($saldo_capital < 0) ? 0 : $saldo_capital),2,'.','');
		}
		
		// Seteo variables que pueden ser utilizadas luego
		$this->totales->capital = $this->capital;
		$this->totales->porc_iva = $iva*100;
		$this->totales->total = number_format(($this->capital+$total_interes+$total_iva),2,'.','');
		$this->totales->total_iva_interes = number_format(($total_iva),2,'.','');
		//$this->totales->total_interes = new stdClass;
		$this->totales->total_interes = number_format(($total_interes),2,'.','');
		$this->totales->total_intereses = number_format(($total_interes),2,'.','');
		$this->totales->interes_capital = number_format(($total_interes),2,'.','');
		//$this->totales->pagos = new stdClass;
		$this->totales->pagos = $this->plan->tipo_pagos;
		$this->totales->total_impuestos = $this->totales->total_iva_interes;
		$this->cuotas = $cuotas;
		
		$this->tasas->CFT = $this->CalcularCft();
		
		// $this->SetReg($cuotas);
		
	}

	public function calcular_refinanciacion(array $args = []):bool {
		return $this->calcular_prestamo($args);
	}

/**
* Summary. Calcula el total de impuestos y el monto de cada uno según corresponda. Excluido el IVA.
* @return float El total de impuestos.
*/
	private function Calcular_Impuestos() {
			$total = 0; // Esto almacena la sumatoria de los montos calculados.
			if (CanUseArray($this->impuestos)) {
				foreach($this->impuestos as $key => $impuesto) {
					$calculado = 0;
					if ($impuesto['calculo'] == 'FIJO') {
						$calculado = $impuesto['valor'];
					} else {
						if ($impuesto['aplicar'] == 'INTERES') { // Este impuesto aplica sobre el interés.
							if ($this->totales->total_intereses > 0) { // Solo si hay intereses sobre los cuales calcular el impuesto.
								$calculado = ($this->totales->total_intereses*($impuesto['valor']/100));
							}
						}
						if ($impuesto['aplicar'] == 'CAPITALMASINTERES') { // Este impuesto aplica sobre el total imponible.
							if ($this->totales['total_imponible'] > 0) { // Solo si hay total sobre el cual calcular el impuesto.
								$calculado = ($this->totales['total_imponible']*($impuesto['valor']/100));
							}
						}
					} // else
					$this->impuestos[$key]['total'] = $calculado;
					$total = $total + $calculado;
				} // foreach
			} // if
			return $total;
	}

/**
* Summary. Del plan actual, tomar los cargos e impuestos y preparar la lista de ellos. Excepto el IVA_insc que es especial.
* @return null
*/
	private function GetAllAditionals() {
		$this->cargos = array();
		$this->impuestos = array();
		if (!$this->plan->existe) { return; }
		$lista = $this->plan->GetFullListCargos('HAB');
		if (!CanUseArray($lista)) { return; }
		foreach($lista as $item) {
			if (($item->estado == 'HAB') and (strtolower($item->alias) != 'iva_insc')) {
				if ($item->tipo == 'IMP') {
					$this->impuestos[] = array(
							 'id'=>$item->id,
						 'nombre'=>$item->alias,
						  'valor'=>$item->valor,
						'calculo'=>$item->calculo,
						'aplicar'=>$item->aplicar,
						  'total'=>0.0
					);
				} else {
					$this->cargos[] = array(
							 'id'=>$item->id,
						 'nombre'=>$item->alias,
						  'valor'=>$item->valor,
						'calculo'=>$item->calculo,
						'aplicar'=>$item->aplicar,
						  'total'=>$item->valor // No estoy seguro que esto sea correcto.
					);
				} // else
			} // if
		} // foreach
	} // GetAllAditionals

}
