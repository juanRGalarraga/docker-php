<?php
/*
	Author: DriverOp
	Created: 2018-10-18
	Desc: Clase para el manejo de usuarios, no funciona por sí misma, hay que derivar otra clase a partir de ésta.
	Modif: 2018-10-23
	Desc: Cuando $salt está vacío, no salar la contraseña.
	Modif: 2019-10-11
	Desc: Reparado bug. Cuando en el registro de la DB no existía el campo 'tsession' en 'opciones', la sesión nunca se hacía válida.
	Modif: 2019-10-17
	Desc: Derivar de cFundation.
	
	Modif: 2020-09-25
	Desc: Implementar mecanismo de contraseña robuta. Modificados métodos ValidPass y GeneratePassword. Agregado método GetPepper.
	La "salt" de la contraseña ahora es la fecha de alta del registro de usuario.
	Modif: 2020-12-08
	Author: DriverOp
	Desc: Quitadas referencias a Reset() ya que ahora está en el ancestro
*/

require_once(DIR_includes."class.logging.inc.php");
require_once(DIR_includes."class.fechas.inc.php");
require_once(DIR_model."class.fundation.inc.php");

const USUARIOS_NIVEL_INT = array('OWN'=>1,"ADM"=>2,"USR"=>3);

class cUsuarios extends cModels {
	protected $salt = ""; // Obsoleto.
	protected $SecretKey = null;
	protected $PasswordMaxLenght = 256;
	public $session_name = null;
	public $tsession = 0;
	public $pepper_file = 'pepper.txt';
	//public $pepper_file = 'pepper.txt';
	
