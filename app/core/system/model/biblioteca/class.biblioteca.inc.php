<?php
/*
	Clase para manejar la biblioteca de archivos de una persona moral.
	Created: 
	Author: 
*/

require_once(DIR_model."class.fundation.inc.php");
require_once(DIR_includes."class.sidekick.inc.php");

class cBiblioteca extends cModels {
	
	private $tabla_personas = TBL_personas;
	private $tabla_bibliotecas = TBL_biblioteca;
	private $tabla_bibliotecas_archivos = TBL_biblioteca_archivos;
	public $qMainTable = TBL_biblioteca;
	public $base_path = DIR_biblioteca;
	public $usuario_id = null;
	
	function __construct() {
		parent::__construct();
		$this->actual_file = __FILE__;
		$this->mainTable = $this->tabla_bibliotecas;
		$this->qMainTable = SQLQuote($this->mainTable);
		$this->base_path = EnsureTrailingSlash($this->base_path);
	}
	
/**
* Summary. Devuelve los datos de la bibliteca según el id (de la biblioteca) incluyendo los datos de la persona a la cual pertenece.
*/
	public function Get(int $id = null):?object {
		$result = false;
		try{
            // Controlamos que id sea valido.
            if (SecureInt($id,null) == null) { throw new Exception(__LINE__." ID debe ser un número."); }
            // Buscamos los datos por su id.
			$this->sql = "SELECT `biblioteca`.*, `biblioteca`.`nombre` AS `directorio`, CONCAT_WS(' ',`persona`.`nombre`, `persona`.`apellido`) AS `nomape` FROM ".$this->qMainTable." AS `biblioteca`, ".SQLQuote($this->tabla_personas)." AS `persona` WHERE `biblioteca`.`id` = ".$id." AND `persona`.`id` = `biblioteca`.`persona_id` LIMIT 1;";
            return parent::Get();
        }catch(Exception $e){
            // Ante el error salimos por catch.
            $this->SetError(__METHOD__, $e);
        }
		return $result;
	}

	public function CreateFolderPerson($reg){
		$result = false;
		
		try{
            // Controlamos que id sea valido.
			if (!CanUseArray($reg)) { throw new Exception(__LINE__." el array enviado esta vacio."); }
			// $campos = $this->GetColumnsNames($this->tabla_bibliotecas);
            // Showvar($campos);
            // Showvar($reg);
			// foreach ($data as $key => $value) {
            //     if (in_array($key, $campos)) {
            //         $key = $this->RealEscape($key);
            //         $value = $this->RealEscape($value);
            //         if ($key != 'id') { // Just a precaution.
            //             $reg[$key] = $value;
            //         }
            //     }
            // }
			// Showvar($reg,true);
			$nombre_alias = cSideKick::GenerateAlias($reg['nombre']);
			$reg['nombre'] = $nombre_alias;
			$reg['sys_fecha_alta'] = cFechas::Ahora();
            $reg['sys_fecha_modif'] = cFechas::Ahora();
            $reg['sys_usuario_id'] = $this->usuario_id;
			
			if(!$this->NewRecord($reg)){ 
				throw new Exception(" No se pudo crear la biblioteca de la persona indicada ");
			}
			$ruta = $this->base_path;
			cSideKick::EnsureDirExists($ruta);
			$ruta = $this->base_path.DS.$nombre_alias;
			cSideKick::EnsureDirExists($ruta);
			$result = true;
        }catch(Exception $e){
            // Ante el error salimos por catch.
            $this->SetError($e);
			return false;
        }
		return $result;

	}

