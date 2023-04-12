<?php
/**
 * 
 * Created: 
 * Author: 
 */

    require_once(DIR_model."listados".DS."class.listados.inc.php");

    class cSimular extends cListados {
        function __construct()
        {
            parent::__construct();
        }

        /**
         * Summary. 
         * @param array-object $filters Array u objeto con los filtros a aplicar
         * @return object
         */

        public function Simular($filters = null){
            $result = false;
            try {
                $this->GetQuery("simular/",$filters);
                $result = $this->theData;
            } catch(Exception $e) {
                $this->SetLog($e);
            }
            return $result;
        }
    }