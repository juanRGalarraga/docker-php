<?php
/**
 * Clase para el manejo de los datos asociados a la persona de la tabla personas_data
 * Created: 2021-09-13
 * Author: Gastón Fernandez
 */

require_once(DIR_model."class.fundation.inc.php");

class cPersonasData extends cModels{
    private $tabla_personasData = TBL_personas_data;
    private $personaData = null;
	
	public $qMainTable = '';

    function __construct()
    {
        parent::__construct();
        $this->mainTable = $this->tabla_personasData;
        $this->qMainTable = SQLQuote($this->tabla_personasData);
    }

        /**
         * Summary. Obtiene una persona dado su ID
         * @param int $id El ID de la persona a obtener
         * @return object Objeto con los datos de la solicitud o nulo en caso de no encontrarla
        */
        public function Get(int $id = null):?object {
            $result = false;
            try {
                if (is_null(SecureInt($id))) { throw new Exception("No se indicó ID."); }
                $this->sql = "SELECT * FROM ".$this->qMainTable." WHERE `id` = ".$id;
                $result = parent::Get();
            } catch(Exception $e) {
                $this->SetError($e);
            }
            return $result;
        }
		
		/**
		* Summary. Devuelve en un array todos los datos de la persona apuntada por el parámetro. Alternativamente, devolver solo de un tipo.
		* @param int $id El ID de la persona.
		* @param string $tipo opcional. El tipo de dato que interesa.
		* @return array/null.
		*/
        public function GetAll(int $id, string $tipo = null):?array {
			$result = array();
			$sql = "SELECT `id`, `tipo`, `valor`, `validado`, `default`, `extras`, `duplicado` FROM ".$this->qMainTable." WHERE `persona_id` = ".$id." ";
			if (!empty($tipo)) {
				$tipo = strtoupper(trim($tipo));
				$sql .= "AND `tipo` = '".$this->RealEscape($tipo)."' ";
			}
			$sql .= "ORDER BY `tipo` ASC, `default` DESC;";
			$this->Query($sql, true);
			if ($this->cantidad > 0) while($result[] = $this->Next()) { }
			return array_filter($result);
		}

        /**
         * Summary. Dado un tipo de dato y un valor devuelve el registro encontrado
         * @param string $tipo El tipo de dato buscado
         * @param string $valor el valor buscado para este tipo
         * @param int $persona_id El id de la persona de la cual se quiere bucar el dato.
         * @return bool-object
         */
        public function GetByTipo($tipo, $valor, int $persona_id = null){
            $result = false;
            try {
                if (empty($tipo)) { throw new Exception("No se indico el tipo de valor a buscar."); }
                if (empty($valor)) { throw new Exception("No se indico el valor a buscar."); }
                $this->sql = "SELECT * FROM ".$this->mainTable." WHERE LOWER(`tipo`) = '".$this->RealEscape(strtolower($tipo))."' AND `valor`='".$this->RealEscape($valor)."' ";
				if (!empty($persona_id)) {
					$this->sql = "AND `persona_id` = ".$persona_id;
				}
                $result = parent::Get();
            } catch(Exception $e) {
                $this->SetError($e);
            }
            return $result;
        }

        /**
         * Summary. Coloca los datos para crear la persona en personaData
         * @param array-objecy $reg
         */
        public function SetPersonaData($reg){
            $result = false;
            $columnas = $this->GetColumnsNames();
            unset($columnas['id']);//No nos interes darle un id personalizado...
            try {
                if(!CanUseArray($reg) AND !is_object($reg)){ throw new Exception("No se puede utilizar el registro dado o esta vacío"); }
                $data = null;
                foreach($reg as $key => $value){
                    if(isset($columnas[$key])){
                        $data[$key] = $value;
                    }
                }
                if(CanUseArray($data)){
                    $this->personaData = $data;
                    $result = true;
                }
            } catch(Exception $e) {
                $this->SetError($e);
            }
            return $result;
        }

        /**
         * Summary. Crea una persona dado un registro en la propiedad personaData
         * @return bool-int $result El ID del registro insertado o un bool indicado en estado de la creación
         */
        public function CreateData(){
            try {
                if (!CanUseArray($this->personaData)) { throw new Exception("No se indicaron los datos a crear los datos asociados a la persona."); }
                if(is_null(SecureInt($this->personaData['persona_id'] ?? null))){ throw new Exception("No se indico el ID de la persona asociada a los datos."); }
                return parent::NewRecord($this->personaData);
            } catch(Exception $e) {
                $this->SetError($e);
            }
            return false;
        }       
}