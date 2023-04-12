<?php
/*
	Clase para componer los mensajes de correo electrónico.
	(Esto salió de Becas.)
	Updated: 2021-10-19
	Author: DriverOp
*/

require_once(DIR_includes."class.logging.inc.php");

class cMakeMessage {
	
	private $contenido = null;
	
	public $msgerr = null;
	public $error = false;
	public $DebugOutput = false;
	
	
	public function __construct() {
		
	}
	
/**
* Summary. Dada una ruta con un nombre de archivo, verifica que exista y que sea un template de correo electrónico.
* @param string $theTemplate Ruta hacia el archivo de template.
* @return bool.
*/
	public function LoadTemplate(string $theTemplate):bool {
		$result = false;
		$this->contenido = null;
		try {
			['basename'=>$basename, 'dirname'=>$dirname, 'extension'=>$extension] = pathinfo($theTemplate);
			if (empty($dirname)) { throw new Exception("'$dirname' No es una ruta válida."); }
			if (!is_dir($dirname)) { throw new Exception("Directorio no existe: '$dirname'"); }
			if (empty($basename)) { throw new Exception("No se indicó nombre de archivo."); }
			if (!ExisteArchivo($theTemplate)) { throw new Exception("Archivo no encontrado: '$theTemplate'"); }
			$extension = strtolower($extension);
			if (!in_array($extension, ['htm','html'])) { throw new Exception("Tipo de archivo no permitido: '$extension'"); }
			$this->contenido = file_get_contents($theTemplate);
			$result = true;
		} catch(Exception $e) {
			$this->SetError($e);
		}
		return $result;
	} // LoadTemplate
	
/**
* Summary. Proporcionando un array donde el índice es el tag y el valor el valor a reemplazar, busca en ->contenido los tags y los reemplaza por su valor.
* @param array $tags El array con los tags=>valor
*/
	public function SetValues(array $tags):bool {
		$result = false;
		try {
			if (empty($this->contenido)) { return true; }
			if (!CanUseArray($tags)) { return true; }
		
			foreach($tags as $key => $value) {
				$this->contenido = preg_replace('~\['.$key.'\]~', $value, $this->contenido);
			}
		} catch(Exception $e) {
			$this->SetError($e);
		}
		return $result;
	}
	
/**
* Summary. Devuelve el contenido del mensaje.
*/
	public function GetContent() {
		return $this->contenido;
	}
	
	
	public function SetError($e) {
		$this->msgerr = $e->GetMessage();
		$this->error = true;
		$trace = debug_backtrace();
		$caller = @$trace[1];
		$file = @$trace[0]['file'];
		if (strpos($file, DIR_BASE) !== false) {
			$file = substr_replace($file, '', strpos($file, DIR_BASE), strlen(DIR_BASE));
		}
		$line = sprintf('%s:%s %s%s', $file, @$e->GetLine(), ((isset($caller['class']))?$caller['class'].'->':null), @$caller['function']);
		$line .= ' '.$this->msgerr;
		if (DEVELOPE and $this->DebugOutput) { EchoLogP(htmlentities($line)); }
		cLogging::Write($line, LGEV_WARN);
	} // SetError
	
}