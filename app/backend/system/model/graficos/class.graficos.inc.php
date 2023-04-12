<?php
/**
 * Clase para el manejo de las visitas
 * Created: 2021-10-26
 * Author: Tom
 */

    require_once(DIR_wsclient."class.wsv2Client.inc.php");

    class cVisitas extends cWsV2Client{
        function __construct()
        {
            parent::__construct();
        }

        /**
         * Summary. 
         * @param array-object $filters Array u objeto con los filtros a aplicar
         * @return object
         */
        public function GetVisitas($data){
            $result = false;
            try {
                $this->GetQuery("visitas/list",$data);
                if(!empty($this->theData) AND $this->http_nroerr == 200){
                    $result = $this->theData;
                }
            } catch(Exception $e) {
                $this->SetLog($e);
            }
            return $result;
        }

        public function GenerarMasiveVisites(){
            $result = false;
            try {
                $this->GetQuery("visitas/generatemasive");
                if(!empty($this->theData) AND $this->http_nroerr == 200){
                    $result = $this->theData;
                }
            } catch(Exception $e) {
                $this->SetLog($e);
            }
            return $result;
        }
    }