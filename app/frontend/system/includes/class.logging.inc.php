<?php
/*
	Constantes para establecer el tipo de evento en los logs del sistema.
	Modif: 2021-02-05
	Desc:
		Ahora ya no es necesario establecer $this_file ya que con pasarle __FILE__ esta clase se encarga de eliminar BASE_DIR.
*/

require_once(DIR_model."class.dbutil.3.0.inc.php");

define("LGEV_ALL", 0); // Todos los eventos.
define("LGEV_DEBUG", 1); // Evento de debug.
define("LGEV_INFO", 2); // Información
define("LGEV_WARN", 3); // Aviso de que algo pudo salir mal
define("LGEV_ERROR", 4); // Algo salió mal pero se puede seguir.
define("LGEV_FATAL", 5); // Todo se fue al carajo.
define("LGEV_OFF", 6); // Se apaga el log.
define("LGEV_TRACE", 7); // El log incluye más detalles en la descripción de este evento.

define("LGEV_TARGET_FILE",1); // Se loguea a un archivo.
define("LGEV_TARGET_DB",2); // Se loguea a la base de datos.

define("LGEV_SOURCE","core"); // De dónde viene el log
define("LGEV_REMOTE_IP",true); // El log debe incluir la IP remota?

//define("LGEV_DEFAULT_TARGET",LGEV_TARGET_FILE+LGEV_TARGET_DB); // A donde se escribe el log por omisión.
define("LGEV_DEFAULT_TARGET",LGEV_TARGET_FILE);


if (!defined("LGEV_LEVEL")) {
	define("LGEV_LEVEL",0);
}

const LGEV_EVENT_TEXT = array('ALL','DEBUG','INFO','WARN','ERROR','FATAL','OFF','TRACE');

class cLogging {

	
	private static $default_target = LGEV_DEFAULT_TARGET;
	private static $default_postfix = '';
	private static $dbtabla = 'sys_logging';
	
	static function SetTarget($target) {
		self::$default_target = $target;
	}
	
	static function SetPostfix($postfix) {
		self::$default_postfix = $postfix;
	}

	static function Write($linea = NULL, $event = LGEV_DEBUG, $target = NULL) {
		if ($event < LGEV_LEVEL) { return; }
		if ((is_null($target)) and (self::$default_target != 0)) {
			$target = self::$default_target;
		}
		if ($target & LGEV_TARGET_FILE) {
			self::LogToFile($linea, NULL, $event);
		}
		if ($target & LGEV_TARGET_DB) {
			self::LogToDB($linea, $event);
		}
	}
	
	static function LogToFile($text, $otroArchivo = null, $event = LGEV_DEBUG) {
		if ($event < LGEV_LEVEL) { return; }
		umask(0);
	
		$mes = Date('Y-m');
		$dia = Date('Y-m-d');
		
		$dir = DIR_logging.$mes;
		$archivo = $dir.'/'.$dia;

		if (!empty($otroArchivo)) {
			$archivo .= $otroArchivo;
		}
		if (!empty(self::$default_postfix)) {
			$archivo .= '_'.self::$default_postfix;
		}
		$archivo .= '.log';
		
		if (!file_exists(DIR_logging)) {
			mkdir(DIR_logging,0777);
		}
	
		if (!file_exists($dir)) {
			mkdir($dir,0777);
		}
		
		$linea = '['.Date('Y-m-d H:i:s').'] ';
		if (LGEV_REMOTE_IP) {
			$linea .= GetIP().' - ';
		}
		$linea .= LGEV_SOURCE.' - ';
		if (($event > -1)) {
			$linea .= ((isset(LGEV_EVENT_TEXT[$event]))?LGEV_EVENT_TEXT[$event]:$event)." - ";
		}

		if (strpos($text, DIR_BASE) !== false) {
			$text = substr_replace($text, '', strpos($text, DIR_BASE), strlen(DIR_BASE));
		}

		$linea .= trim($text).PHP_EOL;
		
		return file_put_contents($archivo, $linea, FILE_APPEND);
	}

	static function LogToDB($text, $event = LGEV_DEBUG, $trace = null) {
		if ($event < LGEV_LEVEL) { return; }
		$error_level = error_reporting();
		error_reporting(E_ERROR | E_PARSE);
		try {
			$db = new cDB();
			$db->Connect(DBHOST, DBNAME, DBUSER, DBPASS, DBPORT);
			if ($db->IsConnected()) {
				$reg = array();
				$reg['fechahora'] = Date('Y-m-d H:i:s');
				if (LGEV_REMOTE_IP) {
					$reg['remote_ip'] = GetIP();
				}
				if (strpos($text, DIR_BASE) !== false) {
					$text = substr_replace($text, '', strpos($text, DIR_BASE), strlen(DIR_BASE));
				}
				$reg['source'] = LGEV_SOURCE;
				$reg['tipo_evento'] = (isset(LGEV_EVENT_TEXT[$event]))?LGEV_EVENT_TEXT[$event]:$event;
				$reg['descripcion'] = $db->RealEscape(substr($text,0,255));
				if (!empty($trace)) {
					if (is_object($trace) or is_array($trace)) {
						$reg['data'] = $db->RealEscape(json_encode($trace, JSON_HACELO_BONITO_CON_ARRAY));
					} else {
						$reg['data'] = $db->RealEscape($trace);
					}
				}
				if (isset($objeto_usuario) and isset($objeto_usuario->id)) {
					$reg['usuario_id'] = $objeto_usuario->id;
				}
				$db->Insert(self::$dbtabla, $reg);
				if ($db->error) { throw new Exception('DBErr: '.$db->errmsg); }
			} else {
				throw new Exception('DBErr: No se pudo conectar a la base de datos: '.$db->errmsg);
			}
		} catch(Exception $e) {
			self::LogToFile(__FILE__.$e->GetMessage(), null, LGEV_ERROR);
		}
		error_reporting($error_level);
	}
	
	static function EventToText($event) {
		$result = NULL;
		switch ($event) {
			case 0: $result = 'ALL'; break;
			case 1: $result = 'DEBUG'; break;
			case 2: $result = 'INFO'; break;
			case 3: $result = 'WARN'; break;
			case 4: $result = 'ERROR'; break;
			case 5: $result = 'FATAL'; break;
			case 6: $result = 'OFF'; break;
			case 7: $result = 'TRACE'; break;
		}
		return $result;
	}
	
	static function TrimBaseFile($file) {
		if (strpos($file, DIR_BASE) !== false) {
			$file = substr_replace($file, '', strpos($file, DIR_BASE), strlen(DIR_BASE));
		}
		return $file;
	}
}