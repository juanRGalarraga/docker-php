<?php
/*
	Clase para manejar los mensajes entre usuarios del backend
	Created: 2020-02-21
	Author: DriverOp
*/

class cUsrMsgs extends cModels {
	
	
	private $tabla_mensajes = TBL_backend_mensajes;
	private $tabla_usuarios = TBL_backend_usuarios;
	private $tabla_leidos = "backend_messages_read";
	private $tabla_perfiles = TBL_backend_perfiles;

	function __construct($usuario) {
		parent::__construct();
		$this->actual_file = __FILE__;
		$this->tabla_principal = $this->tabla_mensajes;
		$this->usuario = $usuario;
	}

/*
	Obtiene un mensaje para el usuario actual.
	Establece las propiedades del objeto tomando los campos de la tabla.
*/
	public function Get($id=null):?object {
		$result = false;
		try {
			if (SecureInt($id,null) == null) { throw new Exception(__LINE__." ID debe ser un número."); }
			$this->sql = "SELECT * FROM ".SQLQuote($this->tabla_mensajes)." AS `mensajes` WHERE `mensajes`.`id` = ".$id." AND ((FIND_IN_SET('".$this->usuario->nivel."', `mensajes`.`grupo`)) OR (`mensajes`.`to_id` = ".$this->usuario->id.") OR (`mensajes`.`from_id` = ".$this->usuario->id.") OR (FIND_IN_SET('ALL', `mensajes`.`grupo`)))";
			if (parent::Get($id)) {
				$this->ParseRecord();
				$this->GetFromUser();
				$this->GetLeído();
				$result = true;
			}
		} catch(Exception $e) {
			$this->SetError(__METHOD__,$e->GetMessage());
		}
		return $result;
	}
	
/*
	$que indica cuáles mensajes devolver:
	0 => todos
	1 => no leídos
	-1 => leídos
*/
	public function GetMessages($que = 0, $limit = 25) {
		$result = false;
		try {
			$this->usuario->GetPerfil();
			$sql = "SELECT `mensajes`.*, `usuarios`.`nombre`, `usuarios`.`apellido`, `leidos`.`fechahora` AS `fechahora_leido` FROM ".SQLQuote($this->tabla_mensajes)." AS `mensajes` ";
			$sql .= "LEFT JOIN ".SQLQuote($this->tabla_usuarios)." AS `usuarios` ON `mensajes`.`from_id` = `usuarios`.`id` ";
			$sql .= "LEFT JOIN ".SQLQuote($this->tabla_leidos)." AS `leidos` ON (`leidos`.`msgid` = `mensajes`.`id` AND `leidos`.`usuario_id` = ".$this->usuario->id.") ";
			$sql .= "WHERE ((`to_id` = ".$this->usuario->id.") OR (FIND_IN_SET('".$this->usuario->nivel."', `mensajes`.`grupo`)) ";
			if (isset($this->usuario->perfil->alias)) {
				$sql .= "OR (FIND_IN_SET('".$this->usuario->perfil->alias."', `mensajes`.`grupo`)) ";
			}
			$sql .= "OR (FIND_IN_SET('ALL', `mensajes`.`grupo`))) ";
			if ($que != 0) {
				$sql .= "AND (`leidos`.`fechahora` IS ";
				$sql .= ($que < 0)?"NOT":"";
				$sql .= " NULL) ";
			}
			$sql .= "ORDER BY `mensajes`.`fechahora` DESC ";
			$sql .= "LIMIT 0,".$limit.";";
			//Echolog($sql);
			$this->Query($sql, true);
		} catch(Exception $e) {
			$this->SetError(__METHOD__,$e->getMessage());
		}
		return $result;
	}
	
	public function Next($res = null) {
		$fila = parent::Next($res);
		if ($fila) {
			$fila['leido'] = !is_null($fila['fechahora_leido']);
		}
		return $fila;
	}
	
	public function MarkAsRead() {
		$result = false;
		if ($this->encontrado) {
			try {
				
				$reg = array();
				$reg['fechahora'] = cFechas::Ahora();
				
				$sql = "SELECT * FROM ".SQLQuote($this->tabla_leidos)." WHERE `msgid` = ".$this->id." AND `usuario_id` = ".$this->usuario->id." LIMIT 1;";
				$this->Query($sql);
				if ($fila = $this->First()) {
					$this->Update($this->tabla_leidos, $reg, "`id` = ".$fila['id']);
				} else {
					$reg['msgid'] = $this->id;
					$reg['usuario_id'] = $this->usuario->id;
					$this->Insert($this->tabla_leidos, $reg);
				}
				$result = true;
			} catch(Exception $e) {
				$this->SetError(__METHOD__,$e->getMessage());
			}
		}
		return $result;
	}
/*
	Busca al autor del mensaje.
*/
	private function GetFromUser() {
		$result = false;
		$this->from = new stdClass();
		$this->from->encontrado = false;
		if ($this->encontrado) {
			try {
				$sql = "SELECT `usuarios`.*, `perfiles`.`nombre` AS `nombre_perfil` FROM ".SQLQuote($this->tabla_usuarios)." AS `usuarios` LEFT JOIN ".SQLQuote($this->tabla_perfiles)." AS `perfiles` ON `perfiles`.`id` = `usuarios`.`perfil_id` WHERE `usuarios`.`id` = ".$this->from_id." ";
				$this->Query($sql);
				if ($fila = $this->First()) {
					$this->from->encontrado = true;
					$this->from->id = $fila['id'];
					$this->from->nombre = $fila['nombre'];
					$this->from->apellido = $fila['apellido'];
					$this->from->nivel = $fila['nivel'];
					$this->from->perfil_id = $fila['perfil_id'];
					$this->from->perfil = $fila['nombre_perfil'];
					$result = true;
				}
			} catch(Exception $e) {
				$this->SetError(__METHOD__,$e->getMessage());
			}
		}
		return $result;
	}
/*
	Determina si el mensaje actual fue leído y cuándo.
*/
	private function GetLeído() {
		$result = false;
		if ($this->encontrado) {
			$this->fechahora_leido = null;
			try {
				$sql = "SELECT `fechahora` FROM ".SQLQuote($this->tabla_leidos)." AS `leidos` WHERE `msgid` = ".$this->id." AND `usuario_id` = ".$this->usuario->id."";
				$this->Query($sql);
				if ($fila = $this->First()) {
					$this->fechahora_leido = $fila['fechahora'];
					$result = true;
				}
			} catch(Exception $e) {
				$this->SetError(__METHOD__,$e->getMessage());
			}
		}
		return $result;
	}
}

?>