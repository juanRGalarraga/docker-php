<?php 

require_once(DIR_model."listados".DS."class.listados.inc.php");

const TYPES_ELEMENT = array("CARGO","IMP");

class cImpuestos extends cListados{

    public $rpp = 25;
    public $pag = 1;
    public $cantidad = 1;

    public function __construct(){
        parent::__construct();
    }

    /**
     * Summary. Busca un listo para Impuestos o para Cargos
     * @param string $element del tipo de listado que se quiere traer "CARGO" o "IMPUESTO"
	 * @param array $params Filtros a aplicar
     * @return array/obj $result
     */
    public function List($element,array $params){
        $result = false;
        try{
            if (empty($element) OR !in_array($element,TYPES_ELEMENT)){ throw new Exception(__LINE__." El elemento está vacío o no es uno válido.");}
			if(!CanUseArray($params)){
				$params = array();
			}
			$params["element"] = $element;
            $result = $this->GetQuery("impuestos/list",$params);
        }catch(Exception $e){
            $this->SetError($e);
        }
        return $result;
    }

    /**
     * Summary. Crea un nuevo registro nuevo en la BDD
     * @param array $data
     * @return array/obj $result
     */
    public function Create($data){
        $result = false;
        try{
            if (!CanUseArray($data)){ throw new Exception(__LINE__." El array está vacío.");}

            if ($this->PostQuery("impuestos/",$data)){
                $result = true;
            }

        }catch(Exception $e){
            $this->SetError($e);
        }
        return $result;
    }

    /**
     * Summary. Busca un elemento por ID
     * @param int $id
     * @return array/obj $result
     */
    public function Get(int $id):?object{
        try{
            if (!SecureInt($id)){ throw new Exception(__LINE__." El id esta vacío o no es uno valido.");}

            $this->GetQuery("impuestos/".$id);
			if(!empty($this->theData) AND $this->http_nroerr == 200){ 
				return $this->theData;
			}
        }catch(Exception $e){
            $this->SetError($e);
        }
        return null;
    }

    /**
     * Summary. Crea un nuevo registro nuevo en la BDD
     * @param int $id
     * @param array $data
     * @return array/obj $result
     */
    public function Update(int $id, array $data){
        $result = false;
        try{
            if (!CanUseArray($data)){ throw new Exception(__LINE__." El array esta vacío.");}

            if ($this->PutQuery("impuestos/".$id,$data)){
                $result = true;
            }

        }catch(Exception $e){
            $this->SetError($e);
        }
        return $result;
    }
}

?>