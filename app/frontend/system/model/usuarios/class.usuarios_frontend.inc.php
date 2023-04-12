<?php
/*
	Clase para el manejo de las cuentas de ingreso de los clientes a sus estados de cuenta, solicitudes de préstamo, etc...
	Está fuertemente atado a la tabla "personas".
	
	Created: 2019-10-28
	Author: DriverOp.
	
	
*/

require_once(DIR_model."usuarios".DS."class.usuarios.inc.php");
require_once(DIR_model."class.clientes.inc.php");
require_once(DIR_includes."class.fechas.inc.php");

class cUsrFrontend extends cUsuarios {
	
	protected $tabla_usuarios = TBL_personas;
	protected $tabla_sesiones = TBL_frontend_sesiones;
	public $actual_file = __FILE__;
	
	protected $salt = "ElPicoDelPato"; // Sal de la contraseña.
	protected $SecretKey = 1; // Multiplicador de la sesión
	protected $session_name = 'USR_FRONTEND'; // Nombre de la sesión PHP
	public $tsession = 3600; // Tiempo de vida de la sesión, en segudos.
	
	public $cliente = null;
	
	function __construct() {
		parent::__construct();
		$this->tabla_principal = $this->tabla_usuarios;
		$this->cliente = new cCliente();
	}

/*
	Busca una persona con el número de documento indicado.
*/
	public function GetByDNI($dni) {
		$result = false;
		try {
			$dni = $this->RealEscape(substr(trim($dni),0,32));
			$sql = "SELECT * FROM ".SQLQuote($this->tabla_usuarios)." WHERE `nro_doc` = '".$dni."' LIMIT 1;";
			$this->Query($sql);
			if ($this->raw_record = $this->First()) {
				$this->SetUserData();
				if (!empty($this->raw_record['data'])) {
					$this->data = json_decode($this->raw_record['data']);
					foreach($this->data as $key => $value) {
						$this->$key = $value;
					}
				}
				if (isset($this->password) and !empty($this->password)) { // Para que la persona sea usuario debe tener una password asignada, de lo contrario tratarlo como si no fuera usuario.
					$result = true;
				}
			}
		} catch(Exception $e) {
			$this->SetError(__METHOD__,$e->getMessage());
		}
		return $result;
	}
	
/*
	Establecer la contraseña al usuario.
*/
	public function SetUserPassword($password) {
		$result = false;
		try {
			if (empty($password)) { throw new Exception("No se puede establecer una contraseña vacía."); }
			if (strlen($password) > $this->PasswordMaxLenght) { throw new Exception("La nueva contraseña es demasiado larga."); }
			$this->newpassword = $this->GeneratePassword($password);
			
			if (!isset($this->data) or !is_object($this->data)) {
				$this->data = new stdClass();
				$this->data->password = '';
				$this->data->tsession = 3600;
				$this->data->rpp = 15;
				$this->data->jumble = '';
			}
			$this->data->password = $this->newpassword;
			
			$reg = array(
				'fecha_modif'=>Date('Y-m-d H:i:s'),
				'usuario_id'=>null,
				'data'=>json_encode($this->data, JSON_HACELO_BONITO)
			);
			$this->Update($this->tabla_usuarios,$reg,"`id` = ".$this->id);
			$this->password = $this->newpassword;
			$result = true;
		} catch (Exception $e) {
			$this->SetError(__METHOD__,$e->GetMessage());
		}
		return $result;
	}
/*
	Leer la sesión de usuario sin recurrir a la cookie.
*/
	public function GetSessionDirect($sesion_id) {
		$result = false;
		try {
			$sql = "SELECT * FROM ".SQLQuote($this->tabla_sesiones)." WHERE `id` = ".$sesion_id." AND `estado` = 1 ORDER BY `fecha_hora` DESC LIMIT 1;";
			$this->Query($sql);
			if ($this->raw_record = $this->First()) {
				if ((date("U")-$this->raw_record['idle']) < $this->tsession) {
					$result = true;
					$this->UpdateSessionDirect($this->raw_record['id']);
				}
			}
		} catch(Exception $e) {
			$this->SetError(__METHOD__,$e->getMessage());
		}
		return $result;
	}
/*
	Actualizar la sesión del usuario sin pasar por la cookie.
*/
	public function UpdateSessionDirect($sesion_id) {
		$result = false;
		try {
			$this->Update($this->tabla_sesiones,array(
				'idle'=>date("U")
			),"`id` = ".$sesion_id);
		} catch(Exception $e) {
			$this->SetError(__METHOD__,$e->getMessage());
		}
		return $result;
	}
/*
	Iniciar la sesión directamente.
	$usuario_id es el 
*/
	public function SetSessionDirect($usuario_id) {
		$result = false;
		try {
			$this->Update($this->tabla_sesiones,array('estado'=>0),"`usuario_id` = ".$usuario_id);
			$this->Insert($this->tabla_sesiones,array(
				'usuario_id'=>$usuario_id,
				'estado'=>1,
				'idle'=>date("U"),
				'navegador'=>'From: '.@$_SERVER['HTTP_REFERER'],
				'ip'=>GetIP(),
				'fecha_hora'=>date('Y-m-d H:i:s')
			));
			$result = $this->last_id;
		} catch(Exception $e) {
			$this->SetError(__METHOD__,$e->getMessage());
		}
		return $result;
	}
/*
	Acabar con la sesión.
*/
	public function EndSessionDirect($session_id) {
		$result = false;
		try {
			$this->Update($this->tabla_sesiones,array('estado'=>0),"`id` = ".$session_id);
		} catch(Exception $e) {
			$this->SetError(__METHOD__,$e->getMessage());
		}
		return $result;
	}
/*
	Dado un id de sesión extraer todos los datos del cliente al que pertenece esa sesión, siempre y cuando la sesión esté en estado = 1
*/
	public function GetByRemoteSession($sesion_id) {
		$result = false;
		if ($this->GetSessionDirect($sesion_id)) {
			if ($this->cliente->Get($this->raw_record['usuario_id'])) {
				$result = true;
			}
		}
		return $result;
	} // GetByRemoteSession
}
?>