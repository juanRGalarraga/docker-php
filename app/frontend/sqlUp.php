<?php 
/* 
    Herramienta para la ejecucion de consultas SQL desde un directorio.
	Author: Gonza 
    Created: 2021-02-12
*/

// Sin el initialize no funciona el frame
require_once('initialize.php');

require_once(DIR_config.'config.inc.php');
require_once(DIR_includes."common.inc.php"); // Contiene las funciones comunes.
require_once(DIR_includes."class.fechas.inc.php"); // Funciones de tratamiento de fechas y horas.
require_once(DIR_includes.'class.logging.inc.php'); // Para escribir entradas en el log de eventos.
require_once(DIR_model.'class.dbutili.2.inc.php'); // Clase base para el manejo de la base de datos.

$this_file = substr(__FILE__,strlen(DIR_BASE))." ";

// intanciamos y conectamos con la base de datos
$objeto_db = new cDb();
$objeto_db->Connect(DBHOST, DBNAME, DBUSER, DBPASS, DBPORT);
$continue = false;

// Ante un error cortamos
if ($objeto_db->error) {
    cLogging::Write($this_file . " DBErr: " . $objeto_db->errmsg);
    return;
}

// Tipos de acciones validas
$actions = array('-all','-id','-sql');

// Controlo que sea alguna opcion valida y si es necesario controlo que el segundo parametro
switch (strtolower(@$argv[1])) {
    case '-all':
        $continue = true;
    break;
    case '-id':
        if (!isset($argv[2]) OR !is_numeric($argv[2])) {
            echo('Debe de indicar el registro.');
            exit;
        }
        $continue = true;
    break;
    case '-sql':
        if (!isset($argv[2]) OR !is_string($argv[2])) {
            echo('Debe indicar el nombre.');
            exit;
        }
        $continue = true;
    break;
    default:
        echo('Falta indicar accion.');
        exit;
    break;
}

// Si algo salio mal no pasamos.
if (!$continue){
    exit;
}

// Control de que la variable este definida
if (!defined("SQLUP") or empty(SQLUP)){
    EchoLog('La variable se encuentra vacia.');
    cLogging::Write(__LINE__.' '.$this_file.' La variable se encuentra vacia.');
    return;
}

// Paso el nombre a una unica funcion
$base = EnsureTrailingSlash(DIR_sql.SQLUP);

// Se controla que exista el directorio dentro de BASE
if (!ExisteCarpeta($base)){
    EchoLog('No se encontro el directorio indicado denteo de '.DIR_sql.'.');
    cLogging::Write(__LINE__.' '.$this_file.' No existe la carpeta que almacena los SQL.');
    return;
}

// Depende d ela accion es la funcion que se ejecuta
switch (@$argv[1]) {
    case '-all':
        all($objeto_db,$base);
    break;
    case '-id':
        id($objeto_db,$base,$argv[2]);
    break;
    case '-sql':
        sql($objeto_db,$base,$argv[2]);
    break;
}

/**
 * Summary. Funcion que recorre todo el direcctorio y ejecuta los sql sin ejecutar
 * @param objet $objeto_db es la coneccion a la base de datos
 * @param string $base es el nombre del directorio fisico
 */
function all($objeto_db,$base){
    try{
        // Abrimos la carpeta para obtener todos los sql que ahi
        $dir_open = scandir($base);
        $dir_open = array_diff($dir_open,['.','..']);
        
        // las variables simples
        $arraybdd= array();
        $theQuery = '';

        // Busco el ultimo numero de ronde ademas traer un array de los nombre de la tabla
        $arraybdd = SqlListos();
        $rondabdd = SqlRondas();

        // Recorro todo los directorios
        foreach ($dir_open as $sql) {

            // desarmo el nombre
            $sqlName = ExtraerNombre($sql);
            $ext = ExtraerExtension($sqlName);

            // Controlo que sea un archivo calido y que no este vacío.
            if (filesize($base.DS.$sqlName) < 1 OR $ext != 'sql'){
                cLogging::Write(__LINE__.' El archivo esta vacio o no es un SQL');
                continue;
            }
            
            // Ssaco el nombre unicamente
            $Name = explode('.',$sqlName);

            // Si el nombre es igual a alguno en la base de datos este no se ejecuta
            if (in_array($Name[0],$arraybdd)){
                cLogging::Write(__LINE__.'Este SQL ya fue ejecutado: '.$Name[0].'.');
                EchoLog('Este SQL ya fue ejecutado: '.$Name[0].'.');
                continue;
            }

            // Llamo a la funcion que se encarga de leer el archivo y ejecutar el SQL
            if (whileFun($objeto_db,$base,$sqlName,$theQuery)){
                $data = array (
                    'nombre'=>	$Name[0],
                    'ronda'=> $rondabdd
                );

                // Si se ejecuto la consulta guardo el registro en la base de datos
                GuardarLog($data);
            }
        }

    }catch(Exception $e){
        cLogging::Write($e->GetMessage());
    }
}

/**
 * Summary. Funcion para crear el registro en la tabla de log
 * @param arry $data el nombre fisico del archivo. 
 * @param objet $objeto_db es el objeto db.
 * @return bool $result true si sale todo bien.
 */
function GuardarLog($reg){
	$result = false;
	$objeto_db = new cDb();
    $objeto_db->Connect(DBHOST, DBNAME, DBUSER, DBPASS, DBPORT);
	try{
		$reg['sys_fecha_alta']= cFechas::Ahora();
		
		$reg = $objeto_db->RealEscapeArray($reg);
		$objeto_db->Insert(TBL_sqlupdate,$reg);
		if ($objeto_db->error) { throw new Exception('DBErr: '.$objeto_db->errmsg); }
		$result = true;
	}catch(Exception $e){
        cLogging::Write($e->GetMessage());
	} finally {
		$objeto_db->Disconnect();
	}
	return $result;
}