	private $session_record = null;
	public $esta_logueado = false;
	public $DebugOutput = false;
	private $tabla_usuarios = TBL_backend_usuarios;
	
	
	public function __construct() {
		parent::__construct();
		$this->actual_file = __FILE__;
		if (defined("PEPPER_FILE")) {
			$this->pepper_file = PEPPER_FILE;
		}
	}

	
	public function Get($id=null):?object {
		$result = false;
		try {
			$this->forceParse = true;
			if (SecureInt($id,null) == null) { throw new Exception(__LINE__." ID debe ser un número."); }
			$this->sql = "SELECT * FROM ".SQLQuote($this->tabla_usuarios)." WHERE `id` = ".$id;
			if (parent::Get($id)) {
				$this->ParseRecord();
				$result = true;
				if (!empty($this->opciones)) {
					foreach($this->opciones as $key => $value) {
						$this->$key = $value;
					}
				}
				if (!empty($this->data)) {
					foreach($this->data as $key => $value) {
						$this->$key = $value;
					}
				}
				$this->SetUserData();
			}
		} catch (Exception $e) {
			$this->SetError(__METHOD__,$e->GetMessage());
		}
		return $result;
	}
/**
	 * Summary. Crea un usuario nuevo usando los datos de $data
	 * @param array $data debe ser un array donde el índice es el nombre del campo y el valor el valor a almacenar en ese campo.
	 * @return bool/int false si hubo un error, o el ID del registro creado.
	 ************** NO VERIFICA LA VALIDEZ DE LOS DATOS ************
*/
	public function Create($data, $usuario_id = NULL)
	{
		$ahora = cFechas::Ahora();
		$result = false;
		if (CanUseArray($data)) {
			$reg = array();
			try {
				foreach ($data as $key => $value) {
					if ($key == 'password') {
						$this->fecha_alta = $ahora;
						$value = $this->GeneratePassword($value);
					} else {
						$key = $this->RealEscape($key);
						$value = $this->RealEscape($value);
					}
					if ($key != 'id') { // Just a precaution.
						$reg[$key] = $value;
					}
				}
				$reg['fecha_alta'] = $ahora;
				$reg['fecha_modif'] = $ahora;
				$reg['usuario_id'] = $usuario_id;
				$this->Insert($this->tabla_usuarios, $reg);
				$result = $this->last_id;
				$this->Get($result);
			} catch (Exception $e) {
				$this->SetError(__METHOD__, $e->getMessage());
			}
		}
		return $result;
	}
/**
	 * Summary. Modifica los datos del usuario actual usando los datos de $data
	 * @param array $data debe ser un array donde el índice es el nombre del campo y el valor el valor a almacenar en ese campo.
	 * @return bool/int false si hubo un error, o el ID del registro creado.
	 ************** NO VERIFICA LA VALIDEZ DE LOS DATOS ************
*/
	public function Save($data, $usuario_id = NULL)
	{
		$ahora = cFechas::Ahora();
		$result = false;
		try {
			if (!$this->existe) { throw new Exception(__LINE__." Debe de haber un usuario activo."); }
			if (CanUseArray($data)) {
				$reg = array();
				foreach ($data as $key => $value) {
					if ($key == 'password') {
						$value = $this->GeneratePassword($value);
					} else {
						$key = $this->RealEscape($key);
						$value = $this->RealEscape($value);
					}
					if ($key != 'id') { // Just a precaution.
						$reg[$key] = $value;
					}
				}
				$reg['fecha_modif'] = $ahora;
				$reg['usuario_id'] = $usuario_id;
				$this->Update($this->tabla_usuarios, $reg, "`id` = ".$this->id);
				$result = true;
				$this->Get($this->id);
			}
		} catch (Exception $e) {
			$this->SetError(__METHOD__, $e->getMessage());
		}
		return $result;
	}

/*
	Determina si existe un usuario según su username
*/
	public function GetByUsername($username) {
		$result = false;
		$this->Reset();
		try {
			if (empty($username)) { throw new Exception("No puedo buscar a un usuario anónimo."); }
			$username = substr($username,0,32);
			$username = $this->RealEscape($username);
			$sql = "SELECT * FROM ".SQLQuote($this->tabla_usuarios)." WHERE LOWER(`username`) = LOWER('".$username."') AND `estado` != 'ELI' LIMIT 1;";
			$this->Query($sql);
			if ($this->raw_record = $this->First()) {
				$this->SetUserData();
				$result = true;
			}
		} catch (Exception $e) {
			$this->SetError(__METHOD__,$e->GetMessage());
		}
		return $result;
	}
/*
	Determina si existe un usuario según su email
*/
	public function GetByEmail($email) {
		$result = false;
		try {
			if (empty($email)) { throw new Exception("No puedo buscar a un usuario sin email."); }
			$email = mb_substr($email,0,128);
			$email = $this->RealEscape($email);
			$sql = "SELECT * FROM ".SQLQuote($this->tabla_usuarios)." WHERE LOWER(`email`) = LOWER('".$email."') AND `estado` != 'ELI' LIMIT 1;";
			$this->Query($sql);
			if ($this->raw_record = $this->First()) {
				$this->SetUserData();
				$result = true;
			}
		} catch (Exception $e) {
			$this->SetError(__METHOD__,$e->GetMessage());
		}
		return $result;
	}

/*
	Determina si existe un usuario según su jumble
*/
	public function GetByJumble($jumble) {
		$result = false;
		try {
			if (empty($jumble)) { $jumble = NULL; }
			$jumble = mb_substr($jumble,0,32);
			$jumble = $this->RealEscape($jumble);
			$sql = "SELECT * FROM ".SQLQuote($this->tabla_usuarios)." WHERE LOWER(`jumble`) = LOWER('".$jumble."') AND `estado` != 'ELI' LIMIT 1;";
			
			$this->Query($sql);
			if ($this->raw_record = $this->First()) {
				$this->SetUserData();
				$result = true;
			}
		} catch (Exception $e) {
			$this->SetError(__METHOD__,$e->GetMessage());
		}
		return $result;
	}

/*
	Comprueba si el usuario actual tiene la sesión activa.
*/
	public function CheckLogin() {
		$result = false;
		$this->esta_logueado = false;
		try {
			if ((isset($_SESSION[$this->session_name])) and ($_SESSION[$this->session_name] > 0)) {

				$this->id = $this->DecodeID();
				
				$sql = "SELECT * FROM ".SQLQuote($this->tabla_sesiones)." WHERE `usuario_id` = '".$this->id."' AND `estado` = 1 ORDER BY `id` DESC";
				$this->Query($sql);
				if ($this->session_record = $this->First()) {

					$this->Get($this->session_record['usuario_id']);
					
					//if ($fila['ip'] == GetIP()) { // Es la misma IP?

						if (($this->session_record['navegador'] == $_SERVER['HTTP_USER_AGENT'])) { // Está en el mismo navegador?
						
							if ((date('U')-$this->session_record['idle']) < ((isset($this->tsession))?$this->tsession:3600)) { // La sesión no se venció?
								
								$result = true; // Entonces la sesión es válida.
								$this->esta_logueado = true;
								
							} else {
								$this->SetError(__METHOD__,"La sesión del usuario expiró o tsession no está definido.");
							}
						} else {
							$this->SetError(__METHOD__,"El usuario no está en el mismo navegador que en la sesión anterior.");
						}
					/*} else {
							if ($this->DebugOutput) { $this->SetError(__METHOD__,"El usuario cambió de IP."); }
						} 
					*/
					if ($result) {
						$this->UpdateSession();
					} else {
						$this->Logout();
					}
				} else {
					throw new Exception("No se encontró ninguna sesión para el usuario ".$this->id);
				}
			} else {
				throw new Exception("Cookie de sesión PHP no existe o está vacía.");
			}
		} catch (Exception $e) {
			$this->SetError(__METHOD__,$e->GetMessage());
		}
		return $result;
	}
	
