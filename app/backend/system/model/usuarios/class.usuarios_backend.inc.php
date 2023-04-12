<?php
/*
	Clase cUsrBackend.
	Esto maneja los usuarios del backend. Está basado en la clase cUsuarios.
	Created: 2018-10-22
	Author: DriverOp.
	Update: 2018-11-02
	Desc: Agregado método para recuperar datos de un usuario arbitrario.
	Update: 2019-11-16
	Desc: Agregado métodos Everything, Menu y ParseItemMenu relacionado con el menú del usuario.
	Update: 2021-01-18
	Desc: Agregado método GetLastLogin().
	Update: 2021-01-29
	Desc: Hacer pública la propiedad session_name para que se pueda implementa el impersonate.
*/

require_once(DIR_model . "usuarios".DS."class.usuarios.inc.php");
require_once(DIR_model . "usuarios".DS."class.usuarios_menu.inc.php");
require_once(DIR_includes . "class.fechas.inc.php");
require_once(DIR_model."usuarios".DS."class.usrmsgs.inc.php");
require_once(DIR_model."permisos".DS."class.user.inc.php");
require_once(DIR_model."permisos".DS."class.template.inc.php");
//require_once(DIR_model . "class.negocios.inc.php");

class cUsrBackend extends cUsuarios
{
	protected $tabla_usuarios = TBL_backend_usuarios;
	protected $tabla_sesiones = TBL_backend_sesiones;
	protected $salt = '';
	protected $SecretKey = 6005;
	protected $PasswordMaxLenght = 32;

	private $tabla_msgasuntosusr = "config_motivos_usuarios";
	private $tabla_reloads = 'backend_reloads';
	private $tabla_asuntos_mensajes = 'config_motivos';
	private $tabla_contenidos = TBL_contenido;
	
	public $active_content = null; // El ID del contenido que está activo en este momento.
	public $lg_menues = null; // Los contenidos traducidos, necesario en ParseItemMenu
	public $iniciales = '';	//Iniciales de nombre y apellido
	public $puede_ver = true; //Flags que verifica si tiene permiso para ver
	public $puede_crear = true; //Flags que verifica si tiene permiso para crear
	public $puede_modificar = true; //Flags que verifica si tiene permiso para modificar
	public $puede_eliminar = true; //Flags que verifica si tiene permiso para eliminar

	public function __construct()
	{
		parent::__construct();
		$this->tabla_principal = $this->tabla_usuarios;
		$this->actual_file = __FILE__;
	}
	
	public function GetIdsAreasUsuario()
	{
		$result = array();
		if ($this->existe) {
			try {
				if ($this->nivel == 'USR') {
					$sql = "SELECT " . SQLQuote($this->tabla_msgasuntosusr) . ".`motivo_id` FROM " . SQLQuote($this->tabla_msgasuntosusr) . ", " . SQLQuote($this->tabla_asuntos_mensajes) . " WHERE " . SQLQuote($this->tabla_asuntos_mensajes) . ".`id` = " . SQLQuote($this->tabla_msgasuntosusr) . ".`motivo_id` AND " . SQLQuote($this->tabla_msgasuntosusr) . ".`usuario_id` = " . $this->id . " AND " . SQLQuote($this->tabla_msgasuntosusr) . ".`estado` = 'HAB' AND " . SQLQuote($this->tabla_asuntos_mensajes) . ".`habilitado` = 'HAB' ORDER BY " . SQLQuote($this->tabla_asuntos_mensajes) . ".`id`";
				} else {
					$sql = "SELECT " . SQLQuote($this->tabla_asuntos_mensajes) . ".`id` AS `motivo_id` FROM " . SQLQuote($this->tabla_asuntos_mensajes) . " WHERE " . SQLQuote($this->tabla_asuntos_mensajes) . ".`habilitado`= 'HAB' ORDER BY " . SQLQuote($this->tabla_asuntos_mensajes) . ".`id`";
				}
				$this->Query($sql);
				if ($this->numrows > 0) {
					while ($fila = $this->Next()) {
						$result[] = $fila['motivo_id'];
					} // while
				}
			} catch (Exception $e) {
				$this->SetError($e);
			}
		}
		return $result;
	}

	
/**
* Summary. Devuelve en un array la lista de contenidos permitidos para el usuario actual para armarle el menú.
*/
	public function Menu() {
		$menu = new cUsrMenu();
		try {
		$menu->SetUser($this->id);
		return $menu->Menu();
		} finally {
			$menu = null;
		}
	}

/**
* Summary. Dibuja el menu del usuario.
*/
	public function RenderMenu(array $lista = null) {
		$result = null;
		$menu = new cUsrMenu();
		try {
			$menu->SetUser($this->id);
			if (is_array($lista) and count($lista)) {
				foreach($lista as $id) {
					$menu->SetActiveContent($id);
				}
			}
			$result = $menu->RenderMenu();
		} finally {
			$menu = null;
		}
		return $result;
	}