	/** Summary. Obtener la data de un archivo por su id
	 * @param int $id el id del archivo
	 * @return bool/string	$result con el JSON de la data del archivo o false si falla 
	 */
	public function getDataArchivo($id = null)
	{
		$result = false;
		try {
			if (is_null($id)) {
				throw new Exception(__LINE__ . " No se ha indicado el archivo.");
			}
			$this->sql = "SELECT * FROM " . SQLQuote($this->tabla_bibliotecas_archivos) . " AS `archivos` WHERE `archivos`.`id` = $id AND `archivos`.`estado`='HAB' ORDER BY `sys_fecha_modif`";
			if ($fila = $this->FirstQuery()) {
				$result = $fila;
			}
		} catch (Exception $e) {
			$this->SetError(__METHOD__, $e);
		}
		return $result;
	}


/**
* Summary. Obtener la biblioteca según la persona. Pero si la persona no tiene bibliteca, se crea y listo...
* @param int $persona_id El id de la persona de la cual se busca su biblioteca.
*/
	public function GetByPersona($persona_id) {
		$result = false;
		try{
			if (is_null(SecureInt($persona_id,null))) { throw new Exception(__LINE__." ID debe ser un número."); }
			$this->sql = "SELECT `biblioteca`.`id` FROM ".SQLQuote($this->tabla_bibliotecas)." AS `biblioteca` WHERE `biblioteca`.`persona_id` = ".$persona_id." LIMIT 1;";
			
			if ($fila = $this->FirstQuery($this->sql)) {
                $result = $this->Get($fila->id);
			} else {
				$this->sql = "SELECT CONCAT_WS(' ',`persona`.`nombre`, `persona`.`apellido`) AS `nomape` FROM ".SQLQuote($this->tabla_personas)." AS `persona` WHERE `persona`.`id` = ".$persona_id." LIMIT 1;";
				if ($fila = $this->FirstQuery($this->sql)) {
					$nombre = substr(cSideKick::GenerateAlias($fila->nomape),0,32);
					
					$reg = array();
					$reg['persona_id'] = $persona_id;
					$reg['nombre'] = $nombre;
					$reg['sys_fecha_modif'] = cFechas::Ahora();
					$reg['sys_fecha_alta'] = cFechas::Ahora();
					$reg['sys_usuario_id'] = $this->usuario_id;
					$reg = $this->RealEscapeArray($reg);
					$this->Insert($this->tabla_bibliotecas, $reg);
					$result = $this->Get($this->last_id);
					cSideKick::EnsureDirExists(EnsureTrailingSlash($this->base_path.$nombre));
				}
			}
        }catch(Exception $e){
            // Ante el error salimos por catch.
            $this->SetError(__METHOD__, $e);
        }
		return $result;
	}

/**
* Summary. Obtiene el nombre (ruta) o crea el directorio de la biblioteca que se pasa como parámetro.
* @param int $id optional. El id de la biblioteca, si no se pasa, entonces se asume la biblioteca previamente obtenida con ->Get
* @return str $result. El path (ruta de directorios) absoluto hacia el directorio físico de la biblioteca, o sea 'c:\dir1\dir2\biblioteca\sarasa\'
*/
	public function GetOrCreate($id = null, $site = "backend")
	{
		$result = null;
		try {
			if (!empty($id) and !is_null(SecureInt($id)) and ($id != $this->id)) {
				$this->Get($id);
			}
			if (!$this->existe) { throw new Exception(__LINE__." No se indicó una biblioteca o la indicada no existe (".$this->id.")."); }
			if (empty($this->directorio)) {
				$nombre = substr(cSideKick::GenerateAlias($this->nomape),0,32);
				if (empty($nombre)) { $nombre = substr(md5(cFechas::Ahora().rand(100,999)),0,rand(5,7)); }
				$this->Update($this->tabla_bibliotecas,['nombre'=>$this->RealEscape($nombre),'sys_fecha_modif'=>cFechas::Ahora()], "`id` = ".$this->id);
				$this->directorio = $nombre;
			}
			$theDir = EnsureTrailingSlash($this->base_path.DS.$site.DS.$this->directorio);
			cSideKick::EnsureDirExists($theDir);
			$result = $theDir;
		} catch(Exception $e) {
			$this->SetError(__METHOD__,$e);			
		}
		return $result;
	}
/**
* Summary. Agregar un archivo a la biblioteca
* @param str $archivo El nombre del archivo.
* @param int $id opcional El id de la biblioteca.
*/
	public function AddFile($archivo, $id = null) {
		$result = false;
		try {
			if (is_null($id)) { $id = @$this->id; }
			if (is_null($id)) { throw new Exception(__LINE__." No se ha seleccionado ninguna biblioteca."); }
			$archivo = trim($archivo);
			if (empty($archivo)) { throw new Exception(__LINE__." No se indicó ningún archivo."); }
			
			$reg = array();
			$reg['biblioteca_id'] = $id;
			$reg['nombre'] = $archivo;
			$reg['sys_fecha_modif'] = (isset($this->fecha_custom))? $this->fecha_custom:cFechas::Ahora();
			$reg['sys_usuario_id'] = $this->usuario_id;
			
			$this->sql = "SELECT `id` FROM ".SQLQuote($this->tabla_bibliotecas_archivos)." AS `archivos` WHERE `archivos`.`biblioteca_id` = ".$id." AND LOWER(`archivos`.`nombre`) = LOWER('".$this->RealEscape($archivo)."') LIMIT 1;";
			$this->mainTable = $this->tabla_bibliotecas_archivos;
			$reg = $this->RealEscapeArray($reg);
			if ($fila = $this->FirstQuery($this->sql)) {
				$this->Update($this->tabla_bibliotecas_archivos, $reg, "`id` = ".$fila['id']);
			} else {
				$reg['sys_fecha_alta'] = (isset($this->fecha_custom))? $this->fecha_custom:cFechas::Ahora();
				$this->Insert($this->tabla_bibliotecas_archivos, $reg);
			}
			$this->mainTable = $this->tabla_bibliotecas;
			$this->Touch();
			$result = true;
		} catch(Exception $e) {
			$this->SetError(__METHOD__,$e);			
		}
		return $result;
	}
/**
* Summary. Cambia el momento de modificación del registro de biblioteca.
*/
	private function Touch() {
		try {
			$this->Update($this->tabla_bibliotecas, ['sys_fecha_modif'=>cFechas::Ahora(),'sys_usuario_id'=>@$this->usuario_id],"`id` = ".$this->id);
		} catch(Exception $e) {
			$this->SetError(__METHOD__,$e);			
		}
	}

	
	public function GetCarpetasPersona($persona_id,$route = ""){
		$result = false;
		$aux_name = "";
		try {
			if(!SecureInt($persona_id)){ throw new Exception(__LINE__." No se ha seleccionado persona para la biblioteca."); }
			if(ExisteCarpeta($this->base_path)){
				if(!ExisteCarpeta($this->base_path.DS.$this->nombre)){
					mkdir($this->base_path.DS.$this->nombre);
				}
				if(!empty($route)){
					if(!ExisteCarpeta($this->base_path.DS.$this->nombre.DS.$route)){
						cLogging::Write(__FILE__ ." ".__LINE__ ." No existe la carpeta buscada -> ".$this->base_path.DS.$this->nombre.DS.$route);
						return false;
					}
				}
				if ($opendirectory = opendir($this->base_path.DS.$this->nombre.DS.$route)){
					while (($file = readdir($opendirectory)) !== false){
						if($file != "." && $file != ".."){
							$aux_name = $file;
							if(is_dir($this->base_path.DS.$this->nombre.DS.$route.DS.$file)){
								$result[$file] = $this->BuscarHijos($route.DS.$file);
							}else{
								$result["archivos"][] = array("nombre"=>$file,"sys_fecha_modif"=>date ("Y-m-d H:i:s.", filemtime($this->base_path.$this->nombre.DS.$route.DS.$file)));

							}
						}
					}
					closedir($opendirectory);
				}
			}
			
		} catch(Exception $e) {
			$this->SetError(__METHOD__,$e);			
		}
		return $result;	
	}


