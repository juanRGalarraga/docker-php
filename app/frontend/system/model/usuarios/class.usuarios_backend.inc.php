<?php
/*
	Clase cUsrBackend.
	Esto maneja los usuarios del backend. Está basado en la clase cUsuarios.
	Created: 2018-10-22
	Author: DriverOp.
	Modif: 2018-11-02
	Desc: Agregado método para recuperar datos de un usuario arbitrario.
	Modif: 2019-11-16
	Desc: Agregado métodos Everything, Menu y ParseItemMenu relacionado con el menú del usuario.
	Modif: 2021-01-18
	Desc: Agregado método GetLastLogin().
	Modif: 2021-01-29
	Desc: Hacer pública la propiedad session_name para que se pueda implementa el impersonate.
*/

require_once(DIR_model . "usuarios".DS."class.usuarios.inc.php");
require_once(DIR_includes . "class.fechas.inc.php");
require_once(DIR_model."usuarios".DS."class.usrmsgs.inc.php");
//require_once(DIR_model . "class.negocios.inc.php");

class cUsrBackend extends cUsuarios
{
	protected $tabla_usuarios = TBL_backend_usuarios;
	protected $tabla_sesiones = TBL_backend_sesiones;
	protected $tabla_perfiles = TBL_backend_perfiles;
	protected $tabla_recovery = TBL_backend_recovery;
	protected $salt = '';
	protected $SecretKey = 6005;
	public $session_name = 'USR_BACKEND';
	protected $PasswordMaxLenght = 32;
	private $tabla_msgasuntosusr = "config_motivos_usuarios";
	private $tabla_permitidos = TBL_backend_permisos;
	private $tabla_reloads = 'backend_reloads';
	private $tabla_asuntos_mensajes = 'config_motivos';
	private $tabla_contenidos = TBL_contenido;
	private $tabla_perfiles_contenidos = TBL_backend_perfiles_contenidos;
	public $active_content = null; // El ID del contenido que está activo en este momento.
	public $lg_menues = null; // Los contenidos traducidos, necesario en ParseItemMenu

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
				$this->SetError(__METHOD__, $e->GetMessage());
			}
		}
		return $result;
	}
	/*
	Devuelve en un array la lista de contenidos permitidos para el usuario actual para armarle el menú.
*/
	public function Menu($parent_id = 0, $url = BASE_URL)
	{
		$result = array();
		if ($this->existe) {
			if ($this->nivel == 'OWN') {
				return $this->Everything();
			}
			try {
				$sql = "SELECT " . SQLQuote($this->tabla_contenidos) . ".* FROM " . SQLQuote($this->tabla_contenidos) . " WHERE 1=1 AND " . SQLQuote($this->tabla_contenidos) . ".`parent_id` = " . $parent_id . " AND " . SQLQuote($this->tabla_contenidos) . ".`esta_protegido` = 1 AND " . SQLQuote($this->tabla_contenidos) . ".`estado` = 'HAB' AND " . SQLQuote($this->tabla_contenidos) . ".`en_menu` = 1 ORDER BY " . SQLQuote($this->tabla_contenidos) . ".`id` ASC, " . SQLQuote($this->tabla_contenidos) . ".`orden` ASC;";
				//EchoLog($sql);
				$res = $this->Query($sql, true);
				if ($this->cantidad > 0) {
					while ($fila = $this->Next($res)) {
						$result[$fila['id']] = $this->ParseItemMenu($fila);
						$result[$fila['id']]['url'] = EnsureTrailingURISlash($url . $fila['alias']);
					}
				}
				if (CanUseArray($result)) {
					foreach ($result as $key => $value) {
						$result[$key]['childs'] = $this->Menu($value['id'], $result[$key]['url']);
					}
				}
			} catch (Exception $e) {
				$this->SetError(__METHOD__, $e->GetMessage());
			}
		}
		$this->FindActive($result);
		$this->Menu_Usuario = $result;
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
			$this->SetError(__METHOD__, $e->GetMessage());
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
			$this->SetError(__METHOD__, $e->GetMessage());
		}
		return $result;
	}
	/*
	Devuelve TODOS los contenidos para menú.
*/
	private function Everything($parent_id = 0, $url = BASE_URL)
	{
		$result = array();
		try {
			$sql = "SELECT * FROM " . SQLQuote($this->tabla_contenidos) . " WHERE 1=1 AND `parent_id` = " . $parent_id . " AND `esta_protegido` = 1 AND `estado` = 'HAB'  AND `en_menu` = 1 ORDER BY `orden`";
			$res = $this->Query($sql, true);
			if ($this->cantidad > 0) {
				while ($fila = $this->Next($res)) {
					$result[$fila['id']] = $this->ParseItemMenu($fila);
					$result[$fila['id']]['url'] = EnsureTrailingURISlash($url . $fila['alias']);
				}
			}
			if (CanUseArray($result)) {
				foreach ($result as $key => $value) {
					$result[$key]['childs'] = $this->Everything($value['id'], $result[$key]['url']);
				}
			}
		} catch (Exception $e) {
			$this->SetError(__METHOD__, $e->GetMessage());
		}
		$this->Menu_Usuario = $result;
		$this->FindActive($result);
		return $result;
	}
	/*
	Esto uniformiza los items de menú para que todos queden con las mismas propiedades.
*/
	private function ParseItemMenu($item)
	{
		$metadata = json_decode($item['metadata']);
		$item['active'] = ($this->active_content == $item['id']);
		//$item['menutag'] = (isset($metadata->menutag)) ? $metadata->menutag : $item['nombre'];
		//var_dump($this->lg_contenidos);
		$item['menutag'] = ReturnLang((isset($metadata->menutag))?$metadata->menutag:$item['nombre']);
		$item['icon_class'] = 'fa-dashboard';
		$item['icon_class'] = (isset($metadata->icon_class)) ? $metadata->icon_class : $item['icon_class'];
		$item['icon_collection'] = 'fa';
		$item['icon_collection'] = (isset($metadata->icon_collection)) ? $metadata->icon_collection : $item['icon_collection'];
		$item['tooltip'] = (isset($metadata->tooltip)) ? $metadata->tooltip : $item['nombre'];
		//echo "<pre>"; print_r($item); echo "</pre>";
		return $item;
	}
	/*
	Pone la rama activa del menú del usuario según el contenido actual.
*/
	private function FindActive(&$menu)
	{
		$active = false;
		foreach ($menu as $id => &$item) { // Ese ampersand es el que hace todo el truco.
			if ($item['active']) {
				$active = true;
				break;
			} else {
				if (count($item['childs']) > 0) {
					if ($this->FindActive($item['childs'])) {
						$active = true;
						$item['active'] = true;
					}
				}
			}
		}
		return $active;
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
			$this->SetError(__METHOD__, $e->GetMessage());
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
				$this->SetError(__METHOD__, $e->GetMessage());
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
				$this->SetError(__METHOD__, $e->GetMessage());
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
				$this->SetError(__METHOD__, $e->GetMessage());
			}
		}
		return $result;
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
				$this->SetError(__METHOD__, $e->GetMessage());
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
				$this->SetError(__METHOD__, $e->GetMessage());
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
				$this->SetError(__METHOD__, $e->GetMessage());
			}
		}
		return $result;
	}
	/**
	 * Summary. Devuelve los permisos de los usuarios para un contenido dado o FALSE si no tiene acceso.
	 * @param int $contenido_id ID del contenido a verificar. Default = null.
	 * @param string $tipo El tipo de permiso que se quiere determinar. Default todos
	 * @return array/bool array con los permisos o false.
	 *    
	 */

	public function tienePermiso($contenido_id = null, $tipo = null)
	{

		$result = false;
		if ($this->existe) {
			try {
				if ($this->IsAdmin()) {
					$result = array('puede_ver' => 'SI', 'puede_crear' => 'SI', 'puede_modificar' => 'SI', 'puede_borrar' => 'SI');
					return $result;
				} // Es el Admin o el Owner

				// Selecciono los permisos
				$select = "SELECT `puede_ver`, `puede_crear`, `puede_modificar`, `puede_borrar` ";
				$from = "FROM " . SQLQuote($this->tabla_permitidos) . " as `permisos`";
				// del contenido al que quiero acceder
				$where = " WHERE `permisos`.`contenido_id` = " . $contenido_id . " AND `usuario_id` = " . $this->id;
				$sql = $select . $from . $where;
				// EchoLog($sql);
				$this->Query($sql);
				if ($registro = $this->First()) {
					$result = $registro;
				} else { // El contenido no está entre los permisos del usuario...
					if ($this->IsAdmin()) { // ... si es el admin, permitir todo
						$result = array('puede_ver' => 'SI', 'puede_crear' => 'SI', 'puede_modificar' => 'SI', 'puede_borrar' => 'SI');
					} else { // Sino, solo ver y modificar.
						$result = array('puede_ver' => 'SI', 'puede_crear' => 'NO', 'puede_modificar' => 'SI', 'puede_borrar' => 'NO');
					}
				}
				if (!empty($tipo)) {
					$tipo = strtoupper($tipo);
					switch ($tipo) {
						case 'VER':
							$result = ($result['puede_ver'] == 'SI');
							break;
						case 'CREAR':
							$result = ($result['puede_crear'] == 'SI');
							break;
						case 'MODIF':
							$result = ($result['puede_modificar'] == 'SI');
							break;
						case 'BORRAR':
							$result = ($result['puede_borrar'] == 'SI');
							break;
						default:
							$result = false;
					}
				}
			} catch (Exception $e) {
				$this->SetError(__METHOD__, $e->GetMessage());
			}
		}
		return $result;
	} // tienePermiso