	public function GetSubContenido($id){
		$result = array();
		if ($this->existe) {
			try{
				$sql = "SELECT * FROM ".SQLQuote($this->tabla_contenidos)." WHERE `parent_id` = ".$id." AND `es_default` = 1 ORDER BY `orden` ASC LIMIT 1";
				
				$result = $this->FirstQuery($sql);

			}catch (Exception $e) {
				$this->SetError($e);
			}
		}
		return $result;
	}

	/*
	Marca el tiempo (momento) en que se visualizó un listado.
*/
	public function SetReload($id_listado)
	{
		$sql = "SELECT * FROM " . SQLQuote($this->tabla_reloads) . " WHERE `usuario_id` = " . $this->id . " AND `listado_id` = " . $id_listado;
		try {
			$this->Query($sql);
			$reg = array('momento' => Date('Y-m-d H:i:s'));
			if ($this->numrows > 0) {
				$this->Update($this->tabla_reloads, $reg, "`usuario_id` = " . $this->id . " AND `listado_id` = " . $id_listado);
			} else {
				$reg['usuario_id'] = $this->id;
				$reg['listado_id'] = $id_listado;
				$this->Insert($this->tabla_reloads, $reg);
			}
		} catch (Exception $e) {
			$this->SetError($e);
		}
	} // SetReload
	/*
	Lee el momento en que se visualizó un listado.
*/
	public function GetReload($id_listado)
	{
		$result = null;
		$sql = "SELECT * FROM " . SQLQuote($this->tabla_reloads) . " WHERE `usuario_id` = " . $this->id . " AND `listado_id` = " . $id_listado;
		try {
			$this->Query($sql);
			$result = $this->First();
		} catch (Exception $e) {
			$this->SetError($e);
		}
		return $result;
	}
/**
* Summary. Devuelve los datos del perfil del usuario si lo tiene.
* @return array of objects
*/
	public function GetPerfil()
	{
		if (!$this->existe) { return false; }
		$this->perfil = array();
		try {
			if (!empty($this->perfil_id)) {
				$sql = "SELECT * FROM " . SQLQuote($this->tabla_perfiles) . " WHERE 1=1 ";
				if (!empty($this->perfil_id)) {
					if (is_array($this->perfil_id)) {
						$sql .= "AND `id` IN (" . implode(',',$this->perfil_id). ")";
					} else {
						$sql .= "AND `id` = '".$this->perfil_id."' ";
					}
				}
				$this->Query($sql);
				if ($fila = $this->First()) {
					do {
						$this->perfil[$fila['id']] = json_decode(json_encode($fila));
					} while($fila = $this->Next());
				}
			}
		} catch (Exception $e) {
			$this->SetError($e);
		}
		return $this->perfil;
	}
/**
* Summary. Determina si el usuario tiene el rol indicado.
* @param str $perfil El perfil buscado.
* @return bool.
*/
	public function IsPerfil($perfil = null) {
		if (!$this->existe) { return false; }
		if ($this->IsOwner()) { return true; }
		$result = false;
		if (empty($this->perfil)) {
			$this->GetPerfil();
		}
		if (CanUseArray($this->perfil)) {
			$result = false;
			$perfil = strtoupper($perfil);
			foreach($this->perfil as $value) {
				if (strtoupper($value->alias) == $perfil) {
					$result = true;
					break;
				}
			}
		} else {
			$result = true; // Tiene todos los perfiles.
		}
		return $result;
	}
	/**
	 * Summary. Determina si el usuario es un administrador. Si el usuario es owner, da true.
	 * @return boolean true cuando sí, false cuando no.
	 */
	public function IsAdmin()
	{
		$result = false;
		if ($this->existe) {
			try {
				$result = ($this->nivel == 'ADM' or $this->nivel == 'OWN');
			} catch (Exception $e) {
				$this->SetError($e);
			}
		}
		return $result;
	}
	/**
	 * Summary. Determina si el usuario es un usuario
	 * @return boolean true cuando sí, false cuando no.
	 */
	public function IsUser()
	{
		$result = false;
		if ($this->existe) {
			try {
				$result = $this->nivel == 'USR';
			} catch (Exception $e) {
				$this->SetError($e);
			}
		}
		return $result;
	}