	public function GetByBaseDatosBiblioteca($persona_id){
		$result = false;
		try {
			if(!SecureInt($persona_id)){ throw new Exception(__LINE__." No se ha seleccionado persona para la biblioteca."); }
			$this->sql = "SELECT * FROM ".$this->tabla_bibliotecas." WHERE `persona_id` = ".$persona_id;
			if($datos = $this->FirstQuery()){
				$result = $datos;
			}
		} catch(Exception $e) {
			$this->SetError(__METHOD__,$e);			
		}
		return $result;	
	}

	public function GetAllBiblioteca(){
		$result = false;
		$aux_name = "";
		try {
			if(ExisteCarpeta($this->base_path)){
				if ($opendirectory = opendir($this->base_path)){
					while (($file = readdir($opendirectory)) !== false){
						if($file != "." && $file != ".."){
							$aux_name = $file;
							if(ExisteCarpeta($this->base_path.$file)){
								$result[$file] = $this->BuscarHijosBase($file);
							}
						}
					}
					closedir($opendirectory);
				}
			}
			
		} catch(Exception $e) {
			$this->SetError(__METHOD__,$e);			
		}
		return $result;	
	}


	private function BuscarHijosBase($carpeta){
		$result = false;
		$aux_name = $carpeta;	
		if ($opendirectory = opendir($this->base_path.DS.$carpeta)){
			while (($file = readdir($opendirectory)) !== false){
				if($file != "." && $file != ".."){	
					if(is_file($this->base_path.DS.$carpeta.DS.$file)){
						$result["archivos"][] = array("nombre"=>$file,"sys_fecha_modif"=>date ("Y-m-d H:i:s.", filemtime($this->base_path.DS.$carpeta.DS.$file)));
					}else{
						$result["folder"][$file] = $this->BuscarHijosBase($carpeta.DS.$file);
					}
				}
			}
			closedir($opendirectory);
		}
		return $result;
	}

	private function BuscarHijos($carpeta){
		$result = false;
		$aux_name = $carpeta;	
		if ($opendirectory = opendir($this->base_path.$this->nombre.DS.$carpeta)){
			while (($file = readdir($opendirectory)) !== false){
				if($file != "." && $file != ".."){	
					if(is_file($this->base_path.$this->nombre.DS.$carpeta.DS.$file)){
						$result["archivos"][] = array("nombre"=>$file,"sys_fecha_modif"=>date ("Y-m-d H:i:s.", filemtime($this->base_path.$this->nombre.DS.$carpeta.DS.$file)));
					}else{
						$result["folder"][$file] = $this->BuscarHijos($carpeta.DS.$file);
					}
				}
			}
			closedir($opendirectory);
		}
		return $result;
	}
	
	/**
	*	Summary. Obtiene la biblioteca de reportes asignada al backend
	*	@return bool $result
	*/
	public function GetBiblioReportes(){
		$result = false;
		try{
			$sql = "SELECT `id` FROM ".SQLQuote($this->tabla_bibliotecas)." WHERE `persona_id` IS NULL";
			$this->Query($sql);
			if($fila = $this->First()){
				$this->id = $fila['id'];
				$result = true;
			}else{
				$reg = array(
					'sys_fecha_alta' => cFechas::Ahora(),
					'sys_fecha_modif' => cFechas::Ahora(),
					'sys_usuario_id' => 1
				);
				$result = $this->Insert($this->tabla_bibliotecas,$reg);
				$this->id = $this->last_id;
			}
		}catch(Exception $e) {
			$this->SetError(__METHOD__,$e);
		}
		return $result;
	}
} // Fin de clase
?>