<?php
/*
	Clase ejemplo práctico de cómo derivar una clase desde cModels.
	Created: 2021-08-16
	Author: DriverOp
*/

require_once(DIR_model."class.fundation.inc.php");
require_once(DIR_model."calculos".DS."class.calculo.inc.php");
require_once(DIR_model."cuotas".DS."class.cuotas_hist.inc.php");


class cCuotas extends cModels {
	
	const tabla_cuotas = TBL_cuotas;
	public $qMainTable = TBL_cuotas;
	public $calculos = null;
	public function __construct() {
		parent::__construct();
		$this->mainTable = self::tabla_cuotas;
		$this->qMainTable = SQLQuote($this->mainTable);
		$this->ResetInstance();
		$this->calculos = new cCalculo;
	}
	
	public function Get(int $id = null):?object {
		if (is_null($id)) { return null; }
		$this->sql = "SELECT * FROM ".$this->qMainTable." WHERE `id` = ".$id;
		return parent::Get();
	}
	
	public function GetList($prestamo_id,$estados = false):?array {
		$result = null;
		if(!SecureInt($prestamo_id)){ throw new Exception("No se indico el prestamo por el cual se van a buscar las cuotas"); }
		$this->sql = "SELECT * FROM ".$this->qMainTable." WHERE `prestamo_id` = ".$prestamo_id;
		if(CanUseArray($estados)){
			$this->sql .= " AND `estado` IN ('".implode("','",$estados)."') ";
		}
		if($fila = $this->FirstQuery()){
			do {
				$result[] = $fila;
			} while ($fila = $this->Next());
		}
		return $result;
	}


	public function CreateCuotas($data,$data_prestamo){
		$result = false;
		$hist_cuotas = new cCuotasHist;
		try {
			if(!CanUseArray($data)){ throw new Exception("No hay cuotas ingresadas"); }
			if(!CanUseArray($data_prestamo)){ throw new Exception("No hay datos del prestamo"); }
			if(!is_countable($data) or count($data) <= 0){ throw new Exception(" No se indicaron cuotas del prestamo"); }
			foreach($data as $key => $value){
				$reg_cuotas = array();
				$reg['cuota_nro'] = $value['cuota_nro'];
				$reg['ref_cuota'] = $data_prestamo['prestamo_id']."-".$value['cuota_nro']."-".$data_prestamo['persona_id'];
				$reg['prestamo_id'] = $data_prestamo['prestamo_id'];
				$reg['dias'] = $value['dias'];
				$reg['tipo_moneda'] = $value['tipo_moneda'];
				$reg['saldo_inicio_periodo'] = $value['saldo_inicio_periodo'];
				$reg['fecha_venc'] = $value['fecha_venc'];
				$reg['capital'] = $value['capital'];
				$reg['interes_cuota'] = $value['interes_cuota'];
				$reg['iva_interes_cuota'] = $value['iva_interes_cuota'];
				$reg['monto_cuota'] = $value['monto_cuota'];
				$reg['saldo_final_periodo'] = $value['saldo_final_periodo'];

				if(!$last_id = $this->NewRecord($reg)){
					throw new Exception(" No se pudo crear la cuota indicada");
				}
				
				$reg_history_cuota = array();
				$reg_history_cuota = array_merge($reg_history_cuota,$reg);
				$reg_history_cuota['cuota_id'] = $last_id;
				$reg_history_cuota['orden'] = 1;
				$reg_history_cuota['operacion'] = "Originación";
				if(!$hist_cuotas->CreateHistorial($reg_history_cuota)){
					throw new Exception(" No se pudo crear el historial para la cuota con id ".$last_id);
				}
			}
			$result = true;
			
		} catch(Exception $e) {
			$this->SetError($e);
			return false;
		}
		return $result;
	}

	
	public function SetValoresMora($values){
		$result = false;
		$reg = array();
		try {
			if(!CanUseArray($values)){ throw new Exception(" No se indico el estado"); }
			if(!$this->existe){ throw new Exception(" No hay seteado un prestamo"); }
			if(isset($values['monto_mora'])){ 
				$this->monto_mora = $values['monto_mora'];
			}
			if(isset($values['total_iva_mora'])){ 
				$this->total_iva_mora = $values['total_iva_mora'];
			}
			$result = $this->Set();
		}  catch(Exception $e) {
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

	public function GetCuotaAPagarByPrestamo($prestamo_id)
	{
		$result = false;
		try {
			if (!SecureInt($prestamo_id)) { throw new Exception('El identificador de prestamo, no puede llegar como parametro vacio.'); }
			$this->sql = "SELECT `id` FROM ".$this->qMainTable." WHERE `prestamo_id` = ".$prestamo_id." AND `estado` != 'CANC' LIMIT 1" ;
			if($response = $this->FirstQuery($this->sql)){
				$result = $this->Get($response['id']);
			}
		} catch (Exception $e) {
			$this->SetErrorEx($e);
		}
		return $result;
    }
}