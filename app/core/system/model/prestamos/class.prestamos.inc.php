<?php
/*
	Clase para manejar préstamos.
	Created: 2021-11-02
	Author: Tomás
	
	Updated: 2021-11-05
	Author: DriverOp
	Desc:
		Una vez creado el préstamo, leerlo para tener sus valores como propiedades del objeto.
*/

require_once(DIR_model."class.fundation.inc.php");
require_once(DIR_model."calculos".DS."class.calculo.inc.php");
require_once(DIR_model."prestamos".DS."class.prestamos_hist.inc.php");
require_once(DIR_model."cuotas".DS."class.cuotas.inc.php");


class cPrestamos extends cModels {
	
	const tabla_prestamos = TBL_prestamos;
	public $qMainTable = TBL_prestamos;
	public $calculos = null;
	public function __construct() {
		parent::__construct();
		$this->mainTable = self::tabla_prestamos;
		$this->qMainTable = SQLQuote($this->mainTable);
		$this->ResetInstance();
		$this->calculos = new cCalculo;
	}
	
	public function Get(int $id = null):?object {
		if (is_null($id)) { return null; }
		$this->sql = "SELECT * FROM ".$this->qMainTable." WHERE `id` = ".$id;
		return parent::Get();
	}

	public function GetPrestado(){
		$result = false;
		try {
			$this->sql = "SELECT SUM(`total_imponible`) as 'total' , `tipo_moneda` FROM ".$this->qMainTable;
			if($fila = $this->FirstQuery($this->sql)) {
				if(is_numeric($fila->total)){ 
					$result = number_format($fila->total,2,".","");
					if(isset($fila->money_converted) && !empty($fila->money_converted) && $fila->money_converted != $fila->tipo_moneda){
						$result = number_format($fila->converted_total,2,".","");
					}
				}
			}
		} catch(Exception $e) {
			$this->SetError($e);
			return false;
		}
		return $result;
	}
	
	public function GetList():?object {
		$result = null;
		$this->sql = "SELECT * FROM ".$this->qMainTable." ";
		$result = $this->FirstQuery();
		return $result;
	}

	private function SetDataDailyPrestamo($data){
		$reg = array();
		$reg["persona_id"] = (isset($data['persona_id']) && !empty($data['persona_id'])) ? $data['persona_id'] : null;
		$reg["tenant"] = DEVELOPE_NAME;
		$reg["negocio_id"] = (isset($data['negocio_id']) && !empty($data['negocio_id'])) ? $data['negocio_id'] : null;
		$reg["plan_id"] = (isset($data['plan_id']) && !empty($data['plan_id'])) ? $data['plan_id'] : null;
		$reg["pais_origen"] = "ARS";
		$reg["estado"] = "PEND";
		$reg["estado_ant"] = "PEND";
		$reg["fechahora_emision"] = cFechas::Ahora();
		$reg["fecha_vencimiento"] = cFechas::Sumar($reg["fechahora_emision"],$data['plazo']).' 23:59:59';
		$reg["calculo"] = (isset($this->calculos->calculo) && !empty($this->calculos->calculo)) ? $this->calculos->calculo : null;
		$reg["periodo"] = (isset($data['plazo']) && !empty($data['plazo'])) ? $data['plazo'] : null;
		$reg["capital"] = (isset($data['capital']) && !empty($data['capital'])) ? $data['capital'] : null;
		$reg["total_imponible"] = (isset($data['capital']) && !isset($this->calculos->totales->sobrecargo)) ? $data['capital']+$this->calculos->totales->sobrecargo : null;
		$reg["total_intereses"] = (isset($this->calculos->totales->total_intereses) && !empty($this->calculos->totales->total_intereses)) ? $this->calculos->totales->total_intereses : null;
		$reg["interes_capital"] = (isset($this->calculos->totales->interes_capital) && !empty($this->calculos->totales->interes_capital)) ? $this->calculos->totales->interes_capital : null;
		$reg["total_mora"] = 0;
		$reg["total_cargos"] = (isset($this->calculos->totales->total_cargos) && !empty($this->calculos->totales->total_cargos)) ? $this->calculos->totales->total_cargos : null;
		$reg["cargos_administrativos"] = (isset($this->calculos->totales->cargos_admin) && !empty($this->calculos->totales->cargos_admin)) ? $this->calculos->totales->cargos_admin : null;
		$reg["cargos_cobranza"] = (isset($this->calculos->totales->cargos_cobranza) && !empty($this->calculos->totales->cargos_cobranza)) ? $this->calculos->totales->cargos_cobranza : null;
		$reg["total_impuestos"] = (isset($this->calculos->totales->total_impuestos)) ? $this->calculos->totales->total_impuestos : null;
		$reg["total_iva"] = (isset($this->calculos->totales->total_iva_interes) && !empty($this->calculos->totales->total_iva_interes)) ? $this->calculos->totales->total_iva_interes : null;
		$reg["total_iva_interes"] = (isset($this->calculos->totales->total_iva_interes) && !empty($this->calculos->totales->total_iva_interes)) ? $this->calculos->totales->total_iva_interes : null;
		$reg["iva_interes_capital"] = (isset($this->calculos->totales->iva_interes_capital) && !empty($this->calculos->totales->iva_interes_capital)) ? $this->calculos->totales->iva_interes_capital : null;
		$reg["tasas_impuestos"] = (isset($this->calculos->tasas_impuestos) && !empty($this->calculos->tasas_impuestos)) ? json_encode($this->calculos->tasas_impuestos) : null;
		$reg["tasas_cargos"] = (isset($this->calculos->tasas_cargos) && !empty($this->calculos->tasas_cargos)) ? json_encode($this->calculos->tasas_cargos) : null;
		$data['cargos'] = (isset($this->calculos->cargos)) ? $this->calculos->cargos : null;
		$reg["data"] = (isset($data['cargos'])) ? json_encode($data['cargos']) : null;
		$reg["total_iva_cargos"] = (isset($this->calculos->totales->total_iva_cargos)) ? $this->calculos->totales->total_iva_cargos : null;
		$reg["iva_cargos_administrativos"] = (isset($this->calculos->totales->iva_cargos_admin)) ? $this->calculos->totales->iva_cargos_admin : null;
		$reg["iva_cargos_cobranza"] = (isset($this->calculos->totales->cargos_cobranza)) ? $this->calculos->totales->cargos_cobranza : null;
		$reg["tasas"] = (isset($this->calculos->tasas)) ? $this->calculos->tasas : null;
		$reg["solicitud_id"] = $data['solicitud_id'];
		return $reg;
	}

