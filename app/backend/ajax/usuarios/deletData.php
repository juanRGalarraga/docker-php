<?php
    require_once(DIR_model."personas".DS."class.personasData.inc.php");
    $this_file = substr(__FILE__, strlen(DIR_BASE)) . " ";
    $post = CleanArray($_POST);

    if (isset($post['calle']) AND isset($post['altura']) AND isset($post['piso'])){
        $calle = $post['calle'];
        $altura = $post['altura'];
        $piso = $post['piso'];

        $valor = array($calle,$altura,$piso);
        $indice = array("calle","altura","departamento");

        $action = $post['action'];

        if (secureInt($action)){
            $datapeople = new cPersonasData($action);
                    
            if (!$datapeople->DeletOneElement($valor,$indice)){
                cLogging::Write($this_file.", No fue posible eliminar el elemento.");
            }
        }

    }else if (isset($post['eli'])){
        $valor = @$post["eli"];
    
        if (empty($valor)){
            cLogging::Write($this_file.", No se puede eliminar un elemtno vacío.");
            return;
        }
    
        $action = $post['action'];
        if (secureInt($action)){
    
            $element = $post['elem'];
            $indice = array();
    
            preg_match_all('/Email|Phone/i',$element,$indice);
            switch ($indice[0][0]) {
                case 'Email':
                        $indice ="EMAIL";
                    break;
                case 'Phone':
                        $indice ="TEL";
                    break;
            }

            if (!is_array($indice)){   
                $datapeople = new cPersonasData($action);
                
                if (!$datapeople->DeletOneElement($valor,$indice)){
                    cLogging::Write($this_file.", No fue posible eliminar el elemento.");
                }
                
            }
        }
    }

    ResponseOk();
?>