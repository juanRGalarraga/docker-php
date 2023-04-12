<?php
/**
 * Clase para el manejo de brokers
 * Created: 2021-11-08
 * Author: Gastón Fernandez
 */


require_once(DIR_model."listados".DS."class.listados.inc.php");

class cBrokers extends cListados {
	function __construct(){
		parent::__construct();
	}


	/**
	 * Summary. Obtiene un broker dado su ID
	 * @param int $id El ID del broker a obtener
	 * @return null|object El resultado
	 */
	public function Get(int $id){
		try {
			if(is_null(SecureInt($id))){ throw new Exception("El ID del broker debe ser un número entero válido"); }
			$this->GetQuery("brokers/".$id);
			if(!empty($this->theData)){ return $this->theData; }
		} catch (Exception $e) {
			$this->SetError($e);
		}
		return null;
	}
}