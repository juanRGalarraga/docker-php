<?php
/*
	Clase para manejar el menú de un usuario.
	Created: 2021-09-22
	Author: DriverOp
*/

require_once(DIR_includes."class.logging.inc.php");
require_once(DIR_includes."class.fechas.inc.php");
require_once(DIR_model."class.fundation.inc.php");

class cUsrMenu extends cModels {
	
	const tabla_contenidos = TBL_contenido;
	
	private $LogDebug = false;
	private $plantilla = null;
	private $staticmenu = '';
	public $qMainTable = '';
	public $usuario_id = null;
	public $contenido_activo = array();

	public function __construct() {
		parent::__construct();
		$this->mainTable = self::tabla_contenidos;
		$this->qMainTable = SQLQuote(self::tabla_contenidos);
		$this->ResetInstance();
		$this->LogDebug = DEVELOPE;
        if(!ExisteCarpeta(DIR_usersmenu)){
            mkdir(DIR_usersmenu);
        }
	}

/**
* Summary. Establece quién es el usuario al cual hay que generarle el menú.
* @param int $usuario_id El ID del usuario en cuestión.
*/
	public function SetUser(int $usuario_id = null) {
		$this->usuario_id = $usuario_id;
	}

/**
* Summary. Establece cuál es el contenido activo en el menú del usuario.
* @param int $content_id El ID del usuario en cuestión.
*/
	public function SetActiveContent(int $content_id = null) {
		$this->contenido_activo[] = $content_id;
	}

/**
* Summary. Genera el menu de usuario teniendo en cuenta los permisos del usuario actual.
* @param int $parent_id El ID del parent de los hijos actualmente leídos.
* @param string $url default BASE_URL El dominio canónico del contenido padre del contenido actual
* @return array
* @note Este método se llama recursivamente.
*/

