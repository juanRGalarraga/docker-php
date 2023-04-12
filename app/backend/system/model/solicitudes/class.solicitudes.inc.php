<?php
/**
 * Clase para el manejo de llamadas a las API's de solicitudes
 * Created: 2021-09-23
 * Author: Gastón Fernandez
 */

    require_once(DIR_model."listados".DS."class.listados.inc.php");

    class cSolicitudes extends cListados {
        function __construct()
        {
            parent::__construct();
        }

        /**
         * Summary. Obtiene una solicitud a traves de su listado
         * @param array-object $filters Array u objeto con los filtros a aplicar
         * @return object
         */

        public function Get($id){
            $result = false;
            try {
                if(!SecureInt($id)) { throw new Exception(__LINE__." EL identificador esta vacío o no es numerico.");}
                $this->GetQuery("solicitud/".$id);
                $result = $this->theData;
            } catch(Exception $e) {
                $this->SetLog($e);
            }
            return $result;
        }
        
        /**
         * Summary. Obtiene un listado de solicitudes del core
         * @param array-object $filters Array u objeto con los filtros a aplicar
         * @return object
         */
        public function GetListado($filters = null){
            $result = false;
            try {
                $result = $this->GetQuery("solicitudes/listado",$filters);
            } catch(Exception $e) {
                $this->SetLog($e);
            }
            return $result;
        }
    }