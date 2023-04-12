<?php
/**
 * Clase para el manejo de llamadas a las API's de planes
 * Created: 2021-09-24
 * Author: Gastón Fernandez
 */

require_once(DIR_model."listados".DS."class.listados.inc.php");

class cPlanes extends cListados{
	function __construct()
	{
		parent::__construct();
		return $this;
	}

	/**
	 * Summary. Obtiene los datos de un plan para cotizador.
	 * @param int $id El ID del plan en cuestión
	 * @return object
	 */
	public function Get(int $id = null){
		$result = false;
		try {
			$result = $this->RawGetQuery("planes/".$id);
		} catch(Exception $e) {
			$this->SetLog($e);
		}
		return $result;
	}

	/**
	 * Summary. Obtiene todos los datos de un plan.
	 * @param int $id El ID del plan en cuestión
	 * @return object
	 */
	public function GetAll(int $id){
		$result = false;
		try {
			$result = $this->RawGetQuery("planes/todo/".$id);
		} catch(Exception $e) {
			$this->SetLog($e);
		}
		return $result;
	}

	/**
	 * Summary. Obtiene un listado de planes del core
	 * @param array-object $filters Array u objeto con los filtros a aplicar
	 * @return object
	 */
	public function GetListado($filters){
		$result = false;
		try {
			$result = $this->GetQuery("planes/listado",$filters);
		} catch(Exception $e) {
			$this->SetLog($e);
		}
		return $result;
	}

	/**
	 * Summary. Obtiene un listado de tipos de pagos
	 * @return object
	 */
	public function GetTiposPagos(){
		$result = false;
		try {
			$result = $this->RawGetQuery("planes/tipos");
		} catch(Exception $e) {
			$this->SetLog($e);
		}
		return $result;
	}
	/**
	 * Summary. Crear un nuevo plan
	 * @param array $data Los datos de creación del plan.
	 * @return object
	 */
	public function Post($data){
		$result = false;
		try {
			$result = $this->PostQuery("planes/", $data);
		} catch(Exception $e) {
			$this->SetLog($e);
		}
		return $result;
	}
	/**
	 * Summary. Modificar un plan existente
	 * @param int $id El id del plan a modificar
	 * @param array $data Los datos modificados del plan.
	 * @return object
	 */
	public function Put(int $id, $data){
		$result = false;
		try {
			$result = $this->PutQuery("planes/".$id, $data);
		} catch(Exception $e) {
			$this->SetLog($e);
		}
		return $result;
	}
}