/*
	Esto crea o recrea los permisos de acceso a los contenidos basados en el perfil del usuario.
*/
	public function CreateUserPermissions($id_usuario_actual = null)
	{
		$result = false;
		try {
			if (!$this->existe) {
				throw new Exception(__LINE__ . ' Tiene que haber un usuario activo para continuar esta operación.');
			}
			if (!empty($this->perfil_id)) {
				$this->Delete($this->tabla_permisos, "`usuario_id` = " . $this->id);
				$sql = "SELECT * FROM " . SQLQuote($this->tabla_perfiles_contenidos) . " WHERE `perfil_id` = " . $this->perfil_id . " AND `estado` = 'HAB';";
				$res = $this->Query($sql);
				if ($fila = $this->First($res)) {
					do {
						$reg = array(
							'usuario_id' => $this->id,
							'contenido_id' => $fila['contenido_id'],
							'fechahora' => Date('Y-m-d H:i:s'),
							'usralta_id' => $id_usuario_actual
						);
						$this->Insert($this->tabla_permisos, $reg);
					} while ($fila = $this->Next($res));
				}
			} else {
				cLogging::Write(__METHOD__ . ' El usuario ' . $this->id . ' no tiene ningún perfil asignado.');
			}
		} catch (Exception $e) {
			$this->SetError(__METHOD__, $e->GetMessage());
		}
		return $result;
	}


	/**
	 * Summary. Establece permisos para un contenido para el usuario actual. Si no existe, lo crea.
	 * @param int $contenido_id. El ID del contenido para el cual se quiere establecer permisos (no controla que exista!).
	 * @param array $permisos. Un array con los permisos.
	 * @param array $usuario_id default 1. ID del usuario que establece el permiso.
	 * @return boolean. true si se pudo hacer, false en caso de error.
	 */
	public function SetPermiso($contenido_id, $permisos, $usuario_id = 1)
	{
		$result = false;
		try {
			if (!$this->existe) {
				throw new Exception(__LINE__ . " No hay usuario establecido.");
			}
			if (SecureInt($contenido_id) == null) {
				throw new Exception(__LINE__ . " ID de contenido debe ser un número.");
			}
			if (!CanUseArray($permisos)) {
				throw new Exception(__LINE__ . " Permisos está vacío.");
			}
			$permisos['contenido_id'] = $contenido_id;

			$sql = "SELECT `id` FROM " . SQLQuote($this->tabla_permitidos) . " WHERE `usuario_id` = " . $this->id . " AND `contenido_id` = " . $contenido_id;

			$this->Query($sql);
			if ($fila = $this->First()) {
				$this->Update($this->tabla_permitidos, $permisos, "`id` = " . $fila['id']);
			} else {
				$permisos['usuario_id'] = $this->id;
				$permisos['usralta_id'] = $usuario_id;
				$permisos['fechahora'] = cFechas::Ahora();
				$this->Insert($this->tabla_permitidos, $permisos);
			}
			$result = true;
		} catch (Exception $e) {
			$this->SetError(__METHOD__, $e->GetMessage());
		}
		return $result;
	} // SetPermiso

	/**
	 * Summary. Borra físicamente, el registro que tiene el permiso para el contenido apuntado, para el usuario actual.
	 * @param int $contenido_id. El ID del contenido al que se quiere eliminar permisos (no controla que exista!).
	 * @return boolean. true si se pudo hacer, false en caso de error.
	 */
	public function DelPermiso($contenido_id)
	{
		$result = false;
		try {
			if (!$this->existe) {
				throw new Exception(__LINE__ . " No hay usuario establecido.");
			}
			if (SecureInt($contenido_id) == null) {
				throw new Exception(__LINE__ . " ID de contenido debe ser un número.");
			}
			$sql = "SELECT `id` FROM " . SQLQuote($this->tabla_permitidos) . " WHERE `usuario_id` = " . $this->id . " AND `contenido_id` = " . $contenido_id;
			if ($fila = $this->First($sql)) {
				$this->Delete($this->tabla_permisos, "`id` = " . $fila['id']);
			}
			$result = true;
		} catch (Exception $e) {
			$this->SetError(__METHOD__, $e->GetMessage());
		}
		return $result;
	} // DelPermiso
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
			$this->SetError(__METHOD__, $e->GetMessage());
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
			$this->SetError(__METHOD__, $e->GetMessage());
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
				$this->SetError(__METHOD__, $e->getMessage());
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
			$this->SetError(__METHOD__, $e->GetMessage());
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
			$this->SetError(__METHOD__, $e->GetMessage());
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
			$this->SetError(__METHOD__, $e->GetMessage());
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
			$this->SetError(__METHOD__, $e->GetMessage());
		}
		return $result;
	} // GetByJumble

} // end of class
