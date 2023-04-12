<?php 
    require_once(DIR_wsclient."class.wsv2Client.inc.php");

    class cGeo extends cWsV2Client{

        public function __construct()
        {
            parent::__construct();
        }

        /**
         * 
         * 
         * 
        */
        public function GetPaise(int $id){
            $result = false;
            try{
                if (!SecureInt($id)){ throw new Exception(__LINE__." EL identificador esta vacío o no es numerico.");}

                $this->GetQuery("geo/pais/".$id);

            }catch(Exception $e){
                $this->SetLog($e);
            }
        }

        /**
         * Summary. Busca un Listado de paises en la BDD del core.
         * @return obj $result
        */
        public function ListPaises(){
            $result = array();
            try{
                $data = $this->GetQuery("geo/listPaises",["rpp"=>"0"]);

                if ($data->list){
                    $result = $data->list;
                }

            }catch(Exception $e){
                $this->SetLog($e);
            }
            
            return $result;
        }

        /**
         * Summary. Busca un Listado de provincias en la BDD del core en base al ID de un país.
         * @param int $id
         * @return obj $result
        */
        public function ListProvinciasByPais(int $id){
            $result = array();
            try{
                if (!SecureInt($id)){ throw new Exception(__LINE__." EL identificador esta vacío o no es numerico.");}

                $data = $this->GetQuery("geo/listregionespais/".$id,["rpp"=>"0"]);

                if ($data->list){
                    $result = $data->list;
                }

            }catch(Exception $e){
                $this->SetLog($e);
            }
            
            return $result;
        }

        /**
         * Summary. Busca un listado de Ciudades en la BDD del core por el ID de la ciudad
         * @param int $id identificador de la Provincias.
         * @return array $result.
        */
        public function ListCiudadesByProvincias(int $id){
            $result = array();
            try{
                if (!SecureInt($id)){ throw new Exception(__LINE__." EL identificador esta vacío o no es numérico.");}

                $data = $this->GetQuery("geo/listciudadesregion/".$id,["rpp"=>"0"]);

                if ($data->list){
                    $result = $data->list;
                }

            }catch(Exception $e){
                $this->SetLog($e);
            }
            
            return $result;
        }
	/**
	* Summary. Le pide al core que devuelva una lista de ciudaded cuyo nombre contenga el string indicado, alternativamente filtrado por región.
	* @param string $str El lexema de búsqueda.
	* @param integer $region_id El id de la región entre las cuales buscar la ciudad.
	* @return array of objects.
	*/
	public function GetCiudades(string $str, int $region_id = null) {
		return $this->GetQuery("geo/list/".$region_id, ['str'=>$str]);
	}
}