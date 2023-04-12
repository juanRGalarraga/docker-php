<?php
/**
 * Clase para el manejo de las plantillas de permisos
 * @author Juan Galarraga
 * @created 2021-11-01
 */

require_once(DIR_model."class.fundation.inc.php");
class cTemplate extends cModels {

	private $qMainTable = '';
    private $qSecondTable = '';
	private const PERMISOS = ["r" => "read", "u" => "update", "c" => "create", "d" => "delete"];
	
	public function __construct() {
		parent::__construct();
        $this->mainTable = TBL_plantillas_permisos;
		$this->qMainTable = SQLQuote($this->mainTable);
		$this->qSecondTable = SQLQuote(TBL_contenido);
		$this->ResetInstance();
	}

    /**
     * Summary.
     * Obtiene una plantilla mediante el ID.
     * @param int $id ID del rol
     * @return object|null Un objeto con los datos o null en caso de no devolver algo.
     */
	
	public function GetPlantilla(int $id) :? object {
		$this->sql = "SELECT * FROM $this->qMainTable WHERE `id` = $id AND `estado` = 'HAB' ";
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
	public function NewPlantilla(array $record) :? int {
		return parent::NewRecord($record);
	}

	public function All(array $campos = []) : array {
		$sql = "SELECT * FROM $this->qMainTable WHERE `estado` = 'HAB' ";
		return $this->GetArray($sql, false, $campos);
	}

    /**
	 * Obtiene un listado con todos los contenidos habilitados que entran en la zona de permisos.
	 * Sirve para generar la plantilla por defecto.
	 * @return array|null
	 */

	public function GetTemplate($parent = 0)	{
		$result = array();
		try {
			$sql = "SELECT `id`, `alias`, `nombre`, `parent_id` 
                    FROM $this->qSecondTable 
                    WHERE `estado` = 'HAB' AND `parent_id` = $parent AND `permit` = true
                    ORDER BY `id` ASC;";
			
			$res = $this->Query($sql);
			if ($this->error) {
				throw new Exception(__LINE__ . " DBErr: " . $this->errmsg);
			}

			while ($fila = $this->Next($res)) {
				$alias = $fila->alias;
				$nombre = $fila->nombre;
				$result[$fila->id] = array(
					'id' => $fila->id,
					'alias' => $alias,
					'nombre' => $nombre,
					'permisos' => [
						'r' => 'r',
						'c' => 'c',
						'u' => 'u',
						'd' => 'd'
					]
				);
			} // while

			if (!empty($result)) {
				foreach ($result as $key => $value) {
					if($childs = $this->GetTemplate($value['id'])){
						$result[$value['id']]['childs'] = $childs;
					}
				}

				$result = json_decode(json_encode($result, JSON_HACELO_BONITO_CON_ARRAY));
			}
		} catch (Exception $e) {
			$this->SetError($e);
		}
		return $result;
	} // function GetMenuItems

	/**
	 * Pregunta si un permiso existe en un plantilla para un determinado contenido
	 * @param string $action - Tipo de permiso (r => read, u => update, c => create, d => delete)
	 * @param array $template - Plantilla con los permisos
	 * @param int $contentID - ID del contenido sobre el cual pregunta
	 * @return bool $result - True si tiene permiso, de lo contrario false.
	 */

	public function PermitExists(string $action, $template, int $contentId) : bool {
		$result = false;

		if( !$template ) {
			cLogging::Write(__LINE__." El template llegó vacío!");
			return $result;
		}

		if( !array_key_exists($action, self::PERMISOS) ){
			cLogging::Write(__LINE__." $action no es un tipo de permiso válido. Intenta con: ".print_r(self::PERMISOS));
			return $result;
		}

		foreach($template as $value){
			if($value->id == $contentId){
				if(isset($value->permisos) and property_exists($value->permisos, $action)){
					$result = true;
				} else {
					if( isset($value->childs) and !empty($value->childs) ){
						$this->PermitExists($action, $value->childs, $contentId);
					}
					if(isset($value->permisos) and !property_exists($value->permisos, $action)){

					}
				}
			} else {
				if( isset($value->childs) and !empty($value->childs) ){
					$this->PermitExists($action, $value->childs, $contentId);
				}
			}
		}

		return $result;
	}
}