	public function UpdateSession() {
		$idSesion = SecureInt(substr($this->session_record['id'],0,11),null);
		$result = false;
		try {
			if ($idSesion == NULL) { throw new Exception("El id de sesión al que se intenta regenerar es pura fruta."); }
			$this->Update($this->tabla_sesiones,array('idle'=>date("U")),"`id` = ".$idSesion);
			$aux = $_SESSION[$this->session_name];
			unset($_SESSION[$this->session_name]);
			$_SESSION[$this->session_name] = $aux;
			$result = true;
		} catch (Exception $e) {
			$this->SetError(__METHOD__,$e->GetMessage());
		}
		return $result;
	}
/*
	Termina la sesión del usuario. Si $id = null, la del usuario actual, si no, la del usuario apuntado por $id
*/
	public function Logout($id = null) {
		$result = false;
		if ($this->existe) { $id = $this->id; }
		else { $id = $this->DecodeID(); }
		if (empty($id)) { return false; }
		try {
			unset($_SESSION[$this->session_name]);
			$this->Update($this->tabla_sesiones,array('estado'=>0),"`usuario_id` = ".$id);
			$result = true;
		} catch (Exception $e) {
			$this->SetError(__METHOD__,$e->GetMessage());
		}
		return $result;
	}
/*
	Inicia la sesión del usuario actual.
*/
	public function Login() {
		$result = false;
		try {
			$this->Update($this->tabla_sesiones,array('estado'=>0),"`usuario_id` = ".$this->id); // Vencer cualquier otra sesión que el usuario tenga abierta.
			$reg = array(
				'usuario_id'=>$this->id,
				'fecha_hora'=>Date('Y-m-d H:i:s'),
				'estado'=>1,
				'navegador'=>$_SERVER['HTTP_USER_AGENT'],
				'idle'=>Date('U'),
				'ip'=>GetIp()
			);
			$this->Insert($this->tabla_sesiones,$reg);
			$_SESSION[$this->session_name] = $this->EncodeID($this->id);
			$result = true;
		} catch (Exception $e) {
			$this->SetError(__METHOD__,$e->getMessage());
		}
		return $result;
	} // Login
/*
	Devuelve un array con los datos relativos a la última sesión del usuario.
	Si $id es null entonces se refiere al usuario actual.
	Si $prev es true, entonces devuelve la última sesión sin considerar la actual sesión de usuario.
*/
	public function GetSessionData($id = null, $prev = false) {
		$result = null;
		try {
			if ($id == null) {
				$id = $this->id;
			}
			$sql = "SELECT * FROM `".$this->tabla_sesiones."` WHERE `usuario_id` = ".$id." ";
			if ($prev) {
				$sql .= "AND `estado` = 0 ";
			}
			$sql .= "ORDER BY `fecha_hora` DESC LIMIT 1;";
			$this->Query($sql);
			if ($this->numrows > 0) {
				$result = $this->First();
			}
		} catch (Exception $e) {
			$this->SetError(__METHOD__,$e->GetMessage());
		}
		return $result;
	} // GetSessionData

