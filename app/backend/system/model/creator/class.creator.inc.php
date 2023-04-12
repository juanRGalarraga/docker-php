<?php

/**
 * Clase para manejar la creación de API's desde la linea de comando
 * Created: 2021-08-24
 * Author: Gastón Fernandez
 */
define("API_BASE", DIR_BASE . DEVELOPE_NAME . DS);
if (defined("DEVELOPE_NAME")) {
	define("API_folder", DIR_BASE . DEVELOPE_NAME . DS . "api" . DS);
} else {
	define("API_folder", DIR_BASE . "api" . DS);
}
require_once(DIR_wsserver . "class.wsv2Apis.inc.php");
require_once(DIR_includes . "class.fechas.inc.php");
class cCreator
{
	private $logFile = "creator";
	private $folderTemplate = null; //El nombre de la carpeta donde se encuentran los templates

	private $apiTemplate = null; //La ruta completa al archivo a utilizar como original
	private $apiName = null; //El nombre que se le dara a la api una ves creada
	private $apiPath = null; //La ruta completa al archivo JSON de la api creada
	private $apiFolderPath = null; //La ruta a la carpeta con los contenidos de la API ( copiados de folderTemplate )
	private $options = null;//Si la api es personalizada (personas/get) las opciones a utilizar y metodos seran puestos aquí
    private $defaultMethods = "POST,GET,PUT";//Si los metodos nos son indicados en las opciones se utilizaran estos metodos

	private $classPath = null; //Guarda la ruta donde se creo la clase (normalmente es una carpeta y dentro se encuentra el archivo)
	private $classTemplate = null; //El archivo que se utilizara como original de donde copiar la clase
	private $className = null; //El nombre que tendra la clase una ves creada
	private $classFile = null; //El nombre de archivo con el que se nombrara a la clase creada ( no es una ruta )
	private $classFolder = null; //El nombre que recibira la carpeta contenedora de la clase ( no es una ruta)
	public $CanCreateClass = false;
	public $error_msg = null;

	/**
	 *  Summary. Obtiene el template para las APIS
	 *  @return bool $result Indica si el template pudo ser obtenido
	 */
	public function GetApiTemplate($name = "apiTemplate.json")
	{
		$result = false;
		try {
			if (empty($name)) {
				throw new Exception("El nombre del archivo se encuentra vacío");
			}
			$file = DIR_tools . $name; //Donde normalmente se encuentra el template
			if (ExisteArchivo($file)) {
				$apiContent = file_get_contents($file);
				if (empty($apiContent)) {
					throw new Exception("El template de la API esta vacío");
				}
				if (!json_decode($apiContent)) {
					throw new Exception("El template de la API no es un JSON válido: " . GetJsonMsg(json_last_error()));
				}

				$this->apiTemplate = $file;
				$this->folderTemplate = DIR_tools . "templates" . DS . pathinfo($file, PATHINFO_FILENAME);
				$result = true;
			}
		} catch (Exception $e) {
			$this->error_msg = $e->getMessage();
			$this->SetError($e);
		}
		return $result;
	}

	/**
	 * Summary. Se fija si una api ya existe antes de proceder a la creación de la misma
	 * @param string $apiName El nombre de la API que se quiere controlar
	 * @return bool $result True en caso de que exista, false en caso de que no exista
	 */
	public function CheckApiName($apiName)
	{
		$result = false;
		try {
			if (empty($apiName)) {
				throw new Exception("El nombre de la API esta vacío");
			}

			$apis = new cApis(API_folder);
			if (CanUseArray($apis->filelist) and strstr($apiName, "/") === false) {
				foreach ($apis->filelist as $value) {
					$base = basename($value);
					//El nombre del archivo terminaria siendo el mismo? Le digo que este nombre ya existe
					$info = pathinfo($base, PATHINFO_FILENAME);
					if (strtolower($info) == strtolower($apiName)) {
						$result = true;
						break;
					}
				}
			}

			if (!$result and CanUseArray($apis->apis)) {
				$compareAll = (strstr($apiName, "/")); //Con esto decimos que queremos comparar el nombre exacto de la API
				foreach ($apis->apis as $value) {
					if (empty($value->route)) {
						continue;
					}
					$ruta = $value->route;
					if (!$compareAll) {
						$ruta = explode("/", $ruta);
						$ruta = $ruta[0] ?? $ruta;
					}
					if (strtolower($ruta) == strtolower($apiName)) {
						$result = true;
						break;
					}
				}
			}

			if (!$result) {
				$this->apiName = $apiName;
			}
		} catch (Exception $e) {
			$this->error_msg = $e->getMessage();
			$this->SetError($e);
		}
		return $result;
	}

