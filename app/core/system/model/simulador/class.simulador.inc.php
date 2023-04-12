<?php
require_once(DIR_model."planes".DS."class.planes.inc.php");
$clase = CustPath(DIR_model."calculos".DS."class.calculo.inc.php");
require_once($clase);

class cSimulador {
	
	public $calculo = null;
	public $msgerr = null;
	public $totales = [];
	public $cantDecs = 2; // Cantidad de decimales en los números flotantes.
	
	public function __construct(cPlanes $plan) {
		$this->calculo = new cCalculo();
		$this->calculo->SetPlan($plan);
	}
	
	public function Calcular(array $params) {
		global $clase;
		$result = false;
		if (!is_array($params)) {
			throw new Exception('params no es array');
		}
		if (!isset($params['monto'])) { throw new Exception('Falta indicar monto'); }
		if (!isset($params['plazo'])) { throw new Exception('Falta indicar plazo'); }
		if (!isset($params['plan'])) { throw new Exception('Falta indicar plan'); }
		
		$this->calculo->capital = $params['monto'];
		$this->calculo->plazo = $params['plazo'];
		if (!$this->calculo->Calcular()) {
			cLogging::Write(__FILE__ ." ".__LINE__ ." Cálculo no se completó usando plan ".$params['plan']->id." y clase ".$clase);
			$this->msgerr = "Hubo problemas al realizar cálculos.";
			return false;
		}
		if($params['plan']->tipo_pagos == "diario"){
			$this->totales = $this->calculo->SetReg();
		}
		if($params['plan']->tipo_pagos == "unico"){
			$this->totales = $this->calculo->SetReg();
		}
		return $this->totales;
	}
	
	public function GenerarSalida($from = null) {
		$mostrar = array();
		if(CanUseArray($this->calculo->cuotas)){
			$mostrar = array(
				'Capital'=>$this->calculo->totales->capital,
				'Porc_IVA'=>$this->calculo->totales->porc_iva,
				'Total'=>$this->calculo->totales->total,
				'Total_IVA'=>$this->calculo->totales->total_iva_interes,
				'Total_Intereses'=>$this->calculo->totales->total_interes
				
			);
			$mostrar['cuotas'] = $this->calculo->cuotas;
			return $mostrar;
		}
		$fecha_pago = cFechas::Sumar(Date('Y-m-d'),$this->calculo->plazo);
		// $fecha_pago = cSideKick::GetNextBusinessDate($objeto_db, $fecha_pago); <--- Hay que desarrollarlo!
		$mostrar = array(
			'Capital'=>$this->totales['capital'],
			'Porc_IVA'=>$this->totales['porc_iva'],
			'Total'=>$this->totales['total'],
			'Total_IVA'=>$this->totales['total_iva_interes']
		);
		if (!empty($this->calculo->plan->data->TNA_Publico) and is_object($this->calculo->plan->data->TNA_Publico) and (property_exists($this->calculo->plan->data->TNA_Publico, 'valor'))) {
			$tna_publico = SecureFloat($this->calculo->plan->data->TNA_Publico->valor);
			$gastos_admin_publico = 100-$tna_publico;
			
			$mostrar['Intereses'] = $this->totales['sobrecargo']/100*$tna_publico;
			$mostrar['Gastos_Administrativos'] = $this->totales['sobrecargo']/100*$gastos_admin_publico;
			$mostrar['TNA'] = $this->totales['tasas']->TNA/100*$tna_publico;
		}
		foreach($mostrar as $key => $value) {
			if (is_numeric($value) and !is_int($value)) {
				$mostrar[$key] = number_format(RoundUp($value,2),$this->cantDecs,'.','');
			}
		}

		$mostrar['Tipo'] = $this->calculo->plan->tipo_pagos;
		$mostrar['Fecha_Pago'] = $fecha_pago;
		$mostrar['Fecha_Pago_Display'] = cFechas::SQLDate2Str($fecha_pago, CDATE_SHORT+CDATE_IGNORE_TIME);
		$mostrar['Periodo'] = number_format($this->totales['periodo'],0,'.','');
		$mostrar['Planid'] = $this->calculo->plan->id;
		$mostrar['Dias'] = $this->totales['periodo']; // Cuántos días deben pasar desde el momento del otorgamiento hasta el vencimiento efectivo.
		$mostrar['Simbolo_Moneda'] = 'ARS';
		$mostrar['Tipo_Moneda'] = 'Pesos Argentinos';
		$mostrar['Monto_Minimo'] = number_format($this->calculo->plan->monto_minimo,$this->cantDecs,'.','');
		$mostrar['Monto_Maximo'] = number_format($this->calculo->plan->monto_maximo,$this->cantDecs,'.','');
		$mostrar['Plazo_Minimo'] = $this->calculo->plan->plazo_minimo;
		$mostrar['Plazo_Maximo'] = $this->calculo->plan->plazo_maximo;
		$mostrar['Score_Minimo'] = $this->calculo->plan->score_minimo;
		$mostrar['Score_Maximo'] = $this->calculo->plan->score_maximo;
		$mostrar['Etiqueta_1'] = (!empty($this->calculo->plan->data->TNA_Publico->etiqueta))?$this->calculo->plan->data->TNA_Publico->etiqueta:'Interés';
		$mostrar['Etiqueta_2'] = (!empty($this->calculo->plan->data->Costo_Publico->etiqueta))?$this->calculo->plan->data->Costo_Publico->etiqueta:'Costos administrativos';
		$mostrar['from'] = strtoupper(substr($from,0,15));
		return $mostrar;
	}
}