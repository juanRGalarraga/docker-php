<?php
/*
	Clase ejemplo práctico de cómo derivar una clase desde cModels.
	Created: 2021-08-16
	Author: DriverOp
*/

require_once(DIR_model."class.fundation.inc.php");


class cPrestamosHist extends cModels {
	
	const tabla_prestamos_hist = TBL_prestamos_hist;
	public $qMainTable = TBL_prestamos_hist;
	public $reg = null;
	
	public function __construct() {
		parent::__construct();
		$this->mainTable = self::tabla_prestamos_hist;
		$this->qMainTable = SQLQuote($this->mainTable);
		$this->ResetInstance();
	}
	
	public function Get(int $id = null):?object {
		if (is_null($id)) { return null; }
		$this->sql = "SELECT * FROM ".$this->qMainTable." WHERE `id` = ".$id;
		return parent::Get();
	}

	public function GetByPrestamo(int $id = null):?object {
		if (is_null($id)) { return null; }
		$this->sql = "SELECT * FROM ".$this->qMainTable." WHERE `prestamo_id` = ".$id." ORDER BY `orden_hist` ASC ";
		return parent::Get();
	}
	
	public function GetList():?object {
		$result = null;
		$this->sql = "SELECT * FROM ".$this->qMainTable." WHERE `estado` = 'CANC';";
		$result = $this->FirstQuery();
		return $result;
	}


	public function CreateHistorial($reg){
		$result = false;
		$reg_prestamos_hist = array();
		try {
			if(!CanUseArray($reg)){ throw new Exception("No hay datos del prestamo "); }
			if(!SecureInt($reg['prestamo_id'])){ throw new Exception("No se indico el id de prestamo"); }
			$columnas = $this->GetColumnsNames();
            unset($columnas['id']);//No nos interes darle un id personalizado...
			$data = null;
			foreach($reg as $key => $value){
				if(isset($columnas[$key])){
					$data[$key] = $value;
				}
			}
			$data['orden_hist'] = 1;
			if(!$result = $this->NewRecord($data)){
				throw new Exception("No se pudo crear el historial del prestamo ");
			}

		} catch(Exception $e) {
			$this->SetError($e);
			return false;
		}
		return $result;
	}


	public function AddHistorial($reg,$hist_id){
		$result = false;
		$reg_prestamos = array();
		$prestamos = new cPrestamos;
		$reg_prestamos_hist = array();
		try {
			if(!CanUseArray($reg)){ throw new Exception("No hay datos de la solicitud"); }
			if(!SecureInt($hist_id)){ throw new Exception("No se indico el plan"); }
			if(!$this->Get($hist_id)){ throw new Exception("No se encontro el historial a buscar"); }
			// if(!$this->existe){ throw new Exception("No se realizo ningun get antes de añadir el historial "); }
			
			$columnas = $this->GetColumnsNames();
            unset($columnas['id']);//No nos interes darle un id personalizado...
			$data = null;
			
			foreach($reg as $key => $value){
				if(isset($columnas[$key])){
					$data[$key] = $value;
				}
			}
			
			$data['orden_hist'] = $this->orden_hist + 1;
			$data['prestamo_id'] = $this->prestamo_id;
			$data['fechahora'] = cFechas::Ahora();
			$data['porc_iva'] = $this->porc_iva;
			$data['solicitud_id'] = $this->solicitud_id;
			$data['estado'] = $this->estado;
			if(isset($data['interes_capital']) && isset($data['total_mora'])){
				$data['total_intereses'] = $data['interes_capital'] + $data['total_mora'];
			}
			
			if(isset($data['cargos_administrativos']) && isset($data['cargos_cobranza'])){
				$data['total_cargos'] = $data['cargos_administrativos'] + $data['cargos_cobranza'];
			}
			
			if(isset($data['iva_cargos_administrativos']) && isset($data['iva_cargos_cobranza'])){
				$data['total_iva_cargos'] = $data['iva_cargos_administrativos'] + $data['iva_cargos_cobranza'];
			}
			
			if(isset($data['iva_interes_capital']) && isset($data['iva_mora'])){
				$data['total_iva_interes'] = $data['iva_interes_capital'] + $data['iva_mora'];
			}

			if(isset($data['total_iva_interes']) && isset($data['total_iva_cargos'])){
				$data['total_impuestos'] = $data['total_iva_interes'] + $data['total_iva_cargos'];
			}
			if($data["total_imponible"] <= 5){
				$prestamos->Get($this->prestamo_id);
				$prestamos->CambiarEstado('CANC');
				$data['estado'] = "CANC";
				$data['estado_ant'] = $this->estado;
			}
			
			if(!$result = $this->NewRecord($data)){ 
				throw new Exception("No se pudo crear el historial del prestamo ");
			}
			
		} catch(Exception $e) {
			$this->SetError($e);
			return false;
		}
		return $result;
	}

	/**
		* Summary. Combina los métodos SetPrestamo y Get en una sola llamada.
		* @param cPrestamo Instancia de la clase cPrestamo.
		* @return bool/object.
	*/
	public function SetPrestamoAndGet(cPrestamos $prestamo) {
		$result = false;
		if ($this->SetPrestamo($prestamo)) {
			$result = $this->GetByPrestamo($prestamo->id);
		}
		return $result;
	}

	/**
	* Summary. Setter de la propiedad $prestamo
	* @param cPrestamo Instancia de la clase cPrestamo.
	*/
	public function SetPrestamo(cPrestamos $prestamo) {
		$result = false;
		try {
			$this->prestamo = null;
			if (!$prestamo->existe) { 
				throw new Exception('Préstamo asignado no existe.');
			}
			$this->prestamo = $prestamo;
			$this->reg = array(); // Borrar el registro previo si lo hay.
			
			/*
				Esto encuentra los nombres de campos en común entre las tablas prestamos y prestamos_hist.
			*/
			$camposPrest = $this->GetColumnsNamesSimplify(TBL_prestamos);
			$camposPrest = array_map('strtolower',$camposPrest);
			
			$camposPrestHist = $this->GetColumnsNamesSimplify(TBL_prestamos_hist);
			$camposPrestHist = array_map('strtolower',$camposPrestHist);
			
			unset($camposPrest[array_search('id',$camposPrest)]); // Quitar el nombre de campo id
			unset($camposPrest[array_search('prestamo_id',$camposPrest)]); // Quitar el nombre de campo prestamo_id
			$camposPrest = array_intersect($camposPrestHist, $camposPrest);
			
			foreach($camposPrest as $value) {
				$this->reg[$value] = null;
			}
			
			$result = true;
		} catch(Exception $e) {
			$this->SetError($e);
		}
		return $result;
	}


	public function Duplicate() {
		$aux = (array)$this->actualRecord;
		foreach($aux as $key => $value) {
			if (!in_array($key, ['id','fechahora','observaciones'])) {
				if (isset($this->fieldsName[$key]))
					$this->$key = $value;
			}
		}
		$this->fechahora = cFechas::Ahora();
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
	// 			$this->Update(self::tabla_prestamos_hist, $reg, "`id` = ".$this->id);
	// 		} catch(Exception $e) {
	// 			$this->SerError($e);
	// 			return false;
	// 		}
	// 	}
	// 	return true;
	// }
}