	/**
	 * Summary. Copia el template de la API y reemplaza las variables de la api por la que se creara
	 * @return bool $result Indica si el proceso se pudo efectuar o no.
	 */
	public function CreateApi()
	{
		$result = false;
		try {
			if (empty($this->apiTemplate)) {
				throw new Exception("El template de la API no fue encontrado");
			}
			if (empty($this->apiName)) {
				throw new Exception("Para utilizar este metodo debes utilizar ->CheckApiName antes");
			}


			if (!$file = fopen($this->apiTemplate, "r")) {
				throw new Exception("No se pudo leer el template de la API");
			}

			$target = API_folder . $this->apiName . ".json";
			$newFile = fopen($target, "w+");
			if (!$newFile) {
				unlink($target);
				throw new Exception("No se pudo abrir el archivo copia '" . $target . "'. El archivo fue eliminado.");
			}

			if (($line = fgets($file)) !== false) {
				do {
					if (stripos($line, "[apiName]") !== false) {
						$line = str_replace("[apiName]", $this->apiName, $line);
					}
					if (stripos($line, "[folderName]") !== false) {
						$line = str_replace("[folderName]", $this->apiName, $line);
					}
					fwrite($newFile, $line);
				} while (($line = fgets($file)) !== false);
			}
			fclose($newFile);
			fclose($file);
			$result = true;
			$this->apiPath = $target;
			// unlink($target);
		} catch (Exception $e) {
			$this->error_msg = $e->getMessage();
			$this->SetError($e);
		}
		return $result;
	}

	/**
	 * Summary. Lo que hace es insertar una API, quiere decir que el nombre de la API ya existe, pero el nombre completo de la API no ( 'personas' existe pero 'personas/{id}?:int' no)
	 * @return bool $result Indica si todo pudo ser creado con exito
	 */
	public function InsertApi()
	{
		$result = false;
		try {
			if(empty($this->apiName)){ throw new Exception("Para usar este metodo debes utilizar ->CheckApiName antes"); }

			//Lo primero a hacer es comprobar si existe ya el archivo para esta API
			$tmpName = $this->apiName;
			if(($tmpName = strstr($this->apiName,"/",true)) == false){
				$tmpName = $this->apiName;
			}

            $separado = $this->options['separado'] ?? false;//Indica si debo crear una api por cada metodo indicado
            $methods = (!isset($this->options['methods']) or empty($this->options['methods']))? $this->defaultMethods:$this->options['methods'];

            $dataToSave = array();
            $newData = array(
				'methods' => $methods,
				'route' => $this->apiName,
				'resolver' => (!isset($this->options['resolver']) or empty($this->options['resolver']))? $tmpName:$this->options['resolver'],
				'restricted' => true
			);
            if($separado){
                $methods = explode(",",$methods);
                foreach($methods as $value){
                    $newData['methods'] = $value;
                    $dataToSave[] = $newData;
                }
            }else{
                $dataToSave[] = $newData;
            }

			$folder = API_folder;
			foreach(glob($folder."*") as $value){
				if(is_dir($value)){ continue; }
				$file = pathinfo($value,PATHINFO_FILENAME);
				if(strtolower($file) == strtolower($tmpName)){
					//Archivo encontrado, por lo que solo debo traerme el json e insertar
					if(!$data = file_get_contents($value)){ throw new Exception("No se pudo obtener el contenido del archivo '".$file."' del directorio de API's");}
					if(!$data = json_decode($data)){ throw new Exception("El archivo contenedor de la API no es un JSON válido: " . GetJsonMsg(json_last_error())); }
                    
                    foreach($dataToSave as $valor){
                        $data[] = $valor;
                    }
					if(!file_put_contents($value,json_encode($data,JSON_HACELO_BONITO_CON_ARRAY|JSON_UNESCAPED_SLASHES))){ throw new Exception("No se pudo guardar el contenido del archivo '".$file."' del directorio de API's"); }
					$result = true;
					break;
				}
			}

			//Si llegamos aca y no esta result en true, quiere decir que debemos crear el json
			if(!$result){
				if(!file_put_contents($folder.$tmpName.".json",json_encode($dataToSave,JSON_HACELO_BONITO_CON_ARRAY|JSON_UNESCAPED_SLASHES))){ throw new Exception("No se pudo guardar el contenido del archivo '".$tmpName."' del directorio de API's"); }
			}

			$result = true;
		} catch (Exception $e) {
			$this->error_msg = $e->getMessage();
			$this->SetError($e);
		}
		return $result;
	}

