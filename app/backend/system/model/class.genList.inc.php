<?php
/*
    Arma el listado para cualquiera que lo necesite pero desde el core
    Author: Gonza
    Create: 2021-10-26
*/
require_once(DIR_model."class.fundation.inc.php");
require_once(DIR_includes."class.listhelper.inc.php");

class cGenList extends cModels{

    public $select = array();
    public $where;
    public $rename = array();
    public $orderBy;
    
    public $estado;
    public $pag;
    public $limit;
    public $sql;
    public $order;
    
    // Datos del LISTHELPER
    public $ItemsPorPagina = 25;
    public $PaginaActual = 1;
    public $ReplaceTag = array('<b class="red">', '</b>');
    public $Rango = 3;
    public $CantExtremos = 2;
    public $OrdenwithTitle = true;

    public $field_order = array();
    
    public $table_main;
    public $hlp;

    /**
     * Summary.
     * @param string Se pasa el nombre de la funcion que arma el listado, ya que el metodo trabaja con la generacion de listados armando la consulta.
     */
    function __construct(string $ListadoMgr)
    {
        parent::__construct();
        $this->estado = "HAB";
        $this->limit = 25;
        $this->pag = 1;
        $this->order = array();
        $this->orderBy = '';
        $this->expre = "/^,$/i";

        $this->hlp = new ListHelper();

        $this->hlp->ListadoMgr = $ListadoMgr;
        $this->hlp->ordenClass = 'col-order';
        $this->hlp->ItemsPorPagina = $this->ItemsPorPagina;
        $this->hlp->PaginaActual = $this->PaginaActual;
        $this->hlp->ReplaceTag =  $this->ReplaceTag;
        $this->hlp->Rango = $this->Rango;
        $this->hlp->CantExtremos = $this->CantExtremos;
        $this->hlp->OrdenwithTitle = $this->OrdenwithTitle;
    }

    /**
     * Summary. Devulve un listado 
     * @return obj $result
    */
    public function List(){
        $result = array();
        try{
            $this->SqlSelect();
            $this->SqlWhere();
            $this->Query($this->sql,true);
            if ($fila = $this->First()){
                do{
                    $result[]= $fila;
                }while ($fila = $this->Next());

                $this->hlp->ItemsTotales = $this->cantidad;

                $result = $result;
            }

        }catch(Exception $e){
            $this->SetError($e);
        }
        return $result;
    }

    /**
     * 
     * 
    */
    private function SqlSelect(){
        if (CanUseArray($this->select)){

            $this->sql= "SELECT ";

            foreach ($this->select as $key => $value) {
                if (CanUseArray($value)){
                    foreach ($value as $campo) {
                        if ($this->rename){
                            $this->sql .= "`TBL_".$key."`.`".$campo."` AS `TBL_".$key."_".$campo."`,";
                        }else{
                            $this->sql .= "`TBL_".$key."`.`".$campo."`,";
                        }
                    }
                }else{
                    $this->sql .= " `".$value."`,";
                }
            }
            $cant = strlen($this->sql);
            $this->sql = mb_substr($this->sql,0,$cant-1);
        }else{
            $this->sql= "SELECT * ";
        }

        $this->SqlFrom();
    }

    /**
     * 
     * 
    */
    private function SqlFrom(){
        if (!CanUseArray($this->table_main)){throw new Exception("Debe de contener el nombre de una tabla minimamente");}

        if (CanUseArray($this->select)){
            if (!CanUseArray($this->rename)){
                foreach ($this->table_main AS $key => $value) {
                    $this->sql = str_replace("TBL_".$key,$value,$this->sql);
                }
            }else{
                foreach ($this->rename AS $key => $value) {
                    $this->sql = str_replace("TBL_".$key,$value,$this->sql);
                }
            }
        }

        $this->sql .= " FROM ";
        
        foreach ($this->table_main AS $key => $value) {
            $this->sql .= SQLQuote($value)." ";
            if ($this->rename){
                $this->sql .= "AS `".$this->rename[$key]."`, ";
            }
        }

        $cant = strlen($this->sql);
        $this->sql = mb_substr($this->sql,0,$cant-2);
    }

    /**
     * 
     * 
    */
    private function SqlWhere(){
        if ($this->where){
            $this->sql .= " WHERE ".$this->where." ";
        }

        $this->Orden();
        $this->LimitPag();
    }

    /**
     * 
     * 
    */
    private function Orden(){
        if ($this->order){
            $this->orderBy = "ORDER BY ";
            foreach($this->order AS $valor)
            {
                foreach ($valor as $key => $value) {
                    $this->orderBy .= $value." ".$key.", ";
                }
            }
        }
        $cant = strlen($this->orderBy);
        $this->orderBy = mb_substr($this->orderBy,0,$cant-2);
        $this->sql .= $this->orderBy;
    }

    /**
     * 
     * 
    */
    private function LimitPag(){
        if ($this->limit == 'all'){
            $this->sql .="";
        }
        else
        {
            $form = ($this->limit*$this->pag)-$this->limit;
            $to = $this->limit*$this->pag;
            
            $this->sql .= " LIMIT ".$form.",".$to;
        }
    }
}

?>