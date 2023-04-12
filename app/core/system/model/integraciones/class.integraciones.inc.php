<?php
/**
* Summary. Esto es una clase helper para las integraciones.
* Implementa, por ejemplo, el log de eventos en el log principal que está en una base de datos separada de la principal.
* Created: 2021-10-05
* Author: DriverOp
*/

require_once(DIR_model."class.fundation.inc.php");

define("MASTERLOG_HOST", DBHOST);
define("MASTERLOG_NAME", 'mecha_logs');
define("MASTERLOG_USER", DBUSER);
define("MASTERLOG_PASS", DBPASS);
define("MASTERLOG_PORT", DBPORT);

const LOG_MASTER_RECORD = array(
	'fechahora'=>null,
	'request'=>null,
	'response'=>null,
	'http_status'=>null,
	'solicitud_id'=>null
);

class cIntegracion extends cModels {
	
	private $record = LOG_MASTER_RECORD;
	public $solicitud_id = null;
	public $mainTable = null;
	
/**
* Summary. Constructor de la clase.
* @param string $alias Un alias con el cual apuntar a la tabla correspondiente.
*/
	function __construct($alias) {
		parent::__construct(null, null, null, null, null);
		defined("MASTERLOG_HOST") || define("MASTERLOG_HOST", DBHOST);
		defined("MASTERLOG_NAME") || define("MASTERLOG_NAME", DBNAME);
		defined("MASTERLOG_USER") || define("MASTERLOG_USER", DBUSER);
		defined("MASTERLOG_PASS") || define("MASTERLOG_PASS", DBPASS);
		defined("MASTERLOG_PORT") || define("MASTERLOG_PORT", DBPORT);
		parent::__construct(
			MASTERLOG_HOST,
			MASTERLOG_NAME, 
			MASTERLOG_USER, 
			MASTERLOG_PASS, 
			MASTERLOG_PORT);
		
		if (!$this->IsConnected()) { return; }
		$this->mainTable = $alias;
		if (!$this->TableExists()) {
			$this->CreateTable();
		}
		$this->ResetInstance();
	}

/**
* Summary. Al eliminarse la clase, escribe el registro (buffer) pendiente en la tabla.
*/
	function __destruct() {
		if ($this->record != LOG_MASTER_RECORD) {
			$this->Save();
		}
		if ($this->IsConnected()) {
			$this->Disconnect();
		}
	}

/**
* Summary. Agregar un dato al registro.
* @param array $data.
* @note. Se debe llamar a este método para ir rellenando con valores los campos del registro a escribir. Notar que dos o más llamadas a este método estableciendo el mismo campo, sobreescribe el valor anterior.
*/
	public function Add(array $data):?bool {
		$data = array_change_key_case($data);
		$this->record = array_merge($this->record,$data);
		if (!empty($this->record['solicitud_id'])) {
			$this->solicitud_id = $this->record['solicitud_id'];
		}
		return true;
	}
/**
* Summary. Devolver el registro actual.
*/
	public function GetRecord() {
		return $this->record;
	}
/**
* Summary. Guardar efectivamente el registro y limpiarlo.
* @return bool.
* @note. Esto crea un registro nuevo. Y limpia el buffer actual dejándolo listo para otro registro.
*/
	public function Save():bool {
		$result = false;
		if (!$this->IsConnected()) { return $result; }
		try {
			$record = $this->FilterFields($this->record);
			if (is_array($record) and !empty($record) and count($record)) {
				$record['fechahora'] = (empty($record['fechahora']))?date('Y-m-d H:i:s'):$record['fechahora'];
				$record['solicitud_id'] = (empty($record['solicitud_id']))?$this->solicitud_id:$record['solicitud_id'];
				$this->Insert($this->mainTable, $record);
				$result = true;
				$this->record = LOG_MASTER_RECORD; // Resetear el registro actual a sus valores "de fábrica".
			}
		} catch(Exception $e) {
			$this->SetErrorEx($e);
		}
		return $result;
	}

/**
* Summary. Crea una tabla de logs nueva con el nombre de la tabla principal.
*/
	private function CreateTable() {
		$sentencia = <<<END
CREATE TABLE IF NOT EXISTS `$this->mainTable` (
  `id` int NOT NULL AUTO_INCREMENT,
  `fechahora` datetime DEFAULT NULL,
  `request` text,
  `response` text,
  `http_status` int DEFAULT NULL,
  `solicitud_id` int DEFAULT NULL,
  `estado` enum('NORMAL','ERROR') DEFAULT 'NORMAL',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
END;
		$this->Query($sentencia);
	}
}