	/**
	 * Summary. Establece las opciones a utilizar al momento de insertar la API personalizada (las que no usan templates)
	 * @param array $options Las opciones a utilizar
	 * @return bool $result 
	 */
	public function setCustomOptions($options){
		$result = false;
		$supportedOptions = array('methods','resolver','separado');
		try {
			if(!CanUseArray($options)){ throw new Exception("Debes indicar un array con las opciones a colocar en la API a crear"); }

			$reg = array();
			foreach($options as $key => $value){
				if(in_array(strtolower($key),$supportedOptions)){
					$reg[$key] = $value;
				}
			}

			if(CanUseArray($reg)){
				$this->options = $reg;
			}
			$result = true;
		} catch (Exception $e) {
			$this->error_msg = $e->getMessage();
			$this->SetError($e);
		}
		return $result;
	}

	/**
	 * Summary. Crea la carpeta que sera utilizada por la API
	 * @return bool $result Indica si todo pudo ser creado con exito
	 */
	public function CreateFolder()
	{
		$result = false;
		try {
			if (empty($this->apiName)) {
				throw new Exception("Para utilizar este metodo debes utilizar ->CheckApiName antes");
			}
			if (empty($this->folderTemplate)) {
				throw new Exception("Para utilizar este metodo debes utilizar ->CheckApiName antes");
			}
			if (!ExisteCarpeta($this->folderTemplate)) {
				throw new Exception("La carpeta de templates de la API no existe");
			}

			$targetDir = API_BASE . $this->apiName;
			if (ExisteCarpeta($targetDir)) {
				throw new Exception("Ya existe una carpeta con este nombre");
			}
			$this->apiFolderPath = $targetDir;
			if (!mkdir($targetDir)) {
				throw new Exception("No se pudo crear la Carpeta para la API");
			}
			if (!$result = CopyFolderTo($this->folderTemplate, $targetDir)) {
				throw new Exception("No se pudo copiar el contenido de la carpeta template a la nueva carpeta, La carpeta nueva sera eliminada.");
			}
		} catch (Exception $e) {
			$this->error_msg = $e->getMessage();
			$this->SetError($e);
			$this->revertAll();
		}
		return $result;
	}

	public function GetClassTemplate($name = "classTemplate.inc.php")
	{
		$result = false;
		try {
			if (empty($name)) {
				throw new Exception("El nombre del archivo se encuentra vacío");
			}
			$file = DIR_tools . $name; //Donde normalmente se encuentra el template
			if (ExisteArchivo($file)) {
				$apiContent = file_get_contents($file);
				if (empty($apiContent)) {
					throw new Exception("El template de la Clase esta vacío");
				}

				$this->classTemplate = $file;
				$result = true;
			}
		} catch (Exception $e) {
			$this->error_msg = $e->getMessage();
			$this->SetError($e);
		}
		return $result;
	}

