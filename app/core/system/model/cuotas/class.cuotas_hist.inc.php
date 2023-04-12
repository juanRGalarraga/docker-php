<?php
/*
	Clase ejemplo práctico de cómo derivar una clase desde cModels.
	Created: 2021-08-16
	Author: DriverOp
*/

require_once(DIR_model."class.fundation.inc.php");
require_once(DIR_model."cuotas".DS."class.cuotas.inc.php");


class cCuotasHist extends cModels {
	
	const tabla_cuotas_hist = TBL_cuotas_hist;
	public $qMainTable = TBL_cuotas_hist;
	
	public function __construct() {
		parent::__construct();
		$this->mainTable = self::tabla_cuotas_hist;
		$this->qMainTable = SQLQuote($this->mainTable);
		$this->ResetInstance();
	}
	
	public function Get(int $id = null):?object {
		if (is_null($id)) { return null; }
		$this->sql = "SELECT * FROM ".$this->qMainTable." WHERE `id` = ".$id;
		return parent::Get();
	}
	
	public function GetList():?object {
		$result = null;
		$this->sql = "SELECT * FROM ".$this->qMainTable." WHERE `estado` = 'CANC';";
		$result = $this->FirstQuery();
		return $result;
	}

	public function GetByCuota($cuota_id){
		$result = 0;
		try {
			if(!SecureInt($cuota_id)){ throw new Exception(" No hay un entero seteado "); }
			$this->sql = " SELECT `id` FROM ".$this->qMainTable." WHERE `cuota_id` = ".$cuota_id." ORDER BY `sys_fecha_alta` DESC LIMIT 1 ";
			if($fila = $this->FirstQuery()){
				$result = $this->Get($fila->id);
			}
		} catch(Exception $e) {
			$this->SetError($e);
			return 0;
		}
		return $result;
	}

	public function GetByPrestamo($prestamo_id,$estados = false){
		$result = false;
		try {
			if(!SecureInt($prestamo_id)){ throw new Exception(" No hay un entero seteado "); }
			$this->sql = "SELECT `hist`.*, `cuotas`.`cuota_nro` ,`cuotas`.`ref_cuota`,`cuotas`.`prestamo_id`,`cuotas`.`tipo_moneda`,`cuotas`.`total_iva_mora`,`cuotas`.`fecha_venc`,`cuotas`.`interes_cuota`,`cuotas`.`fecha_cobro`
			FROM ".$this->qMainTable." as hist INNER JOIN ".SQLQuote(TBL_cuotas)." as cuotas ON `hist`.`cuota_id`=`cuotas`.`id` 
			WHERE `cuotas`.`prestamo_id` = ".$prestamo_id ." AND `hist`.`id` NOT IN (SELECT `hist1`.`id` FROM ".$this->qMainTable." as hist1 LEFT JOIN ".$this->qMainTable." as hist2 ON `hist1`.`cuota_id` = `hist2`.`cuota_id`  where `hist1`.`orden` < `hist2`.`orden`)
			ORDER BY `hist`.`cuota_id` ";

			if(CanUseArray($estados)){
				$this->sql .= " AND `hist`.`estado` IN ('".implode("','",$estados)."') ";
			}
			

			if($fila = $this->FirstQuery()){
				do {
					$result[] = $fila;
				} while ($fila = $this->Next());
				// $result = $this->Get($fila->id);
			}
		} catch(Exception $e) {
			$this->SetError($e);
			return 0;
		}
		return $result;
	}

	public function GetSobrantesByPrestamo($prestamo_id){
		$result = 0;
		try {
			if(!SecureInt($prestamo_id)){ throw new Exception(" No hay un entero seteado "); }
			$this->sql = " SELECT SUM(sobrante) as 'sobrantes' FROM ".$this->qMainTable." WHERE `prestamo_id` = ".$prestamo_id;
			if($fila = $this->FirstQuery()){
				$result = $fila->sobrantes;
			}
		} catch(Exception $e) {
			$this->SetError($e);
			return 0;
		}
		return $result;
	}

	public function GetRestantesByPrestamo($prestamo_id){
		$result = 0;
		try {
			if(!SecureInt($prestamo_id)){ throw new Exception(" No hay un entero seteado "); }
			$this->sql = " SELECT SUM(restante) as 'restantes' FROM ".$this->qMainTable." WHERE `prestamo_id` = ".$prestamo_id;
			if($fila = $this->FirstQuery()){
				$result = $fila->restantes;
			}
		} catch(Exception $e) {
			$this->SetError($e);
			return 0;
		}
		return $result;
	}


	public function CreateHistorial($reg){
		$result = false;
		$reg_prestamos_hist = array();
		try {
			if(!CanUseArray($reg)){ throw new Exception("No hay datos de la cuota "); }
			$columnas = $this->GetColumnsNames();
            unset($columnas['id']);//No nos interes darle un id personalizado...
			$data = null;
			foreach($reg as $key => $value){
				if(isset($columnas[$key])){
					$data[$key] = $value;
				}
			}
			if(!$result = $this->NewRecord($data)){ 
				throw new Exception("No se pudo crear el historial de la cuota ");
			}
		} catch(Exception $e) {
			$this->SetError($e);
			return false;
		}
		return $result;
	}


	public function AddHistorial($reg){
		$result = false;
		$cuotas = new cCuotas;
		try {
			if(!CanUseArray($reg)){ throw new Exception("No hay datos de la solicitud"); }
			if(!$this->existe){ throw new Exception("No se realizo ningun get antes de añadir el historial "); }
			$cuotas->Get($this->cuota_id);
			$columnas = $this->GetColumnsNames(TBL_cuotas_hist);
            unset($columnas['id']);//No nos interes darle un id personalizado...
			$data = null;
			foreach($reg as $key => $value){
				if(isset($columnas[$key])){
					$data[$key] = $value;
				}
			}
			$data['prestamo_id'] = $this->prestamo_id;
			$data['cuota_id'] = $this->cuota_id;
			$data['tipo_moneda'] = $this->tipo_moneda;
			$data['fecha_venc'] = $this->fecha_venc;
			$data['dias'] = $this->dias;
			$data['saldo_inicio_periodo'] = $this->saldo_inicio_periodo;
			$data['saldo_final_periodo'] = $this->saldo_final_periodo;
			$data['orden'] = $this->orden + 1;

			if($data["monto_cuota"] <= 5){
				$cuotas->CambiarEstado('CANC');
				$data['estado'] = "CANC";
				$data['estado_ant'] = $this->estado;
			}
			
			if(!$result = $this->NewRecord($data)){ 
				throw new Exception("No se pudo crear el historial de las cuotas ");
			}
			$cuotas->SetValoresMora($data);
		} catch(Exception $e) {
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
	// 			$this->Update(self::tabla_cuotas_hist, $reg, "`id` = ".$this->id);
	// 		} catch(Exception $e) {
	// 			$this->SerError($e);
	// 			return false;
	// 		}
	// 	}
	// 	return true;
	// }
}