<?php
/**
 * Clase para el manejo de los Roles de los usuarios
 * @author Juan Galarraga
 * @created 2021-10-29
 */

require_once(DIR_model."class.fundation.inc.php");
class cRol extends cModels {

	private $qMainTable = '';
	
	public function __construct() {
		parent::__construct();
        $this->mainTable = TBL_backend_usuarios_roles;
		$this->qMainTable = SQLQuote($this->mainTable);
		$this->ResetInstance();
	}

    /**
     * Summary.
     * Obtiene un rol mediante el ID.
     * @param int $id ID del rol
     * @return object|null Un objeto con los datos o null en caso de no devolver algo.
     */
	
	public function GetRol(int $id) :? object {
		$this->sql = "SELECT * FROM $this->qMainTable WHERE `id` = $id";
		return parent::Get();
	}

    /**
    * Summary. Guarda las modificaciones al objeto actual en la base de datos.
    */
	public function Set(string $where = null) {
		if (!$this->existe) { return null; }
		return parent::Set($where);
	}

    /**
    * Summary. Agrega un nuevo registro a la tabla
    */
	public function NewRol(array $record) :? int {
		return parent::NewRecord($record);
	}
}