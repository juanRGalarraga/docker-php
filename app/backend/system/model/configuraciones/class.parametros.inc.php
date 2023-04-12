<?php
/*
	Clase para manejar los parámetros y sus grupos desde el core vía Web Service.
	Created: 2021-10-26
	Author: DriverOp
*/

    require_once(DIR_model."listados".DS."class.listados.inc.php");

class cParametros extends cListados{
	
	
    function __construct() {
		parent::__construct();
	}
/**
* Summary. Devolver la lista de grupos.
* @param array $params Opciones extras de la peticion
*/
	function GetGrupos(array $params = []) {
		$result = [];
		try {
			if (isset($_SESSION['grupos']['listid'])) {
				$params['listid'] = $_SESSION['grupos']['listid'];
			}
			$result = $this->GetQuery("config/list/grupos",$params);
			$_SESSION['grupos']['listid'] = $this->listid;
		} catch(Exception $e) {
			$this->SetLog($e);
		}
		return $result;
	}
	
	
	function GetParams(array $params = []) {
		$result = [];
		try {
			if (!isset($params['listid']) and isset($_SESSION['params']['listid'])) {
				$params['listid'] = $_SESSION['params']['listid'];
			}
			$this->GetQuery("config/list",$params);
			$_SESSION['params']['listid'] = $this->listid;
		} catch(Exception $e) {
			$this->SetLog($e);
		}
		return $result;
	}

	/**
	 * Summary. Obtiene un parámetro por su nombres
	 * @param string $name El nombre del parámetro a buscar
	 * @return object
	 */
	public function GetByName(string $name):?object {
		try {
			if(empty($name)){ throw new Exception("El nombre del parámetro no puede estar vacío.");}
			$this->GetQuery("config/".$name);
			if(!empty($this->theData)){ return $this->theData; }
		} catch(Exception $e) {
			$this->SetLog($e);
		}
		return null;
	}

	/**
	 * Summary. Edita los valores de un parámetro
	 * @param int $id El Id del parámetro
	 * @param array $data Los datos a guardar
	 * @return bool
	 */
	public function Editar(int $id,array $data):?object{
		try {
			if(is_null(SecureInt($id))){ throw new Exception("Debes indicar un ID númerico"); }
			if(!is_array($data) or !CanUseArray($data)){ throw new Exception("Debes indicar un array con datos que se pueda utilizar"); }
			$this->PutQuery("config/".$id,$data);
			if(!empty($this->theData)){ return $this->theData; }
		} catch(Exception $e) {
			$this->SetLog($e);
		}
		return false;
	}

	/**
	 * Summary. Obtiene los filtros de mora para los Préstamos
	 * @return object
	 */
	public function GetMoraFilters():?array{
		try {
			$this->GetQuery("config/filtros/mora");
			if(!empty($this->theData)){ return (array)$this->theData; }
		} catch(Exception $e) {
			$this->SetLog($e);
		}
		return null;
	}
}