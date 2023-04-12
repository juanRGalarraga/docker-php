<?php
/**
 * Clase para el manejo de préstamos
 * Created: 2021-11-04
 * Author: Gastón Fernandez
 */


require_once(DIR_model."listados".DS."class.listados.inc.php");

class cCuotas extends cListados {
	function __construct(){
		parent::__construct();
	}

	/**
	 * Summary. Obtiene un préstamo dado su ID
	 * @param int $id El ID del préstamo a obtener un
	 * @return null|object El resultado
	 */
	public function GetByPrestamo(int $id,$estados = null) {
		try {
			if(is_null(SecureInt($id))){ throw new Exception("El ID del préstamo debe ser un número entero válido"); }
			$this->GetQuery("cuotas/".$id,$estados);
			if(!empty($this->theData)){ return $this->theData; }
		} catch (Exception $e) {
			$this->SetError($e);
		}
		return null;
	}

	/**
	 * Summary. Obtiene un préstamo dado su ID
	 * @param int $id El ID del préstamo a obtener un
	 * @return null|object El resultado
	 */
	public function GetHistByPrestamo(int $id,$estados = null) {
		try {
			if(is_null(SecureInt($id))){ throw new Exception("El ID del préstamo debe ser un número entero válido"); }
			$this->GetQuery("cuotas/history/".$id,$estados);
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
			$result = $this->GetQuery("cuotas/listado",$filters);
		} catch(Exception $e) {
			$this->SetLog($e);
		}
		return $result;
	}
}