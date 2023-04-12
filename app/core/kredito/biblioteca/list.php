<?php
    /**
     * Obtiene un registro dado un ID númerico
     * Created: [date]
     * Author: api_creator
     */

    require_once(DIR_model ."biblioteca".DS."class.biblioteca.inc.php");
    require_once(DIR_model ."personas".DS."class.personas.inc.php");
    
    $this_file = substr(__FILE__,strlen(DIR_BASE))." ";
    
    $biblioteca = new cBiblioteca();
    
    if($persona_id = SecureInt($ws->GetParam("persona_id"))){
        $personas = new cPersonas;
        
        if(!$personas->Get($persona_id)){
            cLogging::Write($this_file." ".$ws->transId." -> ".__LINE__." La persona indicada no existe  : ");
            return $ws->SendResponse(404,null,160);
        }

        $route = "";

        $route = $ws->GetParam("route");
        
        if(!empty($route)){
            $route = str_replace("\\",DS,$route);
        }
        
        if(!$biblioteca->GetByPersona($persona_id)){
            cLogging::Write($this_file." ".$ws->transId." -> ".__LINE__." La biblioteca no se encontro , ni se puedo crear correctamente: ".$persona_id);
			return $ws->SendResponse(404,null,191);
        }
        
        if(!$listado = $biblioteca->GetCarpetasPersona($persona_id,$route)){
            cLogging::Write($this_file." ".$ws->transId." -> ".__LINE__."La biblioteca no se encontro , ni se puedo crear correctamente para la persona: ".$persona_id);
            return $ws->SendResponse(404,null,191);
        }
    }else{
        if(!$listado = $biblioteca->GetAllBiblioteca()){
			cLogging::Write($this_file." ".$ws->transId." -> ".__LINE__." Biblioteca no encontrada para la persona: ".$persona_id);
            return  $ws->SendResponse(404,null,190);
        }
    }

    
    $ws->SendResponse(200,$listado);
    
?>