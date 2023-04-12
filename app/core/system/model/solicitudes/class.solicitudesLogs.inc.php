<?php
/**
 * Clase para el manejo del log de los cambios que tuvo una solicitud
 * Created: 2021-09-27
 * Author: Gastón Fernandez
 */

    require_once(DIR_includes."core_constants.inc.php");
    require_once(DIR_includes."class.fechas.inc.php");
    require_once(DIR_model."class.fundation.inc.php");
    class cSolicitudesLogs extends cModels{
        const tabla_logs = TBL_solicitudes_log;
		
		public $solicitud_id = null;
		public $data = null; // Para guardar los datos relevantes asociados al evento actual. Aquí debe ir los datos que el front envió para agregar a la solicitud.
		public $descripcion = null;
		public $paso = '';

        function __construct()
        {
            parent::__construct();
            $this->mainTable = self::tabla_logs;
			$this->ResetInstance();
        }

        /**
         * Summary. Crea un nuevo registro en los logs de la solicitud
         * @param array $reg Los datos del registro a crear
         */
        public function Crear(){
			$reg = array();
            try {
				if (!isset($this->tipo_evento)) { $this->tipo_evento = 'INFO'; }
				if (!in_array($this->tipo_evento, TIPOS_EVENTOS_LOG)) { $this->tipo_evento = 'INFO'; }
				
				
				$reg['paso'] = $this->paso;
				
				if (!empty($this->data) and empty($this->paso)) {
					$reg['paso'] = $this->ExtraerPaso($this->data);
				}
				
				$reg['descripcion'] = $this->descripcion;
				$reg['tipo_evento'] = $this->tipo_evento;
				$reg['data'] = json_encode($this->data, JSON_FORCE_OBJECT + JSON_BIGINT_AS_STRING + JSON_PRESERVE_ZERO_FRACTION + JSON_UNESCAPED_UNICODE);
				$reg['solicitud_id'] = $this->solicitud_id;
				$reg['fechahora'] = cFechas::AhoraMicro();
                return $this->NewRecord($reg);
            } catch (Exception $e) {
                $this->SetError($e);
            }
            return false;
        }
	
		/**
		* Summary. Esto trata de extraer el alias del paso actual desde los datos supuestamente enviados desde el front.
		*/
		private function ExtraerPaso($data) {
			if (empty($data)) { return null; }
			$salida = null;
			if (is_object($data)) { $data = (array)$data; }
			foreach($data as $key => $value) {
				if (is_string($key) and in_array(strtolower($key),['paso_alias','paso'])) {
					return $value;
				}
				if (is_object($value) or is_array($value)) {
					$salida = $this->ExtraerPaso($value);
					if (!is_null($salida)) { return $salida; }
				}
			}
			return $salida;
		}
    }