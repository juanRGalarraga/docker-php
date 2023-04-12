<?php
/**
 * Clase para el manejo de Cobros
 * Created: 
 * Author: Tom
 */


require_once(DIR_model."listados".DS."class.listados.inc.php");

class cCobros extends cListados {
	function __construct(){
		parent::__construct();
	}


	/**
	 * Summary. Obtiene un préstamo dado su ID
	 * @param int $id El ID del préstamo a obtener un
	 * @return null|object El resultado
	 */
	public function Get(int $id){
		try {
			if(is_null(SecureInt($id))){ throw new Exception("El ID del préstamo debe ser un número entero válido"); }
			$this->GetQuery("cobros/".$id);
			if(!empty($this->theData)){ return $this->theData; }
		} catch (Exception $e) {
			$this->SetError($e);
		}
		return null;
	}

	/**
	 * Summary. 
	 * @param int $id El ID del préstamo a obtener
	 * @return null|object El resultado
	 */
	public function GetByPrestamo(int $id){
		try {
			if(is_null(SecureInt($id))){ throw new Exception("El ID del préstamo debe ser un número entero válido"); }
			$this->GetQuery("cobros/listprestamo/".$id);
			if(!empty($this->theData)){ return $this->theData; }
		} catch (Exception $e) {
			$this->SetError($e);
		}
		return null;
	}


	/**
	 * Summary. 
	 * @param int $data 
	 * @return null|object El resultado
	 */
	public function CreateCobro($data){
		try {
			if(!CanUseArray($data)){ throw new Exception("No se indicaron datos para enviar al pago"); }
			$this->PostQuery("cobros/",$data);
			if(!empty($this->theData)){ return $this->theData; }
		} catch (Exception $e) {
			$this->SetError($e);
		}
		return null;
	}

	/**
	 * Summary. 
	 * @param int $data 
	 * @return null|object El resultado
	 */
	public function CobrosAcreditar($data){
		try {
			if(!CanUseArray($data)){ throw new Exception("No se indicaron datos para enviar al pago"); }
			$this->PostQuery("cobros/acreditar",$data);
			if(!empty($this->theData)){ return $this->theData; }
		} catch (Exception $e) {
			$this->SetError($e);
		}
		return null;
	}


	/**
	 * Summary. 
	 * @param int $data 
	 * @return null|object El resultado
	 */
	public function CobrosRechazar($data){
		try {
			if(!CanUseArray($data)){ throw new Exception("No se indicaron datos para enviar al pago"); }
			$this->PostQuery("cobros/acreditar",$data);
			if(!empty($this->theData)){ return $this->theData; }
		} catch (Exception $e) {
			$this->SetError($e);
		}
		return null;
	}

	/**
	 * Summary. Obtiene un listado de préstamos en base a filtros
	 * @param array-object $filters Array u objeto con los filtros a aplicar
	 * @return object
	 */
	public function GetListado($filters = null){
		$result = false;
		try {
			$result = $this->GetQuery("cobros/list",$filters);
		} catch(Exception $e) {
			$this->SetLog($e);
		}
		return $result;
	}
}