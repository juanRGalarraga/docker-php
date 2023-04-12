<?php
/**
 * Crea una nueva solicitud
 * Created: 2021-09-07
 * Author: Gastón Fernandez
 */
    require_once(DIR_model."solicitudes".DS."class.solicitud.inc.php");
	require_once(DIR_model."personas".DS."class.personas.inc.php");
	require_once(DIR_includes."class.passwords.inc.php");
	require_once(DIR_model."planes".DS."class.planes.inc.php");
	require_once(DIR_model."prestamos".DS."class.prestamos.inc.php");
    
    class cAprSolicitud extends cSolicitudBase{
		protected $CrearPersona = false;
        function __construct()
        {
            parent::__construct();
        }

        /**
         * Summary. Verifica que los datos en una solicitud sean suficientes para aprobarla
         * @return bool $result Indica si la solicitud puede aprobarse o no
         */
        public function verifyData(): bool{
            $result = false;
			$this->dataError = array();
            try {
                if(empty($this->solicitudData)){ throw new Exception("No hay datos de la solicitud a procesar"); }
                $dataToCheck = (is_object($this->solicitudData))?  (array)$this->solicitudData->data ?? null: $this->solicitudData['data'] ?? null;
				if(!CanUseArray($dataToCheck)){ throw new Exception("No hay datos de la solicitud a procesar"); }
                $this->dataToVerify = (array)$this->solicitudData;
                $this->currentAction = "Intento de aprobación de solicitud";
				parent::verifyData();

				//Realizamos una verificación del plan, capital y plazo
				$this->ValidateLoanData($dataToCheck);
				$result = (!CanUseArray($this->dataError));
            } catch (Exception $e) {
                $this->errorMsg = $e->getMessage();
                $this->SetError($e);
            }
            return $result;
        }

		/**
		 * Summary. Aprueba una solicitud previamentete evaluada
		 * @return bool $result
		 */
		public function Aprobar(): bool{
			$result = false;
			$reg = array();
			$prestamos = new cPrestamos;
			try {
				if(empty($this->solicitudData)){ throw new Exception("No hay datos de la solicitud a procesar"); }
				if($this->estado_solicitud != "PEND"){ throw new Exception("La solicitud no se encuentra en estado para crear el prestamo."); }
				$reg['capital'] = $this->solicitudData->data->capital ?? null;
				$reg['plan_id'] = $this->solicitudData->data->plan ?? null;
				$reg['plazo'] = $this->solicitudData->data->plazo ?? null;
				$reg['persona_id'] = $this->persona_id ?? null;
				$reg['solicitud_id'] = $this->id ?? null;
				$reg['negocio_id'] = $this->negocio_id ?? null;
				
				
				if(is_null(SecureFloat($reg['capital']))){ throw new Exception("No hay capital para aprobar la solicitud."); }
				if(is_null(SecureInt($reg['plan_id']))){ throw new Exception("No hay plan_id para aprobar la solicitud."); }
				if(is_null(SecureInt($reg['plazo']))){ throw new Exception("No hay plazo para aprobar la solicitud."); }
				if(is_null(SecureInt($reg['persona_id']))){ throw new Exception("No hay persona_id para aprobar la solicitud."); }
				if(is_null(SecureInt($reg['solicitud_id']))){ throw new Exception("No hay solicitud ID para aprobar la solicitud."); }
				if(is_null(SecureInt($reg['negocio_id']))){ throw new Exception("No hay solicitud ID para aprobar la solicitud."); }
				
				if(!$prestamos->CreatePrestamos($reg)){
					throw new Exception("No se pudo crear el crédito.");
				}
				$this->estado_solicitud = "APRO";
				$this->prestamo_id = $prestamos->id;
				$result = $this->Set();
			} catch (Exception $e) {
                $this->errorMsg = $e->getMessage();
                $this->SetError($e);
            }

			return $result;
		}

		/**
		 * Summary. Valida que el monto,plazo y plan sean válidos
		 * @param array $data Los datos a comprobar
		 */
		private function ValidateLoanData($data): bool{
			$result = true;
			$planes = new cPlanes;
			$dataPlan = null;
			try {
				if(!CanUseArray($data)){ throw new Exception("No hay datos para poder válidar el préstamo"); }
				//De todas maneras si lo de arriba fallo debo comprobar plazo, plan y capital
				$plan = SecureInt($data['plan'] ?? null);
				$plazo = SecureInt($data['plazo'] ?? null);
				$capital = SecureFloat($data['capital'] ?? null);

				if(is_null($plan) or $plan < 1) {
					$this->dataError['plan'] = "El plan debe ser indicadó con un número entero superior a 0";
					$result = false;
				}

				if(is_null($plazo) or $plazo < 1) {
					$this->dataError['plazo'] = "El plazo debe ser indicadó con un número entero superior a 0";
					$result = false;
				}

				if(is_null($capital) or $capital < 1) {
					$this->dataError['capital'] = "El capital debe ser indicadó un número mayor a 0";
					$result = false;
				}

				if(is_null($plan)){
					$this->dataError['plan'] = "El plan no fue indicado";
					$result = false;
				}
				
				if(!is_null($plan) AND !$dataPlan = $planes->Get($plan)){
					$this->dataError['plan'] = "El plan no fue encontrado";
					$result = false;
				}
				if($dataPlan){
					$min = SecureFloat($dataPlan->monto_minimo ?? null);
					$max = SecureFloat($dataPlan->monto_maximo ?? null);
					$plazo_min = $dataPlan->plazo_minimo ?? null;
					$plazo_max = $dataPlan->plazo_maximo ?? null;
					if(is_null($min)){
						$this->dataError['capital_min'] = "El capital mínimo no esta establecido en el plan";
						$result = false;
					}else{
						if($capital < $min){
							$this->dataError['capital_min'] = "El monto mínimo para el capítal debe ser de ".$min;
							$result = false;
						}
					}

					if(is_null($max)){
						$this->dataError['capital_max'] = "El capital máximo no esta establecido en el plan";
						$result = false;
					}else{
						if($capital > $max){
							$this->dataError['capital_max'] = "El monto máximo para el capítal debe ser de ".$max;
							$result = false;
						}
					}

					if(is_null($plazo_min)){
						$this->dataError['plazo_min'] = "El capital mínimo no esta establecido en el plan";
						$result = false;
					}else{
						if($plazo < $plazo_min){
							$this->dataError['plazo_min'] = "El plazo mínimo debe ser de ".$plazo_min;
							$result = false;
						}
					}

					if(is_null($plazo_max)){
						$this->dataError['plazo_max'] = "El capital máximo no esta establecido en el plan";
						$result = false;
					}else{
						if($plazo > $plazo_max){
							$this->dataError['plazo_max'] = "El plazo máximo debe ser de ".$plazo_max;
							$result = false;
						}
					}
				}
			} catch (Exception $e) {
				$result = false;
                $this->errorMsg = $e->getMessage();
                $this->SetError($e);
            }
			return $result;
		}
    }