	/**
	 * Summary. Determina si el usuario es un owner.
	 * @return boolean true cuando sí, false cuando no.
	 */
	public function IsOwner()
	{
		$result = false;
		if ($this->existe) {
			try {
				$result = $this->nivel == 'OWN';
			} catch (Exception $e) {
				$this->SetError($e);
			}
		}
		return $result;
	}

	/**
	 * Obtiene los permisos del usuario actual para un determinado contenido.
	 * Las propiedades del objeto usuario que determinan los permisos son:
	 * ─ puede_ver
	 * ─ puede_crear
	 * ─ puede_modificar
	 * ─ puede_eliminar
	 * @param int $contenidoID ─ Busca por el ID del contenido, sino toma el objeto_contenido
	 * global del contenido actual
	 * @return void 
	 */

	public function GetPermisos(int $contenidoID = null) {
		
		$user = new cUser();
		$template = new cTemplate();

		if($contenidoID){
			$sql = "SELECT * FROM ".SQLQuote($this->tabla_contenidos)." WHERE `id` = $contenidoID";
			$objeto_contenido = $this->FirstQuery($sql);
		} else {
			global $objeto_contenido;
		}

		if(!$this->IsOwner()){
			$plantillaPermisos = [];
			$parentId = $objeto_contenido->parent_id > 0 ? $objeto_contenido->parent_id : null;
			if ($user->GetTemplateByUser($this->id)) {
				$plantillaPermisos = $user->plantilla;
			//En última instancia le seteamos una plantilla por defecto
			} else {
				$plantillaPermisos = $template->GetTemplate();
			}
			
			if(!empty($plantillaPermisos)){
				//Pregunto por el contenido padre
				if( isset($parentId) ){
					if(!$template->PermitExists('r', $plantillaPermisos, $parentId) ){
						$this->puede_ver = false;
						return;
					}
					if(!$template->PermitExists('c', $plantillaPermisos, $parentId) ){
						$this->puede_crear = false;
					}
					if(!$template->PermitExists('u', $plantillaPermisos, $parentId) ){
						$this->puede_modificar = false;
					}
					if(!$template->PermitExists('d', $plantillaPermisos, $parentId) ){
						$this->puede_eliminar = false;
					}
				}
				
				//Pregunto por el contenido hijo 
				if(!$template->PermitExists('r', $plantillaPermisos, $objeto_contenido->id) ){
					$this->puede_ver = false;
					return;
				}
				if($this->puede_crear){
					if(!$template->PermitExists('c', $plantillaPermisos, $objeto_contenido->id)){
						$this->puede_crear = false;
					}
				}
				if($this->puede_modificar){
					if(!$template->PermitExists('u', $plantillaPermisos, $objeto_contenido->id)){
						$this->puede_modificar = false;
					}
				}
				if($this->puede_eliminar){
					if(!$template->PermitExists('d', $plantillaPermisos, $objeto_contenido->id) ){
						$this->puede_eliminar = true;
					}
				}
			}
		}
	}