	/*
	 retorna un listado de usuarios activos (estado HAB)
	*/
	public function GetActivos($user_nivel = []) {
		$result = false;
		$this->Reset();
		try {
			$sql = "SELECT * FROM ".SQLQuote($this->tabla_usuarios)." WHERE estado = 'HAB'";
			if(CanUseArray($user_nivel)){
				$sql .= " AND (`nivel` IN ('" . implode("','", $user_nivel) . "')) ";
			}
			
			$this->Query($sql);
			if ($this->numrows > 0) {	
          		$result = array();
          		while($fila = $this->Next()){
                    array_push($result, $fila);
                }
			}
		} catch (Exception $e) {
			$this->SetError(__METHOD__,$e->GetMessage());
		}
		return $result;
	}
	

	/*
	 retorna un listado de usuarios activos (estado HAB)
	*/
	public function GetByMarca($firma) {
		$result = false;
		$this->Reset();
		try {
			$sql = "SELECT * FROM ".SQLQuote($this->tabla_usuarios)." WHERE marca_id = '".$firma."' AND estado = 'HAB'";
			$this->Query($sql);
			if ($this->numrows > 0) {	
          		$result = array();
          		while($fila = $this->Next()){
                    array_push($result, $fila);
                }
			}
		} catch (Exception $e) {
			$this->SetError(__METHOD__,$e->GetMessage());
		}
		return $result;
	}

/*
	Establece el jumble para el recupero de contraseña.
*/
	public function SetJumble($jumble) {
		$result = false;
		try {
			
			$reg = array(
				'jumble'=>$this->RealEscape($jumble),
				'fechahora_jumble'=>Date('Y-m-d H:i:s')
			);
			$this->Update($this->tabla_usuarios,$reg,"`id` = ".$this->id);
			$result = true;
		} catch (Exception $e) {
			$this->SetError(__METHOD__,$e->GetMessage());
		}
		return $result;
	}

	public function SetUserData() { // Pone los datos del usuario en el objeto a partir del registro.
		$this->ParseRecord();
		$this->nombre_apellido = @$this->nombre." ".@$this->apellido;
		$this->NomApe = @$this->nombre." ".@$this->apellido;
		$this->nivel_int = @USUARIOS_NIVEL_INT[$this->nivel];
	}

	public function EncodeID($id) { // Codifica el id en la sesión de usuario. Para que quien se la robe, no la pueda usar.
		$result = null;
		$id = SecureInt($id,-1);
		if ($id > 0) {
			$result = ($id*$this->SecretKey);
		}
		return $result;
	}