	public function Menu(int $parent_id = 0, string $url = BASE_URL):array {
		$result = [];
		if (empty($this->usuario_id)) { throw new Exception('No hay usuario actual establecido.'); }
		try {
			$sql = "SELECT $this->qMainTable.* FROM $this->qMainTable WHERE $this->qMainTable.`parent_id` = ".$parent_id." AND $this->qMainTable.`esta_protegido` = 1 AND $this->qMainTable.`estado` = 'HAB' AND $this->qMainTable.`en_menu` = 1 ORDER BY $this->qMainTable.`orden` ASC, $this->qMainTable.`id` ASC;";
			$res = $this->Query($sql, true);
			
			if ($this->cantidad > 0) {
				while ($fila = $this->Next($res)) {
					//$result[$fila->id] = $this->ParseItemMenu($fila);
					$result[$fila->id] = $fila;
					$result[$fila->id]->url = EnsureTrailingURISlash($url.$fila->alias);
					$result[$fila->id]->uri = EnsureTrailingURISlash(substr_replace($result[$fila->id]->url, '', strpos($result[$fila->id]->url, BASE_URL), strlen(BASE_URL)));
					$result[$fila->id]->active = ($this->contenido_activo == $fila->id);
				}
			}
			if (CanUseArray($result)) {
				foreach ($result as $key => $value) {
					$result[$key]->childs = $this->Menu($value->id, $result[$key]->url);
				}
			}
			
		} catch (Exception $e) {
			$this->SetError($e);
		}
		$this->FindActive($result);
		// $this->Menu_Usuario = $result;
		return $result;
	}
/**
* Summary. Pone la rama activa del menú del usuario según el contenido actual.
* @param array $menu El array con el menú.
* @return bool.
* @note Se llama recursivamente.
*/
	private function FindActive(&$menu)
	{
		$active = false;
		foreach ($menu as $id => &$item) { // Ese ampersand es el que hace todo el truco.
			if ($item->active) {
				$active = true;
				break;
			} else {
				if (count($item->childs) > 0) {
					if ($this->FindActive($item->childs)) {
						$active = true;
						$item->active = true;
					}
				}
			}
		}
		return $active;
	}
/**
* Summary. Dibuja el menú de usuario.
*/
	public function RenderMenu() {
		if (!ExisteArchivo(DIR_usersmenu.$this->usuario_id.".htm")){	$this->GenMenu();	}
		$menu = file_get_contents(DIR_usersmenu.$this->usuario_id.".htm");
		$menu = str_replace('{BASE_URL}', BASE_URL, $menu);
		if (is_array($this->contenido_activo)) {
			foreach($this->contenido_activo as $id) {
				$menu = str_replace("{active_$id}", 'active', $menu);
			}
		} else {
			$menu = str_replace("{active_$this->contenido_activo}", 'active', $menu);
		}
		$menu = preg_replace("~\s?{active_\d+}~", null, $menu);
		return $menu;
	}
/**
* Summary. Genera el menú del usuario en archivo según plantilla.
*/
	public function GenMenu() {
		$result = false;
		$menu = $this->Menu();
		if ($this->GetPlantilla()) {
			$this->staticmenu = '';
			$this->level = 'item';
			$this->TraverseMenu($menu);
			$result = file_put_contents(DIR_usersmenu.$this->usuario_id.".htm", $this->staticmenu);
		}
		return $result;
	}
	
	
	private function TraverseMenu($menu) {
		foreach($menu as $item) {
			$aux = $this->plantilla['item'];
			$aux = str_replace('[active]', "{active_$item->id}", $aux);
			$aux = str_replace('[menutag]', $item->metadata->menutag??$item->nombre, $aux);
			$aux = str_replace('[icon_class]', $item->metadata->icon_class??null, $aux);
			if (CanUseArray($item->childs)) {
				$aux = str_replace('[url_contenido]', "#", $aux);
				$aux = str_replace('[has-submenu]', 'has-sub', $aux);
				$aux = str_replace('[submenu]', $this->plantilla['submenu'], $aux);
				$aux = str_replace('[active]', "{active_$item->id}", $aux);
				$acumulador = '';
				foreach($item->childs as $child) {
					$subaux = $this->plantilla['subitem'];
					$subaux = str_replace('[active]', "{active_$child->id}", $subaux);
					$subaux = str_replace('[url_contenido]', "{BASE_URL}".$child->uri, $subaux);
					$subaux = str_replace('[menutag]', $child->metadata->menutag??$child->nombre, $subaux);
                    $subaux = str_replace('[icon_class]', $child->metadata->icon_class??null, $subaux);
					$acumulador .= $subaux;
				}
				$aux = str_replace('[sub-items]', $acumulador, $aux);
			} else {
				$aux = str_replace('[url_contenido]', "{BASE_URL}".$item->uri, $aux);
				$aux = str_replace('[has-submenu]', null, $aux);
				$aux = str_replace('[submenu]', null, $aux);
			}
			$this->staticmenu .= $aux;
		}
		return $aux;
	}
/**
* Summary. Devuelve el contenido de la plantilla para el menú estático.
* @return string/null.
*/
	public function GetPlantilla() {
		$result = false;
		$this->plantilla = array();
		try {
			if (ExisteArchivo(DIR_plantillas."main_menu_item.htm")) {
				$this->plantilla['item'] = file_get_contents(DIR_plantillas."main_menu_item.htm");
				$this->plantilla['submenu'] = file_get_contents(DIR_plantillas."main_menu_submenu.htm");
				$this->plantilla['subitem'] = file_get_contents(DIR_plantillas."main_menu_subitem.htm");
				$result = true;
			}
		} catch(Exception $e) {
			$this->SetError($e);
		}
		return $result;
	}
/**
* Summary. Devuelve TODOS los contenidos para menú sin tener en cuenta permisos de usuario.
* @param int $parent_id El ID del parent de los hijos actualmente leídos.
* @param string $url default BASE_URL El dominio canónico.
* @return array
* @note Este método se llama recursivamente.
*/
	public function Everything($parent_id = 0, $url = BASE_URL)	{
		$result = array();
		try {
			$sql = "SELECT * FROM $this->qMainTable WHERE 1=1 AND `parent_id` = " . $parent_id . " AND `esta_protegido` = 1 AND `estado` = 'HAB'  AND `en_menu` = 1 ORDER BY `orden`";
			$res = $this->Query($sql, true);
			if ($this->cantidad > 0) {
				while ($fila = $this->Next($res)) {
					//$result[$fila['id']] = $this->ParseItemMenu($fila);
					$result[$fila->id] = $fila;
					$result[$fila->id]->url = EnsureTrailingURISlash($url . $fila->alias);
					$result[$fila->id]->uri = EnsureTrailingURISlash(($parent_id != 0)?$url . $fila->alias:$fila->alias);
					$result[$fila->id]->active = ($this->contenido_activo == $fila->id);
				}
			}
			if (CanUseArray($result)) {
				foreach ($result as $key => $value) {
					$result[$key]['childs'] = $this->Everything($value->id, $result[$key]->url);
				}
			}
		} catch (Exception $e) {
			$this->SetError($e);
		}
		//$this->Menu_Usuario = $result;
		$this->FindActive($result);
		return $result;
	}
}
