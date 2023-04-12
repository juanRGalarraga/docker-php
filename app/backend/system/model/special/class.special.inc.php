<?php
/**
 * Clase para el manejo de Cobros
 * Created: 
 * Author: Tom
 */


require_once(DIR_model."listados".DS."class.listados.inc.php");

class cSpecial extends cListados {
	function __construct(){
		parent::__construct();
	}


	/**
	 * Summary. Obtiene un préstamo dado su ID
	 * @param int $id El ID del préstamo a obtener un
	 * @return null|object El resultado
	 */
	public function GetInicio(){
		try {
			$this->GetQuery("special/inicio");
			if(!empty($this->theData)){ return $this->theData; }
		} catch (Exception $e) {
			$this->SetError($e);
		}
		return null;
	}
}