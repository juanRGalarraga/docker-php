<?php 
/*
    Manejo de usuario
    Author: Gonza
    Create: 2021-03-06
*/
require_once(DIR_includes."common.inc.php");
require_once(DIR_model."class.wsv1Client.inc.php");
$this_file = substr(__FILE__,strlen(DIR_BASE))." ";

class uBkusuarios extends cWSV1Client{
    

    function __construct()
    {
        parent::__construct();
    }

    function loginUserBackas($user,$pass){
        $result = false;
        global $this_file;

        try{
            if (empty($user)){throw new Exception($this_file." El nombre de usuario esta vacío.");}
            if (empty($pass)){throw new Exception($this_file." El password esta vacío.");}
            if (!$this->PostQuery("tienda/login",["username"=>$user,"password"=>$pass])){
                throw new Exception($this_file." El nombre de Username o el Password son incorrectos.");
            }
            $result = $this->data;
        }catch(Exception $e){
            cLogging::Write(__METHOD__,$e->GetMessage());
        }

        return $result;
    }
}
?>