	public function CreatePrestamos($data){
		$result = false;
		$reg_prestamos = array();
		$reg_prestamos_hist = array();
		try {
			if(!CanUseArray($data)){ throw new Exception("No hay datos de la solicitud"); }
			if(!SecureInt($data['plan_id'])){ throw new Exception("No se indicó el plan"); }
			if(!SecureInt($data['persona_id'])){ throw new Exception("No se indicó la persona"); }
			if(!SecureInt($data['solicitud_id'])){ throw new Exception("No se indicó la solicitud"); }
			if(!SecureInt($data['negocio_id'])){ throw new Exception("No se indicó el negocio"); }
			
			if(empty($data['capital'])){ throw new Exception("No se indicó un capital"); }
			if(empty($data['plazo'])){ throw new Exception("No se indicó un plazo"); }
			
			$planes = new cPlanes;
			$hist_prestamos = new cPrestamosHist();
			$this->calculos->capital = $data['capital'];
			$this->calculos->plazo = $data['plazo'];
			if(!$planes->Get($data['plan_id'])){
				throw new Exception("No se pudo encontrar el plan indicado");
			}
			$this->calculos->SetPlan($planes);
			$totales = $this->calculos->Calcular();
			$this->BeginTransaction();
			$reg = $this->SetDataDailyPrestamo($data);
			if($this->calculos->plan->tipo_pagos != "unico"){
				$reg['total_imponible'] = $this->calculos->totales->total;
				$reg['tipo_prestamo'] = "CUOTAS";
			}else{
				$reg['total_imponible'] = $data['capital']+$this->calculos->totales->sobrecargo;
				$reg['tipo_prestamo'] = "UNICO";
			}
			
			if($last_id = $this->NewRecord($reg)){
				$reg_prestamos_hist["prestamo_id"] = $last_id;
				$reg_prestamos_hist["fechahora"] = cFechas::Ahora();
				$reg_prestamos_hist["porc_iva"] = (isset($this->calculos->tasas_impuestos->iva_insc)) ?$this->calculos->tasas_impuestos->iva_insc : null;
				$reg_prestamos_hist["tipo_moneda"] = (isset($this->calculos->tipo_moneda)) ?$this->calculos->tipo_moneda : "ARS";
				$reg_prestamos_hist["solicitud_id"] = $data['solicitud_id'];
				$reg_prestamos_hist["entidad"] = "ALTA";
				$reg_prestamos_hist["estado"] = "PEND";
				
				$reg_prestamos_hist["capital"] = $data['capital'];
				$reg_prestamos_hist["desembolso"] = $data['capital'];
				$reg_prestamos_hist["total_imponible"] = $reg['total_imponible'];
				$reg_prestamos_hist["total_intereses"] = (isset($this->calculos->totales->total_intereses)) ? $this->calculos->totales->total_intereses : null;
				$reg_prestamos_hist["interes_capital"] = (isset($this->calculos->totales->total_intereses)) ? $this->calculos->totales->total_intereses : null;
				
				$reg_prestamos_hist["total_cargos"] = (isset($this->calculos->totales->total_cargos)) ? $this->calculos->totales->total_cargos : null;
				$reg_prestamos_hist["cargos_administrativos"] = (isset($this->calculos->totales->cargos_admin)) ? $this->calculos->totales->cargos_admin : null;
				$reg_prestamos_hist["cargos_cobranza"] = (isset($this->calculos->totales->cargos_cobranza)) ? $this->calculos->totales->cargos_cobranza : null;
				$reg_prestamos_hist["total_impuestos"] = (isset($this->calculos->totales->total_impuestos)) ? $this->calculos->totales->total_impuestos : null;
				$reg_prestamos_hist["total_iva"] = (isset($this->calculos->totales->total_iva_interes)) ? $this->calculos->totales->total_iva_interes : null;
				$reg_prestamos_hist["total_iva_interes"] = (isset($this->calculos->totales->total_iva_interes)) ? $this->calculos->totales->total_iva_interes : null;
				$reg_prestamos_hist["iva_interes_capital"] = (isset($this->calculos->totales->iva_interes_capital)) ? $this->calculos->totales->iva_interes_capital : null;
				$reg_prestamos_hist["total_iva_cargos"] = (isset($this->calculos->totales->total_iva_cargos)) ? $this->calculos->totales->total_iva_cargos : null;
				$reg_prestamos_hist["iva_cargos_administrativos"] = (isset($this->calculos->totales->iva_cargos_admin)) ? $this->calculos->totales->iva_cargos_admin : null;
				$reg_prestamos_hist["iva_cargos_cobranza"] = (isset($this->calculos->totales->cargos_cobranza)) ? $this->calculos->totales->cargos_cobranza : null;
				
				$reg_prestamos_hist["tasas"] = (isset($this->calculos->tasas) && !empty($this->calculos->tasas)) ? json_encode($this->calculos->tasas) : null;
				$reg_prestamos_hist["tasas_impuestos"] = (isset($this->calculos->tasas_impuestos) && !empty($this->calculos->tasas_impuestos)) ? json_encode($this->calculos->tasas_impuestos) : null;
				$reg_prestamos_hist["tasas_cargos"] = (isset($this->calculos->tasas_cargos) && !empty($this->calculos->tasas_cargos)) ? json_encode($this->calculos->tasas_cargos) : null;
				$reg_prestamos_hist["observaciones"] = "Originacion del prestamo";
				if(!$result = $hist_prestamos->CreateHistorial($reg_prestamos_hist)){ throw new Exception(__FILE__."No se pudo crear el historial del prestamo "); }
				if($this->calculos->plan->tipo_pagos != "unico"){
					$cuotas = new cCuotas();
					if(!$cuotas->CreateCuotas($this->calculos->cuotas,array("prestamo_id"=>$last_id,"persona_id"=>$data['persona_id']))){
						throw new Exception(__FILE__."No se pudo crear el historial del prestamo ");
					}
				}
			}
			$this->Commit();
			if ($last_id) { $this->Get($last_id); }
		} catch(Exception $e) {
			$this->Rollback();
			$this->SetError($e);
			return false;
		}
		return $result;
	}

