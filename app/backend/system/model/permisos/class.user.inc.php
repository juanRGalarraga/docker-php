<?php
/**
 * Clase para el manejo de los permisos por usuarios
 * @author Juan Galarraga
 * @created 2021-10-29
 */

require_once(DIR_model."class.fundation.inc.php");
class cUser extends cModels {

	private $qMainTable = '';
	
	public function __construct() {
		parent::__construct();
        $this->mainTable = TBL_backend_usuarios_permisos;
		$this->qMainTable = SQLQuote($this->mainTable);
		$this->qSecondTable = SQLQuote(TBL_backend_usuarios);
		$this->ResetInstance();
	}

    /**
     * Summary.
     * Obtiene el template de un usuario mediante el ID.
     * @param int $id ID del rol
     * @return object|null Un objeto con los datos o null en caso de no devolver algo.
     */
	
	public function GetTemplate(int $id) :? object {
		$this->sql = "SELECT * FROM $this->qMainTable WHERE `id` = $id";
		return parent::Get();
	}

    /**
     * Summary.
     * Obtiene el template de un usuario mediante el ID del usuario.
     * @param int $id ID del usuario
     * @return object|null Un objeto con los datos o null en caso de no devolver algo.
     */
	
	public function GetTemplateByUser(int $id) :? object {
		$this->sql = "SELECT * FROM $this->qMainTable WHERE `usuario_id` = $id";
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
	public function NewTemplate(array $record) :? int {
		return parent::NewRecord($record);
	}

	public function All(array $campos = []) : array {
		$sql = "SELECT * FROM $this->qSecondTable WHERE `estado` = 'HAB' ";
		return $this->GetArray($sql, false, $campos);
	}

	public function UserExists(int $id) : bool {
		$result = false;
		try {
			$sql = "SELECT * FROM $this->qSecondTable WHERE `id` = $id AND `estado` = 'HAB'";
			$this->Query($sql);
			if($this->First()){
				$result = true;
			}
		} catch (Exception $e) {
			$this->SetError($e);
		}
		return $result;
	}
}