	/**
	 * Summary. Coloca los parametros que se utilizaran al momento de crear la clase
	 * @param array $info Array con el nombre y el nombre del archivo de la clase a crear
	 */
	public function SetClassInfo($info)
	{
		$result = false;
		try {
			if (empty($this->classTemplate)) {
				throw new Exception("El Template de la clase se debe obtener con el metodo ->GetClassTemplate");
			}
			if (!CanUseArray($info)) {
				throw new Exception("La INFO a utilizar para la creación de la clase esta vacía");
			}

			$className = $info['className'] ?? null;
			$classFile = $info['classFile'] ?? null;
			$classFolder = $info['classFolder'] ?? null;

			if (empty($className)) {
				throw new Exception("El nombre a utilizar de la clase esta vacío");
			}
			if (empty($classFile)) {
				$classFile = "class." . $className . ".inc.php";
			}
			if (empty($classFolder)) {
				$classFolder = strtolower($className);
			}

			$this->className = $className;
			$this->classFile = $classFile;
			$this->classFolder = $classFolder;
			$this->CanCreateClass = true;
			$result = true;
		} catch (Exception $e) {
			$this->error_msg = $e->getMessage();
			$this->SetError($e);
		}
		return $result;
	}

	/**
	 * Summary. Crea la clase a utilizar para la API
	 */
	public function CreateClass()
	{
		$result = false;
		try {
			if (!$this->CanCreateClass) {
				throw new Exception("Para utilizar este metodo debes usar ->SetClassInfo antes");
			}
			if (empty($this->className)) {
				throw new Exception("Para utilizar este metodo debes usar ->SetClassInfo antes");
			}
			if (empty($this->classFile)) {
				throw new Exception("Para utilizar este metodo debes usar ->SetClassInfo antes");
			}
			if (empty($this->classFolder)) {
				throw new Exception("Para utilizar este metodo debes usar ->SetClassInfo antes");
			}

			$targetFolder = DIR_model . $this->classFolder;
			$this->classPath = $targetFolder;
			if (ExisteCarpeta($targetFolder)) {
				throw new Exception("Ya existe una carpeta con el nombre '" . $this->classFolder . "' en el directorio models");
			}
			if (!mkdir($targetFolder)) {
				throw new Exception("No se pudo crear la carpeta '" . $this->classFolder . "' en el directorio models");
			}

			if (!$file = fopen($this->classTemplate, "r")) {
				throw new Exception("No se pudo leer el template de la Clase");
			}

			$target = $targetFolder . DS . $this->classFile;
			$newFile = fopen($target, "w+");
			if (!$newFile) {
				throw new Exception("No se pudo abrir el archivo destino para la Clase, todos los cambios seran revertidos");
			}

			if (($line = fgets($file)) !== false) {
				do {
					if (stripos($line, "[className]") !== false) {
						$line = str_replace("[className]", $this->className, $line);
					}
					if (stripos($line, "[classFile]") !== false) {
						$line = str_replace("[classFile]", $this->classFile, $line);
					}
					fwrite($newFile, $line);
				} while (($line = fgets($file)) !== false);
			}
			fclose($file);
			fclose($newFile);
			if (!$result = $this->SetApisName()) {
				throw new Exception("No se pudo actualizar las referencias a la clase en los archivos creados. Los cambios seran revertidos");
			}
		} catch (Exception $e) {
			$this->error_msg = $e->getMessage();
			$this->SetError($e);
			$this->revertAll();
		}
		return $result;
	}

	/**
	 * Summary. Revierte todos los cambios realizados por esta clase, eliminando todas las carpetas y archivos creados
	 */
	public function revertAll()
	{
		if (!empty($this->apiPath)) {
			unlink($this->apiPath);
			$this->apiPath = null;
		}

		if (!empty($this->apiFolderPath)) {
			DeleteFolder($this->apiFolderPath);
			$this->apiFolderPath = null;
		}

		if (!empty($this->classPath)) {
			DeleteFolder($this->classPath);
			$this->classPath = null;
		}
	}

