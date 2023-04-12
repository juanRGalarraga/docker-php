<?php
/**
 * Base para las clases de solicitudes
 * Created: 2021-09-07
 * Author: Gastón Fernandez
 */

 /**
  * @banListMoment Posibles valores
  * pre='Antes de crear la solicitud'
  * mid='Mientras se crea la solicitud (al querer realizar alguna modificación)'
  * post='Al momento de realizar la aprobación de la solicitud (de contener este valor la solicitud se)'
  * De ser post la solicitud se rechazara de manera automatica
  */
	const ValuesToCheck = ['email','ip','tel','nro_doc'];
	const TranslatedValues = array(//Array con las keys correspondientes en la tabla de personas, que podría ser otra al momento de realizar la solicitud
		"tel_movil" => ["tel","tel_movil"],
		"cbu" => ["cbu"],
		"email" => ["mail","email","correo"]
	);
    const Actions = array(
        'new' => "Solicitud iniciada",
        'apro' => "Solicitud aprobada",
        'modif' => "Solicitud modificada",
        'rech' => "Solicitud rechazada"
    );
    require_once(DIR_model."class.fundation.inc.php");
	require_once(DIR_model."banlist".DS."class.banList.inc.php");
    require_once(DIR_model."personas".DS."class.personas.inc.php");
    require_once(DIR_model."personas".DS."class.personasData.inc.php");
    require_once(DIR_model."biblioteca".DS."class.biblioteca.inc.php");

    class cSolicitudBase extends cModels{
        private $tabla_solicitudes = TBL_solicitudes;
        protected $persona = array();//Los datos de la persona que tiene asignada la solicitud, o los datos de la persona a crear en caso de no haber sido creada aún
        protected $persona_data = array();//Los datos extra (cbu, email, tel, etc) de una persona que iran a la tabla personas_data
        protected $solicitudData = null;//En esta propiedad se colocaran todos los datos de las solicitudes, ya sea las que se crearan o las ya existentes
        protected $es_nuevo = false;//Indica si una solicitud es de una persona nueva o no
        protected $updateData = null;//Los datos que seran utilizados para actualizar el registro obtenido en ->Get
		protected $rechazar = false;//Indica si la solicitud debe ser rechazada, de ser true siempre sobreescribe el valor del campo solicitud_estado
        protected $CrearPersona = false;// Indica si se debe crear la persona para esta solicitud o no
        protected $currentAction = null;//Indica la acción actual que se realizo sobre la solicitud ( "NEW","MOFID","APRO" O "RECH" ). Esto para poder guardar el log correspondiente
        protected $actionType = "INFO";//Tipo de acción que se loguea en SaveLog
        protected $dataToVerify = null;//Son los datos que se verificaran a traves del metodo verifyData
        public $errorMsg = '';//Error que devolvío la excepción
        public $dataError = array();//Array con los mensajes de error de los datos encontrados
        public $checkBanList = true;//Indica si debe o no realizar una comprobación en blacklist
        public $banListMoment = "pre";//En que momento se verificara el bloqueo por banList(Referirse al comentario @banListMoment de arriba)

        function __construct()
        {
            parent::__construct();
            $this->mainTable = $this->tabla_solicitudes;
        }

        /**
         * Summary. Obtiene una solicitud dado su ID
         * @param int $id El ID de la solicitud a obtener
         * @return object Objeto con los datos de la solicitud o nulo en caso de no encontrarla
        */
        public function Get(int $id = null):?object {
			try {
				if (empty($id)) { $id = $this->id??null; }
				if (empty($id)) { throw new Exception("No se indicó ID."); }
				$this->sql = "SELECT * FROM ".SQLQuote($this->mainTable)." WHERE `id` = ".$id;
				$data = parent::Get();
				if($data){ $this->SetData($data); }
				return $data;
			} catch(Exception $e) {
				$this->SetError($e);
			}
			return null;
        }

        
        /**
         * Summary. 
         * @param 
         * @return 
        */
        public function GetCantSolicitudes() {
			$result = false;
            try {
				$this->sql = "SELECT COUNT(*) as 'cantidad' FROM ".SQLQuote($this->mainTable);
				if($fila = $this->FirstQuery($this->sql)){
                    $result = $fila->cantidad;
                }
			} catch(Exception $e) {
				$this->SetError($e);
			}
			return $result;
        }

        /**
         * Summary. Coloca los datos de la solicitud en un objeto
         * @param array-object $data Los datos a colocar como propiedad del objeto
         * @return bool $result Indica si se pudo setear los datos como propiedad del objeto
         */
        public function SetData($data):?bool{
            $result = false;
            try {
                $this->currentAction = (isset($this->id))? "MODIF":"NEW";
                if(!CanUseArray($data) AND !is_object($data)){ throw new Exception("DATA debe ser un array o un objeto que se pueda utilizar"); }
                $this->solicitudData = $data;
                $this->es_nuevo = ((!is_array($data) or !isset($data['persona_id'])) AND 
				(!is_object($data) or !isset($data->persona_id)));//Todo esto solo devuelve true o false
                if($this->es_nuevo AND isset($this->id)){ 
                    $this->persona['solicitud_id'] = $this->id;
                 }//Si es nuevo quiere decir que esta es su primera solicitud
				$result = true;
				//Solo me interesa llamar este metodo si el registro que voy a crear es nuevo
				if(!$this->existe AND strtolower($this->banListMoment) == 'pre'){
					$this->CheckBloqueos($data);
				}
            } catch (Exception $e) {
                $this->SetError($e);
            }
            return $result;
        }

        /**
         * Summary. Actualiza una solicitud, con los datos dados en $this->updateData
         * @return bool $result
         */
        protected function Actualizar():?bool {
			$result = false;
            try {
                if(!CanUseArray($this->updateData) AND !is_object($this->updateData)){ throw new Exception("No se indicaron datos para actualizar"); }
				foreach($this->updateData as $key => $value){
					$this->$key = $value;
					if (is_object($value) and isset($value->paso) and empty($this->pasoActual)) {
						$this->pasoActual = $value->paso;
					}
				}
				$result = $this->Set();
            } catch (Exception $e) {
                $this->SetError($e);
            }
			return $result;
        }

		/**
		 * Summary. Comprueba si alguno de los datos está en la lista de bloqueados
		 * @param object-array $data Los datos a comprobar
		 * @return bool
		 */
		protected function CheckBloqueos($data):?bool {
			if(!$this->checkBanList){ return false; }
			$result = false;
			$banList = new cBanList;
			$rechazar = false;
			$moment = strtolower($this->banListMoment);
			try {
				//Si debemos comprobar la lista de bloqueos
				$dataToCheck = (is_object($data))? $data->data ?? null:$data['data'] ?? null;
				foreach($dataToCheck as $key => $value){
					$key = strtolower($key);
					if(in_array($key,ValuesToCheck)){
						//Se encontro en la banList?? Rechazo la solicitud
						$banList->GetByTipo($key,$value);
						if($banList->bloqueado == 'SI'){
							$this->dataError[$key] = "El dato se encuentra en la lista de exclusiones";
							$rechazar = true;
						}
					}
				}
				
				if($rechazar){
					//Si no existe quiere decir que la solicitud es nueva, por lo que procedo a comprobar en que estado esta el banMoment
					if(!$this->existe AND $moment == 'pre'){
						$this->rechazar = true;
					}

					//Si existe no importa en que instancia, debemos rechazar
					if($this->existe AND $moment != 'post'){
						$this->rechazar = true;
					}

					//Existe y el momento debe ser justo antes de ser aprobada (updateData solo contiene los datos que deben ser modificados, por lo que en una aprobación esta vacío)
					if($this->existe AND $moment == 'post' AND empty($this->updateData)){
						$this->rechazar = true;
					}
				}
				$result = $rechazar;
			} catch (Exception $e) {
                $this->SetError($e);
            } finally {
				$banList = null;
			}
			return $result;
		}

		/**
		 * Summary. Rechaza la solicitud actual
		 * @return bool
		 */
		protected function Rechazar(){
			$result = false;
            $this->currentAction = "RECH";
			try {
				if(!$this->existe){ throw new Exception("Para utilizar este metodo debes usar ->Get primero"); }
				$this->estado_solicitud = "RECH";
                $this->actionType = "WARN";
				$this->Set();
			} catch (Exception $e) {
                $this->SetError($e);
            }
			return $result;
		}

        /**
         * Summary. Realiza la creación de la persona
         * @return int-bool Indica si la persona pudo ser creada devolviendo su ID o un bool false indicando que no pudo ser creada
         */
        public function CrearPersona(){
            $result = false;
            $persona = new cPersonas;
            $biblioteca = new cBiblioteca;
            $data_biblioteca = array();
            try {
                if(!CanUseArray($this->persona)){ throw new Exception("No hay datos para crear la persona"); }
                if(!$persona->SetPersonaData($this->persona)){ throw new Exception("No se pudo colocar los datos para crear la persona"); }
                $result = $persona->CreatePersona();
                if($result){
                    $data_biblioteca['persona_id'] = $result;
                    $data_biblioteca['nombre'] = $this->persona['nombre']."-".$this->persona['apellido'];
                    $this->persona_data['persona_id'] = $result;
                    $this->persona_id = $result;
                    $biblioteca->CreateFolderPerson($data_biblioteca);
                    $this->Set();
                    $this->Get($this->id);
                    $this->CreatePersonaData();
                }
            } catch (Exception $e) {
                $this->SetError($e);
            }
            return $result;
        }

        /**
         * Summary. Realiza la creación de los datos asociados a la persona (cbu, email, tel, etc)
         * @return int-bool
         */
        private function CreatePersonaData(){
            $result = false;
            $personaData = new cPersonasData;
            try {
                if(!CanUseArray($this->persona_data)){ throw new Exception("No hay datos para crear la persona"); }
                //Por cada dato en $this->persona_data debo crear un registro
                $persona_id = $this->persona_data['persona_id'] ?? null;
                unset($this->persona_data['persona_id']);
                if(is_null(SecureInt($persona_id))){ throw new Exception("No hay un idetificador de persona a la cual asociar los datos"); }
                
                foreach($this->persona_data as $key => $value){
					$dato = (is_array($value))? $value['valor'] ?? null:$value;
					$validado = (is_array($value))? $value['validado'] ?? 0:0;
                    $reg = array(
                        'persona_id' => $persona_id,
                        'tipo' => strtoupper($key),
                        'valor' => $dato,
						'default' => 1,
						'validado' => $validado
                    );
                    if(!$personaData->SetPersonaData($reg)){ throw new Exception("No se pudo colocar los datos asociados a la persona"); }
                    $this->persona_data[$key] = $personaData->CreateData();
                    if($this->persona_data[$key]) { $result = true; }
                }
            } catch (Exception $e) {
                $this->SetError($e);
            }
            return $result;
        }

        /**
         * Summary. Verifica que los datos en una solicitud sean suficientes para aprobarla
         * @return bool $result Indica si la solicitud puede aprobarse o no
         */
        public function verifyData(){
            $result = false;
			$this->dataError = array();
            $this->actionType = "ERROR";
            try {
                if(empty($this->dataToVerify)){ throw new Exception("No hay datos de la solicitud a procesar"); }
                $this->dataToVerify = (array)$this->dataToVerify;
                //Procedemos a realizar la validación de datos
				$dataToCheck = (is_object($this->dataToVerify))?  (array)$this->dataToVerify->data ?? null: (array)$this->dataToVerify['data'] ?? null;
				if(!CanUseArray($dataToCheck)){ throw new Exception("No hay datos de la solicitud a procesar"); }

                //¿No es nuevo? Validamos el ID de la persona
                if(!$this->es_nuevo){
					$persona_id = (is_array($this->dataToVerify))? SecureInt($this->dataToVerify['persona_id'] ?? null):null;
					if(is_null($persona_id)){
						$this->dataError['persona_id'] = "No se indico una persona la cual este vinculada con la solicitud";
					}else{
						//Validamos que la persona exista...
						$this->ValidatePersona($persona_id);
					}
                }else{
					$this->CrearPersona = true;
				}

                foreach($dataToCheck as $key => $value){
                    switch(strtolower($key)){
                        case 'nro_doc':  $this->VerifyDoc($value); break;
                        case 'tipo_doc':  $this->persona['tipo_doc'] = $value; break;
                        case 'password':  $this->VerifyPassword($value); break;
                        case 'cbu': $this->VerifyDuplicatedData($value, "CBU"); break;
						case 'verif_cbu': $this->SetVerif($value, "cbu"); break;
                        case 'email': $this->VerifyDuplicatedData($value, "email"); break;
						case 'verif_email': $this->SetVerif($value, "email"); break;
                        case 'tel': $this->VerifyDuplicatedData($value, "tel"); break;
						case 'verif_tel': $this->SetVerif($value, "tel"); break;
                        case 'nombre': $this->persona['nombre'] = $value; break;
                        case 'apellido': $this->persona['apellido'] = $value; break;
                    }
                }

                //Realizamos la comprobación de datos contra la lista de bloqueos
                $this->CheckBloqueos($this->dataToVerify);
                if($this->rechazar){
                    $this->Rechazar();
                    return false;
                }
				//
				if(!CanUseArray($this->dataError)){
					$result = true;
                    $this->currentAction = "Se validaron los datos correctamente";
                    $this->actionType = "INFO";
				}

            } catch (Exception $e) {
                $this->errorMsg = $e->getMessage();
                $this->SetError($e);
            }
            return $result;
        }

		/**
		 * Summary. Valida que el documento en una solicitud sea válido y no exista para otra persona
		 * @param string $doc El documento a comprobar
		 * @return bool $result 
		 */
		private function VerifyDoc($doc){
			$result = true;
			$personas = new cPersonas;
			if(empty($doc)){
				$this->dataError['nro_doc'] = "no puede estar vacío";
				$result = false;
			}

			if($result AND !cCheckInput::DNI($doc)){
				$this->dataError['nro_doc'] = "El documento ingresado no es válido";
				$result = false;
			}

            $persona_id = (is_array($this->solicitudData))? $this->solicitudData['persona_id'] ?? null: $this->solicitudData->persona_id ?? null;
			//Encontre una persona con este documento, coloco result en false
			if($result AND $personas->GetBy("nro_doc",$doc)){
                if($persona_id != $personas->id){
                    $this->dataError['nro_doc'] = "Ya existe una persona con este número de documento";
                    $result = false;
                }
			}

			//Si el resultado final es true, lo coloco en el registro a utilizar para crear la persona
			if($result){
				$this->persona['nro_doc'] = $doc;
			}
			return $result;
		}

		/**
		 * Summary. Valida que la contraseña presente en la solicitud cumpla el requisito de ser válida
		 * @param string $password La contraseña a comprobar
		 * @return bool $result
		 */
		private function VerifyPassword($password){
			$result = true;
			$checker = new cPasswords;
			if(empty($password)){
				$this->dataError['password'] = "La contraseña no puede estar vacía";
				$result = false;
			}

			//La contraseña no cumple el estandar
			if($result AND !$checker->CheckSimplifiedPassword($password)){
				$this->dataError['password'] = "La contraseña no cumple los estandares de seguridad";
				$result = false;
			}

			//Si el resultado final es true, lo coloco en el registro a utilizar para crear la persona
			if($result){
				$this->persona['password'] = $password;
			}
			return $result;
		}

		/**
		 * Summary. Verifica que el $Dato ingresado en la solicitud sea válido y no pertenezca a otra persona
		 * @param string $dato El Dato a comprobar
		 * @param string $tipo El Tipo de dato que se comprobara
		 * @return bool $result
		 */
		private function VerifyDuplicatedData($dato, $tipo){
			$result = true;
			$personas = new cPersonas;
            $personasData = new cPersonasData;
			$func = null;
			if(empty($tipo)){ return false; }
			$tipo = strtolower($tipo);
			switch($tipo){
				case 'email':
						$func  = "Email";
					break;
				case 'tel':
						$func  = "Tel";
					break;
				case 'cbu':
						$func  = "CBU";
					break;
			}
			if(empty($func)){ return false; }

			if(empty($dato)){
				$this->dataError[$tipo] = "El ".$tipo." no puede estar vacío";
				$result = false;
			}

			//Verifico que sea válido
			if($result AND !cCheckInput::$func($dato)){
				$this->dataError[$tipo] = "El ".$tipo." no es válido";
				$result = false;
			}

            //Comprobamos si otra persona ya tiene este dato
            $persona_id = (is_array($this->solicitudData))? $this->solicitudData['persona_id'] ?? null: $this->solicitudData->persona_id ?? null;
			if($result AND ($personas->GetBy($tipo,$dato) OR $personasData->GetByTipo($tipo,$dato))){
                $pid = $personas->id ?? null;
                $pid2 = $personasData->persona_id ?? null;
                //Si alguno de los 2 ID's obtenidos es distinto al indicado en la solicitud, devuelvo mensaje
                if((!is_null(SecureInt($pid)) AND $pid != $persona_id) OR (!is_null(SecureInt($pid2)) AND $pid2 != $persona_id)){
                    $this->dataError[$tipo] = "Este ".$tipo." ya pertenece a otra persona";
                    $result = false;
                }
			}

			//Si el resultado final es true, lo coloco en el registro a utilizar para crear la persona
			if($result){
				$personaKey = findKey($tipo);
				$this->persona[$personaKey] = $dato;
				$this->persona_data[$tipo] = (!isset($this->persona_data[$tipo]))? array():$this->persona_data[$tipo];
                $this->persona_data[$tipo]['valor'] = $dato;
			}
			return $result;
		}
		
		/**
		 * Summary. Establece la validación de un dato que sera agregado a los datos asociados con una persona
		 * @param string $dato El estado de la validación del dato
		 * @param string $tipo El Tipo de dato
		 * @return bool $result
		 */
		private function SetVerif($dato, $tipo){
			$validated = false;
			$tipo = strtolower($tipo);
			$dato = (!is_int($dato) AND !is_bool($dato))? strtolower($dato):$dato;
			if(CheckBool($dato)){
				$validated = (array_search($dato,VALID_TRUE_VALUES) === true);
			}
			$this->persona_data[$tipo] = (!isset($this->persona_data[$tipo]))? array():$this->persona_data[$tipo];
			$this->persona_data[$tipo]['validado'] = $dato;
		}

        /**
		 * Summary. Realiza una validación de existencia de una persona
		 * @param int $id El ID de la persona a verificar
		 * @return bool $result
		 */
		private function ValidatePersona($id){
			$result = true;
			$personas = new cPersonas;
			if(is_null(SecureInt($id))){
				$this->dataError['persona_id'] = "El ID de la persona no es un número entero válido";
				$result = false;
			}

			if($result AND !$personas->Get($id)){
				$this->dataError['persona_id'] = "La persona de esta solicitud no fue encontrada";
				$result = false;
			}
			return $result;
		}


    } // class

	/**
	*	Summary. Dada una key intenta encontrar la Key real a utilizar en la tabla personas
	*	@param string $key La clave a buscar, si no encuentra nada, devuelve la misma
	*/
	function findKey(string $key):string{
		$result = $key;
		if(!isset(TranslatedValues[$key])){
			$key = strtolower($key);
			foreach(TranslatedValues as $indice => $value){
				if(in_array($key,$value)){ $result = $indice; break; }
			}
		}
		return $result;
	}