	/**
	* Summary. Determina si el usuario está activo.
	* @return boolean true cuando sí, false cuando no.
	*/
	public function IsActive() {
		$result = false;
		if ($this->existe) {
			try {
				$result = $this->estado == 'HAB';
			} catch (Exception $e) {
				$this->SetError($e);
			}
		}
		return $result;
	}
	/**
	* Summary. Determina si el usuario está desactivado (que esté borrado, también significa que está desactivado).
	* @return boolean true cuando sí, false cuando no.
	*/
	public function IsInactive() {
		$result = false;
		if ($this->existe) {
			try {
				$result = in_array($this->estado,['DES','ELI']);
			} catch (Exception $e) {
				$this->SetError($e);
			}
		}
		return $result;
	}
	/**
	* Summary. Determina si el usuario está eliminado.
	* @return boolean true cuando sí, false cuando no.
	*/
	public function IsDeleted() {
		$result = false;
		if ($this->existe) {
			try {
				$result = $this->estado == 'ELI';
			} catch (Exception $e) {
				$this->SetError($e);
			}
		}
		return $result;
	}

/**
* Devuelve los datos del negocio que el usuario tiene configurado actualmente.
* @return object.
*/
	public function GetNegocio() {
		$result = new stdClass();
		$result->nombre = '('.ReturnLang('ninguno','ninguno').')';
		try {
			if (isset($this->negocio_id) and ($this->negocio_id > 0)) {
				$negocio = new cNegocio();
				if ($negocio = $negocio->Get($this->negocio_id)) {
					$result = $negocio; 
				}
			}
		} catch (Exception $e) {
			$this->SetError($e);
		}
		return $result;
	}
/**
* Establece el negocio actual del usuario al que se pasa como parámetro. ¡No valida que el negocio exista!.
* @param int $negocio_id El ID del negocio.
*/
	public function SetNegocio($negocio_id) {
		$result = false;
		try {
			if (SecureInt($negocio_id) == null) { throw new Exception(__LINE__." Negocio ID debe ser un número."); }
			if (isset($this->negocio_id) and ($this->negocio_id != $negocio_id)) {
				$this->Save(['negocio_id'=>$negocio_id]);
			}
			$result = true;
		} catch (Exception $e) {
			$this->SetError($e);
		}
		return $result;
	}
/**
* Summary. Devuelve la última sesión del usuario actual.
*/
	public function GetLastLogin()
	{
		$result = null;
		if ($this->existe) {
			try {
				$sql = "SELECT * FROM " . SQLQuote(TBL_backend_sesiones) . " WHERE `usuario_id` = " . $this->id . " ORDER BY `fecha_hora` DESC LIMIT 1;";
				$this->Query($sql);
				if ($fila = $this->First()) {
					$result = new StdClass();
					foreach ($fila as $key => $value) {
						$result->$key = $value;
					}
					if (!empty($result->fecha_hora)) {
						$result->fecha_hora_txt = cFechas::SQLDate2Str($result->fecha_hora);
						$result->fecha_hora_txt_short = cFechas::SQLDate2Str($result->fecha_hora, CDATE_SHORT);
						$result->hace = cFechas::TiempoTranscurrido($result->fecha_hora, Date('Y-m-d H:i:s'));
					}
				}
			} catch (Exception $e) {
				$this->SetError($e);
			}
		}
		return $result;
	}
	public function GetByTel($tel)
	{ // Qué usuario tiene el número de telefono indicado?
		$result = false;
		$this->existe = false;
		$this->raw = array();
		try {
			$tel = $this->RealEscape(mb_substr($tel, 0, 25));
			$sql = "SELECT * FROM `" . $this->tabla_usuarios . "` WHERE LOWER(`tel`) = LOWER('" . $tel . "');";
			$this->Query($sql);
			if ($this->error) {
				throw new Exception(__LINE__ . " DBErr: " . $this->errmsg);
			}
			if ($result = $this->first()) {
				$this->raw = $result;
				$this->existe = true;
				$this->SetData($result);
				$result = $this;
			}
		} catch (Exception $e) {
			$this->SetError($e);
		}
		return $result;
	}
	public function GetByEmail($email)
	{ // Util para recuperar la contraseña de un usuario
		$result = false;
		$this->existe = false;
		$this->raw = array();
		try {
			$email = $this->RealEscape(mb_substr($email, 0, 75));
			$sql = "SELECT * FROM `" . $this->tabla_usuarios . "` WHERE LOWER(`email`) = LOWER('" . $email . "');";
			$this->Query($sql);
			if ($result = $this->First()) {
				$this->raw = $result;
				$this->existe = true;
				$this->SetData($result);
				$result = $this;
			}
		} catch (Exception $e) {
			$this->SetError($e);
		}
		return $result;
	} // GetByEmail
	public function SetJumble($len = 32)
	{
		$result = false;
		try {
			if (!$this->existe) {
				throw new Exception(__LINE__ . ' Tiene que haber un usuario activo para continuar esta operación.');
			}
			$jumble = md5($this->username . $this->email . date('YmdHis'));
			$jumble = substr($jumble, 0, $len);
			$this->Delete($this->tabla_recovery, "`estado` = 'ELI' OR `usuario_id` = " . $this->id);
			if ($this->error) {
				throw new Exception(__LINE__ . ' DBErr: ' . $this->errmsg);
			}
			$this->Insert(
				$this->tabla_recovery,
				array(
					'usuario_id' => $this->id,
					'email' => $this->email,
					'code' => $jumble,
					'fecha' => cFechas::Ahora()
				)
			);
			if ($this->error) {
				throw new Exception(__LINE__ . ' DBErr: ' . $this->errmsg);
			}
			$result = $jumble;
			//throw new Exception('');
		} catch (Exception $e) {
			$this->SetError($e);
		}
		return $result;
	}

