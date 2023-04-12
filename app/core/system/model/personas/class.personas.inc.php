<?php
/**
 * Clase para el manejo de personas
 * Created: 2021-09-07
 * Author: Gastón Fernandez
 */

require_once(DIR_model."class.fundation.inc.php");
require_once(DIR_model."personas".DS."class.personasData.inc.php");

class cPersonas extends cModels{
    const tabla_personas = TBL_personas;
	const tabla_extras = TBL_personas_data;
    
	private $personaData = null;
	public $qMainTable = '';
	public $qExtrasTable = '';
	
	public $includeExtras = false;

    function __construct()
    {
        parent::__construct();
        $this->mainTable = self::tabla_personas;
		$this->qMainTable = SQLQuote($this->mainTable);
		$this->qExtrasTable = SQLQuote(self::tabla_extras);
    }

        /**
         * Summary. Obtiene una persona dado su ID
         * @param int $id El ID de la persona a obtener
         * @return object Objeto con los datos de la solicitud o nulo en caso de no encontrarla
        */
        public function Get(int $id = null):?object {
            $result = null;
            try {
                if (is_null(SecureInt($id))) { throw new Exception("No se indicó ID."); }
				$this->sql = "SELECT `persona`.* FROM ".$this->qMainTable." AS `persona` WHERE `persona`.`id` = ".$id;
                if ($result = parent::Get()) {
					$result->extras = $this->includeExtras?$this->GetDatosExtras($id):null;
				}
            } catch(Exception $e) {
                $this->SetError($e);
            }
            return $result;
        }

        /**
         * Summary. 
         * @return 
        */
        public function GetCantClient() {
            $result = null;
            try {
				$this->sql = "SELECT COUNT(`persona`.`id`) as 'cantidad_clientes' FROM ".$this->qMainTable." AS `persona` ";
                if($fila = $this->FirstQuery($this->sql)) {
                    $result = $fila->cantidad_clientes;
                }
            } catch(Exception $e) {
                $this->SetError($e);
            }
            return $result;
        }

        /**
         * Summary. Obtiene una persona por su número de documento
         * @param string $doc El número de documento a buscar
         * @return object Objeto con los datos de la solicitud o nulo en caso de no encontrarla
         */
        public function GetByDoc($doc):?object{
            $result = null;
            try {
                if (empty($doc)) { throw new Exception("No se indicó número de documento a buscar."); }
                $this->sql = "SELECT * FROM ".$this->qMainTable." WHERE `nro_doc` = ".$doc;
                if ($result = parent::Get()){
					$result->extras = $this->includeExtras?$this->GetDatosExtras($this->id):null;
				}
            } catch(Exception $e) {
                $this->SetError($e);
            }
            return $result;
        }
		public function GetByDNI($doc) {
			return $this->GetByDoc($doc);
		}

        /**
         * Summary. Obtiene una persona por un campo y su valor
         * @param string $campo El nombre del campo buscado
         * @param string $valor El valor que debe tener ese campo
         * @return object Objeto con los datos de la solicitud o nulo en caso de no encontrarla
         */
        public function GetBy($campo,$valor):?object{
            $result = null;
            $columnas = $this->GetColumnsNames();
            try {
                if (empty($campo)) { throw new Exception("No se indico el campo a buscar."); }
                if (empty($valor)) { throw new Exception("No se indico el valor a buscar."); }
                if(isset($columnas[$campo])){
                    $this->sql = "SELECT * FROM ".$this->qMainTable." WHERE `".$this->RealEscape($campo)."` = '".$this->RealEscape($valor)."'";
					if ($result = parent::Get()){
						$result->extras = $this->includeExtras?$this->GetDatosExtras($this->id):null;
					}
                }
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
        public function CreatePersona(){
            try {
                if (!CanUseArray($this->personaData)) { throw new Exception("No se indicaron los datos a crear para la persona."); }
                return parent::NewRecord($this->personaData);
            } catch(Exception $e) {
                $this->SetError($e);
            }
            return false;
        }
/**
* Summary. Devuelve los datos extras de la otra tabla para la persona actual o la que se apunte en el parámetro.
* @param int $id opcional.
* @return array/null
*/
		public function GetDatosExtras(int $id = null):?array {
			$result = null;
			if (empty($id)) { $id = $this->id; }
			if (empty($id)) { return $result; }
			$personas_data = new cPersonasData;
			try {
				$result = $personas_data->GetAll($id);
			} catch(Exception $e) {
				$this->SetError($e);
			} finally {
				$personas_data = null;
			}
			return $result;
		}
/**
* Summary. Devuelve el CBU por omisión de la persona actual.
*/
		public function GetCBU():?string {
			$result = null;
            try {
				if (!$this->existe) { throw new Exception("No hay persona activa"); }
				$personaData = new cPersonasData;
				$personaData->sql = "SELECT * FROM ".$personaData->qMainTable." WHERE `persona_id` = ".$this->id." AND `tipo` = 'CBU' AND `estado` = 'HAB' ORDER BY `default`, `sys_fecha_modif` DESC LIMIT 1;";
				if ($personaData->FirstQuery()) {
					$result = $personaData->valor;
				}
            } catch(Exception $e) {
                $this->SetError($e);
            }
			return $result;
		}
}