	public function CambiarEstado($estado){
		$result = false;
		$reg = array();
		try {
			if(empty($estado)){ throw new Exception(" No se indico el estado"); }
			if(!in_array($estado,ESTADOS_PRESTAMOS)){ throw new Exception(" El estado indicado es invalido."); }
			if(!$this->existe){ throw new Exception(" No hay seteado un prestamo"); }
			$this->estado_ant = $this->estado;
			$this->estado = $estado;
			$result = $this->Set();

		}  catch(Exception $e) {
			$this->SetError($e);
			return false;
		}
		return $result;
	}

	public function RevertirEstado(){
		$result = false;
		$reg = array();
		try {
			if(!$this->existe){ throw new Exception(" No hay seteado un prestamo"); }
			$this->estado = $this->estado_ant;
			$this->estado_ant = NULL;
			$result = $this->Set();
		}  catch(Exception $e) {
			$this->SetError($e);
			return false;
		}
		return $result;
	}
	
/**
* Summary. Guarda las modificaciones al objeto actual en la base de datos.
*/
	// public function Set() {
	// 	if (!$this->existe) { return null; }
	// 	if (count($this->fieldsName)==0) { $this->fieldsName = $this->GetColumnsNames(); }
	// 	$reg = array();
	// 	foreach($this as $property => $value) {
	// 		if (isset($this->fieldsName[$property])) {
	// 			$reg[$property] = $value;
	// 		}
	// 	}
	// 	if (count($reg) > 0) {
	// 		try {
	// 			unset($reg['id']); // Just a precaution.
	// 			if (isset($reg['sys_fecha_modif'])) { $reg['sys_fecha_modif'] = cFechas::Ahora(); }
	// 			$this->Update(self::tabla_prestamos, $reg, "`id` = ".$this->id);
	// 		} catch(Exception $e) {
	// 			$this->SerError($e);
	// 			return false;
	// 		}
	// 	}
	// 	return true;
	// }
}