/**
 * Summary. Busca un registro segun su id en la base de datos y si puede lo ejecuta.
 * @param objet $objeto_db Coneccion a la base de datos
 * @param string $base Nombre fisico del diectorio
 * @param interger $id numero de identificacion
 */
function id($objeto_db,$base,$id){
    $theQuery = '';
    $reg = array();
    try{
        // Busco si existe el registro
		$sql= "SELECT * FROM ".SQLQuote(TBL_sqlupdate)."  WHERE id =".$id;
        $objeto_db->Query($sql,true);

        // Controlo si encontre algo
        if ($objeto_db->cantidad > 0){
            if ($fila = $objeto_db->First()){            
                $result = $fila['nombre'];
            }
            // Controlo si existe el archivo
            if (!ExisteArchivo($base.DS.$result.'.sql')){
                cLogging::Write(__LINE__.' No existe el archivo con el nombre: '.$result);
                EchoLog('No se encontro el registro.');
                return;
            }

            // Trato de ejecutar el SQL, si se puede actualizo el registro en la base de datos
            if (whileFun($objeto_db,$base,$result.'.sql',$theQuery)){
                $rondabdd = SqlRondas();

                $reg['sys_fecha_alta']= cFechas::Ahora();
                $reg['ronda']= $rondabdd;
                
                $reg = $objeto_db->RealEscapeArray($reg);
                $objeto_db->Update(TBL_sqlupdate,$reg," id =".$id);
            }

        }else{
            // Si no se encuntra nada se indica que no se pudo
            cLogging::Write(__LINE__.' No se encotro ningun registro con el id: '.$id);
            EchoLog('No se encontro el registro.');
            return;
        }
    }catch(Exception $e){
        cLogging::Write($e->GetMessage());
        return;
	}

    cLogging::Write('Se ejecuto la consulta del registro: '.$id);
    EchoLog('Se ejecuto la consulta del registro: '.$id);
}

/**
 * 
 * 
 */
function sql($objeto_db,$base,$nombre){
    $theQuery = '';
    $reg = array();
    try{
        if (!ExisteArchivo($base.DS.$nombre)){
            cLogging::Write(__LINE__.' No existe el archivo con el nombre: '.$nombre);
            EchoLog('No se encontro el registro.');
            return;
        }

        $nombre = explode('.',$nombre);

		$sql= "SELECT * FROM ".SQLQuote(TBL_sqlupdate)."  WHERE `nombre` =".$nombre[0];
        $objeto_db->Query($sql,true);
        if ($objeto_db->cantidad > 0){
            if ($fila = $objeto_db->First()){            
                $nombre= $fila['nombre'];
                $id = $fila['id'];
            }
            
            if (whileFun($objeto_db,$base,$nombre.'.sql',$theQuery)){
                $rondabdd = SqlRondas();

                $reg['sys_fecha_alta']= cFechas::Ahora();
                $reg['ronda']= $rondabdd;
                
                $reg = $objeto_db->RealEscapeArray($reg);
                $objeto_db->Update(TBL_sqlupdate,$reg," id =".$id);
            }
        }
    }catch(Exception $e){
        cLogging::Write($e->GetMessage());
	}
    cLogging::Write('Se ejecuto la consulta con el nombre: '.$nombre);
    EchoLog('Se ejecuto la consulta con el nombre: '.$nombre);
}

/**
 * 
 * 
 */
function whileFun($objeto_db,$base,$sqlName,$theQuery){
    $result = false;
    try{
        $fh = fopen($base.DS.$sqlName, 'r');

        while($theLine = fgets($fh, 1048576)){
            $theLine = trim($theLine);
        
            if(in_array(substr($theLine,0,2),['--','/*']) OR empty($theLine)){
                continue;
            }
        
            $theQuery .= $theLine;
        
            if(substr(trim($theQuery),-1)==';'){
                $objeto_db->Query($theQuery);
                if ($objeto_db->error) { throw new Exception($objeto_db->errmsg); }
                $theQuery = '';
                $result = true;
            }
        } // while

        fclose($fh);
    }catch(Exception $e){
        cLogging::Write($e->GetMessage());
	}
    return $result;
}

/**
 * Symmary. Funcion que trae el listado de nombres
 */
function SqlListos(){
    $result = array();
	$db = new cDb();
    $db->Connect(DBHOST, DBNAME, DBUSER, DBPASS, DBPORT);
	try{
		$sql= "SELECT * FROM ".SQLQuote(TBL_sqlupdate);
        $db->Query($sql);
        if ($fila = $db->First()){
            do {
                $result[] = $fila['nombre'];
            } while ($fila = $db->Next());
        }
	}catch(Exception $e){
        cLogging::Write($e->GetMessage());
	} finally {
		$db->Disconnect();
	}
	return $result;
}

/**
 * Summary. Funcion que trae el ultimo numero de ronda y le suma uno
 */
function SqlRondas(){
    $result = 1;
	$db = new cDb();
    $db->Connect(DBHOST, DBNAME, DBUSER, DBPASS, DBPORT);
	try{
		$sql= "SELECT * FROM ".SQLQuote(TBL_sqlupdate)."  ORDER BY `ronda` DESC LIMIT 1";
        $db->Query($sql);
        if ($fila = $db->First()){            
            $result = $fila['ronda']+1;
        }
	}catch(Exception $e){
        cLogging::Write($e->GetMessage());
	}
	return $result;
}

?>