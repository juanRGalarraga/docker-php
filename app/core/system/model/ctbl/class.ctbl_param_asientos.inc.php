<?php
/**
 * Clase para el manejo de los parametros de asientos
 * Created: 2021-08-13
 * Author: Gastón Fernandez
 */
    require_once(DIR_model."class.fundation.inc.php");
    class cParamAsientos extends cModels{
        private $tabla_params = TBL_ctbl_param_asientos;

        function __construct()
        {
            parent::__construct();
        }

        /**
         * Obtiene el parametro cuyo ID sea indicado 
         */
        public function Get($id)
        {
            $result = false;
            try {
                if(!SecureInt($id)){ throw new Exception("El ID indicado no es un número"); }
                $sql = "SELECT * FROM ".SQLQuote(TBL_ctbl_param_asientos)." WHERE `id`=".$id;
                $this->Query($sql);
                if($this->raw_record = $this->First()){
                    $result = $this->raw_record;
                    $this->ParseRecord();
                }
            } catch (Exception $e) {
                $this->SetErrorEx($e);
            }
            return $result;
        }
    }
?>