<?php
/**
 * Clase para el manejo de llamadas a las API's de clientes
 * Created: 2021-09-23
 * Author: Gastón Fernandez
 */

	require_once(DIR_model."listados".DS."class.listados.inc.php");

    class cClientes extends cListados {
        function __construct()
        {
            parent::__construct();
        }

		/**
		 * Summary. Obtiene un cliente dado su ID
		 * @param int $id El ID del cliente que se esta buscando
		 * @return null|object Los datos del cliente obtenido
		 */
		public function Get(int $id):?object{
			try {
				if(is_null(SecureInt($id))){ throw new Exception("El ID del cliente debe ser un número"); }
                $this->GetQuery("personas/".$id);
				if(!empty($this->theData)){
					return $this->theData;
				}
            } catch(Exception $e) {
                $this->SetLog($e);
            }
			return null;
		}

        /**
         * Summary. Obtiene un listado de clientes del core
         * @param array-object $filters Array u objeto con los filtros a aplicar
         * @return object
         */
        public function GetListado($filters = null){
            $result = false;
            try {
                $result = $this->GetQuery("personas/listado",$filters);
            } catch(Exception $e) {
                $this->SetLog($e);
            }
            return $result;
        }
    }