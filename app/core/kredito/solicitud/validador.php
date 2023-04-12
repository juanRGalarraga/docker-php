<?php
/**
 * Con esto se pueden válidar los datos antes de colocarlos en una solicitud
 * Created: 2021-09-07
 * Auhtor: Gastón Fernandez
*/
require_once(DIR_includes."class.passwords.inc.php");

/**
 * Summary. Valida los datos para la solicitud
 * @param array-object $data Los datos que seran validados
 * @return bool.
 */
function ValidateData($data){
    global $ws;
    //Aca lo que hacemos es buscar el archivo que me indica que campos debe haber en el archivo y que válidaciones debo realizar sobre cada campo
    $archivo = DIR_config."solicitud_min_data.json";
    if(!ExisteArchivo($archivo)){ 
        cLogging::Write(__FILE__ . " " . __LINE__ . " El archivo con la configuración para crear la solicitud no fue encontrado");
        $ws->SendResponse(500, null, 140); return false;
    }
    if(!$fileData = file_get_contents($archivo)){
        cLogging::Write(__FILE__ . " " . __LINE__ . " El archivo de configuración no pudo ser leído");
        $ws->SendResponse(500, null, 140); return false;
    }
    if(!$fileData = json_decode($fileData,true)){
        cLogging::Write(__FILE__ . " " . __LINE__ . "El contenido del archivo de configuración no es un json válido: ".GetJsonMsg(json_last_error()));
        $ws->SendResponse(500, null, 140); return false;
    }
    
    $dataError = array();
    if(!CanUseArray($fileData)){ 
        cLogging::Write(__FILE__ . " " . __LINE__ . "El archivo leído no contiene nada para válidar");
        $ws->SendResponse(500, null, 140); return false;
    }
    
    foreach($fileData as $key => $value){
        $type = strtolower($value['type'] ?? "string");//Por omisión el valor es string y no debe pasar ninguna validación extra
        $required = boolval($value['required'] ?? false);//¿Es obligatorio?
        $min = $value['min'] ?? 0;//Obtengo el mínimo valor que puede tener el dato (en string sería el largo)
        $max = $value['max'] ?? -1;//Obtengo el máximo valor que puede tener el dato (en string sería el largo)
        $lenMin = $value['lenMin'] ?? 0;//Obtengo el mínimo valor que puede tener el dato (en string sería el largo)
        $lenMax = $value['lenMax'] ?? -1;//Obtengo el máximo valor que puede tener el dato (en string sería el largo)
        $msg = $value['msg'] ?? null;//Obtenemos el mensaje que debemos dar si el dato no esta o no es válido
        $type_msg = $value['type_msg'] ?? null;//Obtenemos el mensaje a dar si el tipo no coincide
        $valor = (is_object($data))? $data->$key ?? null:$data[$key] ?? null;//Obtenemos el valor a comprobar
    
        if(empty($valor) AND $valor !== 0 AND $valor !== false){
            if($required){
                $msg = (empty($msg))? "El ".$key." es un valor obligatorio para crear la solicitud":$msg;
                $dataError[$key] = $msg;
            }
            continue;
        }
    
        $type_msg = (empty($type_msg))? $key.": no es del tipo ".$type:$type_msg;
        //Ahora realizo la comprobación de tipo
        switch($type){
            case 'string':
                    if(!is_string($valor)){ $dataError[$key] = $type_msg; continue 2; }
                    if($lenMin > 0 AND strlen($valor) < $lenMin){ $dataError[$key] = $key.": debe tener una longitud mínima de ".$lenMin." carácteres."; continue 2; }
                    if($lenMax > 0 AND strlen($valor) > $lenMax){ $dataError[$key] = $key.": puede tener una longitud máxima de ".$lenMax." carácteres."; continue 2; }
                break;
            case 'int':
                    if(!CheckInt($valor)){ $dataError[$key] = $type_msg; continue 2; }
                    if($min >= 0 AND $valor < $min){ $dataError[$key] = $key.": debe tener un valor mínimo de ".$min."."; continue 2; }
                    if($max > 0 AND $valor > $max){ $dataError[$key] = $key.": puede tener un valor máximo de ".$max."."; continue 2; }
                break;
            case 'float':
                    if(!CheckFloat($valor)){ $dataError[$key] = $type_msg; continue 2; }
                    if($min >= 0 AND $valor < $min){ $dataError[$key] = $key.": debe tener un valor mínimo de ".$min."."; continue 2; }
                    if($max > 0 AND $valor > $max){ $dataError[$key] = $key.": puede tener un valor máximo de ".$max."."; continue 2; }
                break;
            default:
                    $dataError[$key] = "Tipo de dato no contemplado"; continue 2;
                break;
        }
    
        //Si llegamos aca lo que haremos es ver si este dato require alguna validación especial
        $validateFunc = $value['validate'] ?? null;
        if(empty($validateFunc)){ continue; }
        $tmp = explode("::",$validateFunc);//Con esto sabremos si la funcion tiene una clase o no
		// $func contendrá el nombre de la función o el método a usar para validar el dato.
        $func = $tmp[1] ?? $tmp[0];
        $class = (isset($tmp[0]) AND isset($tmp[1]))? $tmp[0]:null;
    
        //No tengo nombre de función para ejecutar, salgo
        if(empty($func)){ continue; }
    
        //Es una clase estatica?? Compruebo que la clase exista
        if(!empty($class)){
            if(!class_exists($class)){ 
                cLogging::Write(__FILE__." ".__LINE__." La clase para realizar la validación del ".$key." no fue encontrada");
                $dataError[$key] = "No se pudo válidar el dato"; continue; 
            }
    
            if(!method_exists($class,$func)){
                cLogging::Write(__FILE__." ".__LINE__." El metodo para validar el dato: ".$key." no fue encontrado dentro de la clase ".$class);
                $dataError[$key] = "No se pudo válidar el dato"; continue; 
            }
    
            $func = $class."::".$func;
        }else{//De no ser una clase busco la función en si
            if(!function_exists($validateFunc)){ $dataError[$key] = "No se pudo válidar el dato"; continue; }
        }
    
        //Ahora realizo el llamado a la función
        if(!$func($valor)){
            $dataError[$key] = "El ".$key." no es válido";
        }
    } // foreach
    
    if(CanUseArray($dataError)){
        cLogging::Write(__FILE__ . " " . __LINE__ . " Los datos aportados a la solicitud no cumplen los requisitos ".print_r($dataError,true));
        $data = (CanUseArray($dataError))? $dataError:null;
        $ws->SendResponse(400, $data, 148);
        return false;
    }
    return true;
}