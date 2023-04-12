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
require_once(DIR_model."usuarios".DS."class.usuarios_sesiones.inc.php");
require_once(DIR_model."personas".DS."class.personas.inc.php");
require_once(DIR_model."personas".DS."class.personasData.inc.php");

const USUARIOS_CHECK_IP = true; // Verificar si la sesión pertenece a la misma IP.

const USUARIOS_NIVEL_INT = array('OWN'=>1,"ADM"=>2,"USR"=>3);

const USUARIOS_CAMPOS_USUARIOS = <<<END
`usuarios`.`id`,
`usuarios`.`persona_id`,
`usuarios`.`username`,
`usuarios`.`password`,
`usuarios`.`rol_id`,
`usuarios`.`nivel`,
`usuarios`.`estado`,
`usuarios`.`opciones`,
`usuarios`.`sys_fecha_alta`,
`usuarios`.`sys_fecha_modif`,
`usuarios`.`sys_usuario_id`
END;

const USUARIOS_CAMPOS_PERSONAS = <<<END
`personas`.`nombre`,
`personas`.`apellido`,
`personas`.`tipo_doc`,
`personas`.`nro_doc`,
`personas`.`fecha_nac`,
`personas`.`negocio_id`
END;

const USUARIOS_CAMPOS_EXTRAS = <<<END
`personas_data`.`tipo` AS `tipo_dato`,
`personas_data`.`dato`,
`personas_data`.`extras`
END;

class cUsuarios extends cModels {
	const tabla_usuarios = TBL_backend_usuarios;
	const tabla_persona = TBL_backend_personas;
	const tabla_persona_data = TBL_backend_personas_data;
	const tabla_negocio = TBL_negocios;
	
	private $session = null;
	public $tsession = 3600;
	public $pepper_file = 'pepper.txt';
	public $LogDebug = false; // Escribir información de debug en el log.
	public $nivel_int = 0;
	public $id = null;
	public $esta_logueado = false;
	public $persona;
	public $personaData;
	public $sys_usuario;

	protected $PasswordMaxLenght = 256;
	
