<?php
/**
 * Crea una nueva solicitud
 * Created: 2021-09-07
 * Author: Gastón Fernandez
 */
    require_once(DIR_model."solicitudes".DS."class.solicitud.inc.php");
    
    class cModifSolicitud extends cSolicitudBase{
        function __construct()
        {
            parent::__construct();
        }

        /**
         * Summary. Realiza los cambios a la solicitud y los guarda
         * @param array-object $data Los datos que serán colocados en la solicitud
         * @return bool $result Indica si se pudo crear el registro o no
         */
        public function Modificar($data){
            $result = false;
            try {
                if(!CanUseArray($data) AND !is_object($data)){ throw new Exception("No se indicaron datos para actualizar"); }
                $this->updateData = $data;
                $reg = array();
                foreach($data as $key => $value){
                    if($key == 'data'){ continue; }
                    if(isset($this->$key) AND $value == $this->$key){ continue; }
                    $reg[$key] = $value;
                }

                $data = ((array)$data)['data'];
                $datos = (array)$this->data;
				if (CanUseArray($data)) {
					foreach($data as $key => $value){
						if(isset($datos[$key]) AND $value == $datos[$key]){ continue; }
						$reg['data'][$key] = $value;
					}
				}

				$result = $this->Actualizar();
                $this->verifyData();
            } catch (Exception $e) {
                $this->errorMsg = $e->getMessage();
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
            try {
                if(empty($this->updateData)){ throw new Exception("No hay datos de la solicitud a procesar"); }
                $dataToCheck = (is_object($this->updateData))?  (array)$this->updateData->data ?? null: $this->updateData['data'] ?? null;
				if(!CanUseArray($dataToCheck)){ throw new Exception("No hay datos de la solicitud a procesar"); }
                //Si es nuevo tengo que verificar que exista un nro_doc y una contraseña
                $this->updateData['persona_id'] = (isset($this->persona_id))? $this->persona_id:null;
                $this->dataToVerify = $this->updateData;                
				parent::verifyData();
                //Si no hay errores, devuelvo true
				if(!CanUseArray($this->dataError)){
					$result = true;
				}
            } catch (Exception $e) {
                $this->errorMsg = $e->getMessage();
                $this->SetError($e);
            }
            return $result;
        }
		
		/**
		* Summary. Agrega/modifica un dato almacenado en el JSON del campo `data` y escribe inmediatamente.
		* @param object/array $data El o los datos a almacenar.
		* @return bool.
		*/
		public function AddDataAndSet($data):bool {
			if (!$this->existe) { throw new Exception("Llamá primero a ->Get() antes de llamarme a mi!."); }
			$this->data = array_merge((array)$this->data, (array)$data);
			// ShowVar($this->data);
			$this->sys_fecha_modif = cFechas::Ahora();
			return $this->Set();
		}
    }