	public function DecodeID($id = null) { // Decodifica el id en la sesión del usuario. La inversa de la anterior función.
		$result = null;
		if (!is_null($id) and is_numeric($id)) {
			$result = ($id/$this->SecretKey);
		} else {
			if (isset($_SESSION[$this->session_name])) {
				$cookie = SecureInt($_SESSION[$this->session_name],-1);
				if ($cookie > 0) {
					$result = ($cookie/$this->SecretKey);
				}
				$_SESSION[$this->session_name] = $cookie;
			}
		}
		return $result;
	}
/**
* Summary. Método obsoleto dejado acá para hacer la mudanza transparente de todos los usuarios del sistema.
*/
	public function GenerateOldPassword($cleartext) {
		if (!empty($this->salt)) {
			$salt = md5($this->salt);
			$jam = array(substr($salt,0,16),substr($salt,16,16));
			$result = md5($jam[0].md5($cleartext).$jam[1]);
		} else {
			$result = md5($cleartext);
		}
		return $result;
	}

/**
* Summary. Método obsoleto dejado acá para hacer la mudanza transparente de todos los usuarios del sistema.
*/
	public function ValidOldPass($password) {
		$result = false;
		try {
			$password = $this->GenerateOldPassword($password);
			$result = ($this->oldpassword == $password);
		} catch (Exception $e) {
			$this->SetError(__METHOD__,$e->GetMessage());
		}
		return $result;
	}
/**
* Summary. Establece la nueva contraseña a partir de $password
* @param str $password La contraseña en claro.
* @return bool
*/
	public function SetNewPassword($password) {
		$result = false;
		try {
			if (empty($password)) { throw new Exception("No se puede establecer una contraseña vacía."); }
			if (strlen($password) > $this->PasswordMaxLenght) { throw new Exception("La nueva contraseña es demasiado larga."); }
			$this->newpassword = $this->GeneratePassword($password);
			$reg = array(
				'password'=>$this->newpassword,
				'fecha_modif'=>Date('Y-m-d H:i:s'),
				'usuario_id'=>$this->id
			);
			$this->Update($this->tabla_usuarios,$reg,"`id` = ".$this->id);
			ShowVar($this->error, true);
			$this->password = $this->newpassword;
			$result = true;
		} catch (Exception $e) {
			$this->SetError(__METHOD__,$e->GetMessage());
		}
		return $result;
	}
/**
* Summary. Determina si $password es la contraseña correcta para el usuario actual.
* @param str $cleartext La contraseña en claro que se quiere verificar.
* @return bool
*/
	public function ValidPass($cleartext)
	{
		global $this_file;
		$result = false;
		$updatetonewpass = false;
		try {
			if (empty($this->password) and $this->ValidOldPass($cleartext)) {
				$this->Update($this->tabla_usuarios, array('cleartext'=>$cleartext), "`id` = ".$this->id);
				$this->SetNewPassword($cleartext);
			}
			
			$result = md5($cleartext);
			if (!empty($this->fecha_alta)) {
				$salt = md5($this->fecha_alta);
				$jam = array(substr($salt, 0, 16), substr($salt, 16, 16));
				$result = md5($jam[0] . md5($cleartext) . $jam[1]);
			}
			if (version_compare(PHP_VERSION, '5.5.0', '>=')) {
				$pepper = $this->GetPepper();
				$result = hash_hmac("sha512", $result, $pepper);
			}
			$result = password_verify($result, $this->password);
		} catch (Exception $e) {
			$this->SetError(__METHOD__, $e->GetMessage());
		}
		return $result;
	}

/**
* Summary. Genera una contraseña cifrada a partir de un texto en claro.
* @param str $cleartext La contraseña en claro
* @return str La contraseña cifrada/hash del texto en claro.
*/
	public function GeneratePassword($cleartext)
	{
		$result = md5($cleartext);
		if (!empty($this->fecha_alta)) {
			$salt = md5($this->fecha_alta);
			$jam = array(substr($salt, 0, 16), substr($salt, 16, 16));
			$result = md5($jam[0] . md5($cleartext) . $jam[1]);
		} else {
			EchoLog('Fecha de alta está vacía.');
		}
		if (version_compare(PHP_VERSION, '5.5.0', '>=')) {
			$pepper = $this->GetPepper();
			$password_peppered = hash_hmac("sha512", $result, $pepper);
			$result = password_hash($password_peppered, PASSWORD_BCRYPT);
		}
		return $result;
	}

/**
* Summary. Devuelve el contenido del archivo PEPPER.
* @return str
*/
	private function GetPepper()
	{
		$result = '';
		try {
			$path = DIR_BASE . '..' . DIRECTORY_SEPARATOR . $this->pepper_file;
			if (file_exists($path) and is_file($path)) {
				$result = file_get_contents(DIR_BASE . '..' . DIRECTORY_SEPARATOR . $this->pepper_file);
			} else {
				$result = 'Trabajando sin pepper';
			}
		} catch (Exception $e) {
			cLogging::Write(__METHOD__ . " " . $e->GetMessage());
		}
		return $result;
	}
} // Class cUsuario
?>