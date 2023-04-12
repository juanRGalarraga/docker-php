<?php
/**
 * Clase para el manejo del historial de un préstamo
 * Created: 2021-11-08
 * Author: Gastón Fernandez
 */
require_once(DIR_model."listados".DS."class.listados.inc.php");

class cPrestamosHist extends cListados {
	function __construct(){
		parent::__construct();
	}

	/**
	 * Summary. Lista el historial de un préstamo
	 * @param int $id El ID del préstamo al que se le listara el historial
	 * @param array|oject $filters Los filtros a aplicar
	 */
	public function ListarHistorial(int $id, $filters = null):?array{
		$result = null;
		try {
			if(is_null(SecureInt($id))){ throw new Exception("El ID del préstamo debe ser un número entero válido"); }
			$result = $this->GetQuery("prestamos/historial/".$id,$filters);
		} catch(Exception $e) {
			$this->SetLog($e);
		}
		return $result;
	}
}