	/**
	 * Summary. Recorre todos los archivos de la carpeta API template creada y reemplaza los campos por los nombres de la clase creada ( si es que hubo una clase creada )
	 * @return bool $result true en caso de que todo salga bien, false en caso de que falle algo
	 */
	private function SetApisName()
	{
		$result = false;
		try {
			if (empty($this->className)) {
				throw new Exception("Para utilizar este metodo debes usar ->SetClassInfo antes");
			}
			if (empty($this->classFile)) {
				throw new Exception("Para utilizar este metodo debes usar ->SetClassInfo antes");
			}
			if (empty($this->classFolder)) {
				throw new Exception("Para utilizar este metodo debes usar ->SetClassInfo antes");
			}
			if (empty($this->apiName)) {
				throw new Exception("Para utilizar este metodo debes utilizar ->CheckApiName antes");
			}
			if (empty($this->apiFolderPath)) {
				throw new Exception("Para utilizar este metodo debes utilizar ->CheckApiName antes");
			}
			if (!ExisteCarpeta($this->apiFolderPath)) {
				throw new Exception("La carpeta de templates de la API no existe");
			}

			$targetDir = $this->apiFolderPath;
			if (!$files = $this->GetAllFiles($targetDir)) {
				throw new Exception("La carpeta de la API " . $this->apiName . " no contiene archivos");
			}

			foreach ($files as $value) {
				$tmp = file_get_contents($value);
				$tmp = str_replace("[className]", $this->className, $tmp);
				$tmp = str_replace("[classFile]", $this->classFile, $tmp);
				$tmp = str_replace("[classFolder]", $this->classFolder, $tmp);
				$tmp = str_replace("[date]", cFechas::Ahora(), $tmp);
				$tmp = file_put_contents($value, $tmp);
			}
			$result = true;
		} catch (Exception $e) {
			$this->error_msg = $e->getMessage();
			$this->SetError($e);
		}
		return $result;
	}

	/**
	 * Summary. Obtiene una lista con todos los archivos en una carpeta, incluyendo los de sus subdirectorios
	 * @param string $targetDir La carpeta destino en donde se buscaran los archivos
	 */
	private function GetAllFiles($targetDir)
	{
		$result = false;
		try {
			if (empty($targetDir)) {
				throw new Exception("No se indico el directorio donde se realizara el listado");
			}
			if (!ExisteCarpeta($targetDir)) {
				throw new Exception("El directorio '" . $targetDir, "' no fue encontrado");
			}
			$targetDir = EnsureTailingSlash($targetDir);

			$reg = array();
			foreach (glob($targetDir . "*") as $value) {
				if (is_dir($value)) {
					$tmp = $this->GetAllFiles($value);
					if (CanUseArray($tmp)) {
						$reg = array_merge($reg, $tmp);
					}
					continue;
				}
				$reg[] = $value;
			}

			if (CanUseArray($reg)) {
				$result = $reg;
			}
		} catch (Exception $e) {
			$this->error_msg = $e->getMessage();
			$this->SetError($e);
		}
		return $result;
	}

	private function SetError($e)
	{
		cLogging::SetPostfix($this->logFile);
		$trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
		$f = array_pop($trace);
		$line = sprintf('%s:%s %s%s', cLogging::TrimBaseFile($f['file']), $f['line'], ((isset($f['class'])) ? $f['class'] . '->' : null), $f['function']) . ' ' . $e->GetMessage() . PHP_EOL;
		for ($i = count($trace) - 1; $i >= 0; $i--) {
			$f = $trace[$i];
			$line .= sprintf("\t%s:%s %s%s", cLogging::TrimBaseFile($f['file']), $f['line'], ((isset($f['class'])) ? $f['class'] . '->' : null), $f['function']) . PHP_EOL;
		}
		if (DEVELOPE) {
			EchoLog($line);
		}
		cLogging::Write(trim($line));
	}
}
