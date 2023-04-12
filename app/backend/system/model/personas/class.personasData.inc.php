<?php 
    /*
        Gestion de la informacion de personas
        Create: 2021-10-29
        Author: Gonza
    */
    require_once(DIR_model."class.fundation.inc.php");
    require_once(DIR_model."personas".DS."class.personas.inc.php");

class cPersonasData extends cPersonas{

    public $persona;

    public $persona_id;

    /**
     * Summary. El constructor necesita el ID del ususario logeado
     * @param int $id
    */
    public function __construct($id)
    {
        parent::__construct();
        $this->people_id = $id;
        $this->persona_id = $id;
        $this->mainTable = TBL_backend_personas_data;
    }

    /**
     * Summary. Crea los registros de informacion de una persona en la tabla de datos
     * @param array $data Lista de datos a guardar
     * @param string $type diferenciar que tipo de dato es "EMAIL,TEL,DIREC"
     * @return bool $result
     */
    public function CreateData($data,$type){
        $result = false;
        $reg = array();
        try{
            if (!CanUseArray($data)){throw new Exception(__LINE__." El array esta vacío.");}
            if (!in_array($type,['EMAIL','TEL','DIREC'])){throw new Exception(__LINE__." El type no es valido.");}

            foreach ($data as $value) {
                $reg['persona_id'] = $this->persona_id;
                $reg['tipo'] = $type;

                if ($type == 'DIREC'){
                    $reg['dato'] = json_encode(["calle"=>$value['calle'],"altura"=>$value['altura'],"departamento"=>$value['departamento']]);
                }else{
                    $reg['dato'] = json_encode([$type=>$value[$type]]);
                }
                
                $reg['default'] = $value['default'];
                $reg['extras'] = json_encode(["razon"=>"Dirección aún no verificada","valido"=> false,"verificado"=> false]);

                if (!$this->NewRecord($reg)){
                    throw new Exception(__LINE__." No fue posible crear el dato del usuario.");
                }
                
                $result = true;
            }
        }catch(Exception $e){
            $this->SetError($e);
        }

        return $result;
    }

    /**
     * Summary. Busca toda la informacion de la tabla data en base del ID de la persona.
     * @return boolean/array $result
     */
    public function GetAllData(){
        $result = false;
        try {
            $persona = new cPersonas();
            if (!$persona->GetById($this->persona_id)){ throw new Exception(__FILE__." No se pudo recuperar información de la personas");}
            
            $this->sql = "SELECT * FROM ".SQLQuote($this->mainTable)." WHERE `persona_id` = ".$this->persona_id;
            if ($this->FirstQuery()){
                $result = array();
                do{
                    if ($this->actualRecord->tipo == "DIREC"){
                        $result[] = array(
                            "id"=> $this->actualRecord->id,
                            "type"=> $this->actualRecord->tipo,
                            "data"=> $this->actualRecord->dato,
                            "default"=> $this->actualRecord->default
                        );
                    }else{
						if(is_object($this->actualRecord->dato) OR is_array($this->actualRecord->dato)){ 
							reset($this->actualRecord->dato);
						}
                        $result[] = array(
                            "id"=> $this->actualRecord->id,
                            "type"=> $this->actualRecord->tipo,
                            "data"=> $this->actualRecord->dato,
                            "default"=> $this->actualRecord->default
                        );
                    }
                }while($this->Next());
                $result = json_decode(json_encode($result));
            }

        } catch (Exception $e) {
            $this->SetError($e);
        }

        return $result;
    }

    /**
     * Summary. Elimina toda la data de una persona segun su ID
     * @return boolean $result
     */
    public function EliminarAllData(){
        $result = false;
        try{
            if (!SecureInt($this->persona_id)){ throw new Exception(__LINE__." No se pudo recuperar información de la personas.");}
            
            if (!$this->Delete($this->mainTable,"`persona_id`= $this->persona_id")){
                throw new Exception(__LINE__." No fue posible elimiar los registros de usuario.");
            }
            $result = true;
        }catch (Exception $e) {
            $this->SetError($e);
        }
        return $result;
    }

    /**
     * Summary. Elimina el elemento de data de una persona
     * @param string/array $valor dato a buscar
     * @param string/array $indice del JSON
     * @return
     */
    public function DeletOneElement($valor,$indice){
        $result = false;
        try {
            if (empty($valor)){ throw new Exception(__LINE__." El elemtno esta vacío");}

            if (CanUseArray($valor)){
                $count= count($indice);
                for ($i=0; $i < $count; $i++) { 
                    if ($i == 0){
                        $sql =" JSON_EXTRACT(`dato`, '$.".$indice[0]."') LIKE '%".$valor[0]."%'";
                    }else{
                        $sql .=" AND JSON_EXTRACT(`dato`, '$.".$indice[$i]."') LIKE '%".$valor[$i]."%'";
                    }
                } 

            }else{
                if (empty($indice)){ throw new Exception(__LINE__." El elemtno esta vacío");}
                if (!SecureInt($this->persona_id)){ throw new Exception(__LINE__." El valor de la persona esta vacío o no es numerico.");}
                
                $sql =" JSON_EXTRACT(`dato`, '$.".$indice."') LIKE '%".$valor."%' AND `persona_id` = ".$this->persona_id." LIMIT 1";
            }
                
            if (!$this->Delete($this->mainTable,$sql)){
                throw new Exception(__LINE__." No fue posible eliminar a el dato de la persona: $this->persona_id");
            }

            $result = true;

        }catch (Exception $e) {
            $this->SetError($e);
        }
        return $result;
    }
}

?>