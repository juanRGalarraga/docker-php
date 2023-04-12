<?php 
    /*
        Gestion de la informacion de personas
        Create: 2021-10-29
        Author: Gonza
    */
    require_once(DIR_model."class.fundation.inc.php");

class cPersonas extends cModels{

    public $persona_data;
    public $people_id;

    public function __construct()
    {
        parent::__construct();
        $this->mainTable =TBL_backend_personas;
    }

    /**
     * Summary. Buscar una persona en base a su ID
     * @param int $id de la persona
     * @return bool $result
     */
    public function GetById(int $id){
        $result = false;
        try{
            if (!SecureInt($id)){ throw new Exception(__LINE__." El id esta vacío o no es numerico.");}

            $this->sql = "SELECT * FROM ".SQLQuote($this->mainTable)." WHERE `id`= $id ";
            return $this->FirstQuery();

        }catch(Exception $e){
            $this->SetError($e);
        }
        return $result;
    }

    /**
     * Summary. Crea una persona en la tabla correspondiente
     * @param array $data
     * @return bool/int $result
     */
    public function Create(array $data){
        $result = false;
        try{
            if (!CanUseArray($data)){ throw new Exception(__LINE__." El array esta vacío o no es valido.");}
            $data["tipo_doc"] = "DNI";

            if (!$result = $this->NewRecord($data, __METHOD__)){
                throw new Exception(__LINE__." No fue posible crear el usuario.");
            }
        }catch(Exception $e){
            $this->SetError($e);
        }
        return $result;
    }

    /**
     * Summary. Edita una persona en la tabla correspondiente
     * @param array $data
     * @return bool/int $result
     */
    public function Edit(array $data){
        $result = false;
        try{
            if (!CanUseArray($data)){ throw new Exception(__LINE__." El array esta vacío o no es valido.");}
            if (!SecureInt($this->people_id)){ throw new Exception(__LINE__." El id esta vacío o no es valido.");}

            $data['sys_fecha_modif'] = cFechas::Ahora();

            if (!$result = $this->Update(TBL_backend_personas,$data," `id`=".$this->people_id)){
                throw new Exception(__LINE__." No fue posible actialoizar a la persona.");
            }

        }catch(Exception $e){
            $this->SetError($e);
        }
        return $result;
    }

    /**
     * Summary. Busca a una persona en la tabla de personas en base a su documentos
     * @param int $doc
     * @param int $id de la persona
     * @return bool $result
     */
    public function GetByNumDoc(int $doc,$id = null){
        $result = false;
        try{
            if (!SecureInt($doc)){ throw new Exception(__LINE__." El documento esta vacío o no es numerico.");}
            $id = SecureInt($id);

            $sql = "SELECT * FROM ".SQLQuote($this->mainTable)." WHERE `nro_doc`= $doc ";

            if ($id){
                $sql .="AND `id` != $id";
            }

            $this->sql = $sql;
            return $this->FirstQuery();

        }catch(Exception $e){
            $this->SetError($e);
        }
        return $result;
    }

}

?>