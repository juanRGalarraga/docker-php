<?php
/**
 * Clase para el manejo de la lista de bloqueos
 * Created: 2021-09-09
 * Author: Gastón Fernandez
 * Updated: 2021-10-14
 * Author: DriverOp
 * Summary. Corregidos errores de ortografía.
 */
    require_once(DIR_model."class.fundation.inc.php");

    class cBanList extends cModels{
        private $tabla_bans = TBL_bans;
        public $bloqueado = 'NO';//Indica si el dato obtenido esta bloqueado o no

        function __construct()
        {
            $this->mainTable = $this->tabla_bans;
            parent::__construct();
        }

        /**
         * Summary. Obtiene un registro dado el ID del mismo
         * @param int $id El ID del registro a obtener
         * @return object Objeto con los datos o nulo en caso de no encontrarla
        */
        public function Get(int $id = null):?object {
            try {
                if (is_null(SecureInt($id))) { throw new Exception("No se indicó ID."); }
                $this->sql = "SELECT * FROM ".SQLQuote($this->mainTable)." WHERE `id` = ".$id;
                return parent::Get();
            } catch(Exception $e) {
                $this->SetError($e);
            }
            return null;
        }

        /**
         * Summary. Obtiene un registro dado un tipo de dato y el dato en si
         * @param string $tipo El tipo de dato a buscar
         * @param string $dato El valor que se buscara para el tipo indicado
         * @return object $result Objeto con los datos o nulo en caso de no encontrarla
         */
        public function GetByTipo($tipo,$dato){
            $result = null;
            $this->bloqueado = 'NO';
            try {
                if (empty($tipo)) { throw new Exception("No se indicó el tipo de dato a buscar."); }
                if (empty($dato)) { throw new Exception("No se indicó el dato a buscar."); }
                $this->sql = "SELECT * FROM ".SQLQuote($this->mainTable)." WHERE LOWER(`tipo`) = LOWER('".$this->RealEscape($tipo)."') AND `valor`='".$this->RealEscape($dato)."'";
                $data = parent::Get();
                if($data){
                    $bloqueado = 'NO';
                    $expira = (is_array($data))? $data['expira'] ??  null:$data->expira ?? null;
                    $estado = (is_array($data))? $data['estado'] ??  null:$data->estado ?? null;
                    if(cFechas::LooksLikeISODateTime($expira) AND $estado == 'HAB'){
                        if($expira > cFechas::Ahora()){
                            $bloqueado = 'SI';
                        }
                    }

                    if(is_object($data)){
                        $data->bloqueado = $bloqueado;
                    }else{
                        $data['bloqueado'] = $bloqueado;
                    }
                    $this->bloqueado = $bloqueado;
                    $result = $data;
                }
            } catch(Exception $e) {
                $this->SetError($e);
            }
            return $result;
        }
    }