	public function __construct() {
		parent::__construct();
		$this->LogDebug = DEVELOPE;
		$this->mainTable = self::tabla_usuarios;
		$this->qMainTable = SQLQuote(self::tabla_usuarios);
		$this->qPersonasTable = SQLQuote(self::tabla_persona);
		$this->qPersonasDataTable = SQLQuote(self::tabla_persona_data);
		$this->qNegocioTable = SQLQuote(self::tabla_negocio);
		$this->ResetInstance();
		$this->actual_file = __FILE__;
		if (defined("PEPPER_FILE")) {
			$this->pepper_file = PEPPER_FILE;
		}
		$this->session = new cUsrSession;
	}


/**
* Summary. Obtiene el nombre completo del usuario
* @param int $usuario_id El id del usuario buscado.
* @return object/null
*/
	public function GetCompleteName(int $usuario_id = null) :? string{
		if (empty($usuario_id)) { throw new Exception(__LINE__ ." ID debe ser un número."); }
		$this->sql = "SELECT CONCAT(`personas`.`nombre`, ' ', `personas`.`apellido`) AS nombre_completo FROM ".$this->qMainTable." AS `usuarios`, ".$this->qPersonasTable." AS `personas` WHERE `usuarios`.`id`=$usuario_id AND `personas`.`id` = `usuarios`.`persona_id` LIMIT 1;";
		return $this->FirstQuery()->nombre_completo??null;
	}

/**
* Summary. Obtiene los datos de un usuario. Hay que establecer la propiedad ->id para que lea ese usuario.
* @return object/null
*/
	public function Get():?object {
		if (SecureInt($this->id,null) == null) { throw new Exception(__LINE__ ." ID debe ser un número."); }
		$this->sql = "SELECT ".USUARIOS_CAMPOS_USUARIOS.", ".USUARIOS_CAMPOS_PERSONAS." FROM ".$this->qMainTable." AS `usuarios` INNER JOIN ".$this->qPersonasTable." AS `personas` ON `personas`.`id`=`usuarios`.`persona_id` WHERE `usuarios`.`id` = ".$this->id;
		return $this->FirstQuery();
	}
/**
* Summary. Determina si existe un usuario según su username
* @param string $username El nombre de usuario buscado (may/min indistinto).
* @return object/null
*/
	public function GetByUsername($username) {
		if (empty($username)) { throw new Exception("No puedo buscar a un usuario anónimo."); }
		$username = substr($username,0,32);
		$username = $this->RealEscape($username);
		$this->sql = "SELECT ".USUARIOS_CAMPOS_USUARIOS.", ".USUARIOS_CAMPOS_PERSONAS." FROM ".$this->qMainTable." AS `usuarios` INNER JOIN ".$this->qPersonasTable." AS `personas` ON `personas`.`id`=`usuarios`.`persona_id` INNER JOIN ".$this->qNegocioTable." AS `negocio` ON `negocio`.`id`= `personas`.`negocio_id` WHERE LOWER(`usuarios`.`username`) = LOWER('".$username."') AND `usuarios`.`estado` != 'ELI' AND `negocio`.`estado` LIKE 'HAB' LIMIT 1;";
		return $this->FirstQuery();
	}
/**
* Summary. Determina si existe un usuario según su email
* @param string $email La dirección de correo electrónico buscada (may/min indistinto).
* @return object/null
*/
	public function GetByEmail($email) {
		if (empty($email)) { throw new Exception("No puedo buscar a un usuario sin email."); }
		$email = mb_substr($email,0,128);
		$email = $this->RealEscape($email);
		$this->sql = "SELECT ".USUARIOS_CAMPOS_USUARIOS.", ".USUARIOS_CAMPOS_PERSONAS.", ".USUARIOS_CAMPOS_EXTRAS." FROM ".$this->qPersonasDataTable." AS `personas_data`,	".$this->qPersonasTable." AS `personas`, ".$this->qMainTable." AS `usuarios` WHERE `personas_data`.`tipo` = 'EMAIL' AND LOWER(`personas_data`.`dato`) = LOWER('$email') AND `personas`.`id` = `personas_data`.`persona_id` AND `usuarios`.`persona_id` = `personas`.`id` AND `usuarios`.`estado` != 'ELI' LIMIT 1;";
		return $this->FirstQuery();
	}

/**
* Summary. Determina si existe un usuario según su username
* @param string $username El nombre de usuario buscado (may/min indistinto).
* @return object/null
*/
public function GetByPersonaId($persona) {
	if (empty($persona)) { throw new Exception("No puedo buscar a un usuario anónimo."); }
	$persona = SecureInt($persona,0,32);
	$this->sql = "SELECT ".USUARIOS_CAMPOS_USUARIOS.", ".USUARIOS_CAMPOS_PERSONAS." FROM ".$this->qMainTable." AS `usuarios` INNER JOIN ".$this->qPersonasTable." AS `personas` ON `personas`.`id`=`usuarios`.`persona_id` INNER JOIN ".$this->qNegocioTable." AS `negocio` ON `negocio`.`id`= `personas`.`negocio_id` WHERE `usuarios`.`persona_id` = $persona AND `usuarios`.`estado` != 'ELI' AND `negocio`.`estado` LIKE 'HAB' LIMIT 1;";
	return $this->FirstQuery();
}

/**
* Summary. Comprueba si el usuario actual tiene la sesión activa.
* @return bool.
*/
	public function CheckLogin() {
		$result = false;
		$this->esta_logueado = false;
		try {
			if (!$this->session->Exists()) { $this->DoLog(__LINE__ .' No hay sesión activa.'); return false; }
			
			$ua = $_SERVER['HTTP_USER_AGENT']??$_SERVER['USERDOMAIN']; // User Agent
			if (mb_strtolower($ua) != mb_strtolower($this->session->navegador)) { $this->DoLog(__LINE__ .' El User Agent no coincide'); return false; } // El User Agent no coincide
			if (USUARIOS_CHECK_IP) {
				if (GetIP() != $this->session->ip) { $this->DoLog(__LINE__ .' IP no coincide'); return false; }
			}
			$tsession = (isset($this->opciones) and isset($this->opciones->tsession))?$this->opciones->tsession:3600;
			if ((date('U') - $this->session->idle) > $tsession) { $this->DoLog(__LINE__ .' Tiempo de sesión vencido.'); return false; }
			$result = true;
			$this->esta_logueado = true;
			$this->session->sessionTime = $tsession;
			$this->session->Renew();
			$this->id = $this->session->usuario_id;
			$this->Get();
		} catch (Exception $e) {
			$this->SetError($e);
		}
		return $result;
	}
/**
* Summary. Ejecuta el proceso de login del usuario.
*/
	public function Login() {
		if (empty($this->id)) { throw new Exception('No hay usuario actual, usar ->Get'); }
		return $this->session->Login($this->id);
	}

/**
* Summary. Ejecuta el proceso de logout del usuario.
*/
	public function Logout() {
		return $this->session->Logout($this->id);
	}


/**
* Summary. Establece la nueva contraseña a partir de $password
* @param str $thePassword La contraseña en claro.
* @return bool
*/
	public function SetNewPassword($thePassword) {
		$result = false;
		try {
			if (empty($thePassword)) { throw new Exception("No se puede establecer una contraseña vacía."); }
			if (strlen($thePassword) > $this->PasswordMaxLenght) { throw new Exception("La nueva contraseña es demasiado larga."); }
			$this->password = $this->GeneratePassword($thePassword);
			$result = $this->Set();
		} catch (Exception $e) {
			$this->SetError($e);
		}
		return $result;
	}
/**
* Summary. Determina si $password es la contraseña correcta para el usuario actual.
* @param str $cleartext La contraseña en claro que se quiere verificar.
* @return bool
*/
	public function ValidPass($cleartext) {
		if (empty($this->password)) { throw new Exception('El campo password del usuario está vacío.'); }
		if ($result = $this->GetHash($cleartext)) {
			return password_verify($result, $this->password);
		}
		return false;
	}

/**
* Summary. Genera una contraseña cifrada a partir de un texto en claro.
* @param str $cleartext La contraseña en claro
* @return str La contraseña cifrada/hash del texto en claro o null
*/
	public function GeneratePassword(string $cleartext):?string {
		if ($result = $this->GetHash($cleartext)) {
			return password_hash($result, PASSWORD_BCRYPT);
		}
		return null;
	}
/**
* Summary. Devuelve el hash de una cadena de texto, supuestamente la contraseña en claro.
* @param string $cleartext La contraseña a hashear.
* @return string/null el hash resultante.
*/
	private function GetHash(string $cleartext):?string {
		$result = null;
		try {
			if (empty($this->id)) { throw new Exception('No hay usuario activo, usar ->Get()'); }
			$fecha_alta = $this->sys_fecha_alta??date('Y-m-d H:i:s');
			$pepper = $this->GetPepper();
			$result = md5($cleartext);
			$salt = md5($fecha_alta);
			$jam = array(substr($salt, 0, 16), substr($salt, 16, 16));
			$result = md5($jam[0] . md5($cleartext) . $jam[1]);
			$result = hash_hmac("sha512", $result, $pepper);
		} catch(Exception $e) {
			$this->SetError($e);
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

/**
* Summary. Escribe en en log un mensaje para debug.
* @param string $msg El mensaje a escribir.
*/
	private function DoLog(string $msg = '') {
		if (!$this->LogDebug) { return; }
		if (!empty($this->username)) {
			$msg = $this->username." ($this->id) ".$msg;
		}
		$trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS,2);
		if (isset($trace[1])) {
			$msg = $trace[1]['function'].": ".$msg;
		}
		cLogging::Write(__FILE__ ." ".$msg, LGEV_DEBUG);
	}

/**
 * Summary. Gestiona el alto de una persona, usuario y su informacion extra.
 * @param array $data Tiene que contener la informacion de "persona,emails,telefono,direccion,usuario"
 */
	public function CreateUsuario(array $data){
		$result = false;
		try{
			if (!CanUseArray($data)){throw new Exception(__LINE__." EL array se encuntra vacío.");}

			$this->persona = new cPersonas($this->sys_usuario);
			if (!$idp = $this->persona->Create($data['persona'])){
				throw new Exception(__LINE__." No fue posible crear a la persona.");
			}
			
			$this->personaData = new cPersonasData($idp);
			if (!$this->personaData->CreateData($data['emails'],"EMAIL")){
				throw new Exception(__LINE__." No fue posible crear los datos de EMAIL para la persona: $idp.");
			}
			if (!$this->personaData->CreateData($data['telefono'],"TEL")){
				throw new Exception(__LINE__." No fue posible crear los datos de TEL para la persona: $idp.");
			}
			if (!$this->personaData->CreateData($data['direccion'],"DIREC")){
				throw new Exception(__LINE__." No fue posible crear los datos de DIREC para la persona: $idp.");
			}
			
			$usuario = $data['usuario'];
			$usuario['persona_id'] = $idp;
			$usuario['opciones'] = json_encode(["rpp"=> 25,"logout"=> "perfil","tsession"=> 3600]);
			
			if (!$new_user = $this->NewRecord($usuario)){
				throw new Exception("No fue posible crear un usuario para la persona: $idp");
			}
			$this->id = $new_user;
			$this->Get();
			$this->SetNewPassword($usuario['password']);

			$result = true;

		}catch(Exception $e){
			$this->SetError($e);
		}
		return $result;
	}

/**
 * Summary. Gestiona el alto de una persona, usuario y su informacion extra.
 * @param array $data Tiene que contener la informacion de "persona,emails,telefono,direccion,usuario".
 * @param int $id El id de la persona a actualizar.
*/
public function EditarUsuario(array $data,int $id){
	$result = false;
	try{
		if (!CanUseArray($data)){throw new Exception(__LINE__." EL array se encuntra vacío.");}
		if (!SecureInt($id)){throw new Exception(__LINE__." EL id esta vacío o no es numerico.");}

		$this->persona = new cPersonas();
		$this->persona->people_id = $id;
		if (!$this->persona->Edit($data['persona'])){
			throw new Exception(__LINE__." No fue posible Editar a la persona.");
		}
		
		$this->personaData = new cPersonasData($id);
		$this->personaData->EliminarAllData();
		if (!$this->personaData->CreateData($data['emails'],"EMAIL")){
			throw new Exception(__LINE__." No fue posible editar los datos de EMAIL para la persona: $id.");
		}
		if (!$this->personaData->CreateData($data['telefono'],"TEL")){
			throw new Exception(__LINE__." No fue posible editar los datos de TEL para la persona: $id.");
		}
		if (!$this->personaData->CreateData($data['direccion'],"DIREC")){
			throw new Exception(__LINE__." No fue posible editar los datos de DIREC para la persona: $id.");
		}
		
		$usuario = $data['usuario'];
		
		if (!$this->Update($this->mainTable,$usuario," `persona_id`= $id")){
			throw new Exception("No fue posible actualizar al usuario para la persona: $id");
		}

		if (isset($usuario['password']) AND !empty($usuario['password'])){
			$this->GetByPersonaId($id);
			$this->SetNewPassword($usuario['password']);
		}

		$result = true;

	}catch(Exception $e){
		$this->SetError($e);
	}
	return $result;
}
} // Class cUsuario
?>