	public function GetByJumble($jumble)
	{ // Qué usuario tiene este jumble?, el jumble es válido?.
		$result = false;
		$this->existe = false;
		$this->raw = array();
		try {
			$this->Delete($this->tabla_recovery, "`estado` = 'ELI'");
			if ($this->error) {
				throw new Exception(__LINE__ . ' DBErr: ' . $this->errmsg);
			}
			$email = $this->RealEscape(mb_substr($jumble, 0, 32));
			$sql = "SELECT `usuario_id` FROM `" . $this->tabla_recovery . "` WHERE LOWER(`code`) = LOWER('" . $jumble . "');";
			$this->Query($sql);
			if ($this->error) {
				throw new Exception(__LINE__ . " DBErr: " . $this->errmsg);
			}
			if ($result = $this->First()) {
				$this->Get($result['usuario_id']);
				$result = true;
			}
		} catch (Exception $e) {
			$this->SetError($e);
		}
		return $result;
	} // GetByJumble


	public function Get():?object{
		$result = parent::Get();
		$explodes = array();

		$nombres = explode(' ',$result->nombre);
		$apellidos = explode(' ',$result->apellido);
		$explodes = array_merge($explodes,$nombres,$apellidos);

		foreach ($explodes as $value) {
			$this->iniciales .= mb_substr($value,0,1);
		}
		return $result;
	}

	/**
	 * Summary. Busca toda la informacion de un usuario segun su id
	 * @param int $id
	 * @return bool/obj $result
	*/
	public function GetUsuario($id){
		$result = false;
		try{
			$this->sql = "SELECT `user`.`username`, `user`.`nivel`, `user`.`estado`, `user`.`opciones`,`people`.* FROM ".SQLQuote(TBL_backend_usuarios)." AS `user` INNER JOIN ".SQLQuote(TBL_backend_personas)." AS `people` ON `people`.`id`=`user`.`persona_id` WHERE `people`.`id`=$id";
			$result = $this->FirstQuery();
		}catch(Exception $e){
			$this->SetError($e);
		}
		return $result;
	}

} // end of class
