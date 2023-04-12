<?php
/**
 * Crea una nueva solicitud
 * Created: 2021-09-07
 * Author: Gastón Fernandez
 */
    require_once(DIR_model."solicitudes".DS."class.solicitud.inc.php");
    
    class cNuevaSolicitud extends cSolicitudBase{
        function __construct()
        {
            parent::__construct();
        }

        /**
         * Summary. Se crea la solicitud una vez verificados los datos.
         * @return int $result El ID del registro creado o null en caso de problemas.
         */
        public function Crear():?int {
            $result = null;
            try {
                $data = $this->solicitudData;
                if(!CanUseArray($data) AND !is_object($data)){ throw new Exception("No hay datos con los cuales crear la solicitud"); }
                $reg = array();
                foreach($data as $key => $value){
                    $reg[$key] = $value;
                }

                if($result = $this->NewRecord($reg)){
                    $this->id = $result;//Esto para poder guardar el Log...
					$this->Get($result);
                }
            } catch (Exception $e) {
                $this->errorMsg = $e->getMessage();
                $this->SetError($e);
            }
            return $result;
        }

		/**
		 * Summary. Cumple la función de comprobar los datos de una solicitud antes de la creación de la misma
		 */
		public function CheckData(){
			$result = false;
			try {
				if(!CanUseArray($this->solicitudData) AND !is_object($this->solicitudData)){ throw new Exception("No hay datos para comprobar de la solicitud"); }
				$this->dataToVerify = $this->solicitudData;
				$this->checkBanList = (strtolower($this->banListMoment) == 'pre' AND $this->checkBanList);//Con esto estoy diciendo que si se compruebe la lista de exclusiones, solo sí la config lo dicta
				$this->verifyData();
				$result = (!CanUseArray($this->dataError));
			} catch (Exception $e) {
                $this->errorMsg = $e->getMessage();
                $this->SetError($e);
            }
			return $result;
		}
    }