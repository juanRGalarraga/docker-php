<?php
/*
	Controlador especial para servir los archivos solicitados mediante petición AJAX.
	Created: 2020-09-23
	Author: DriverOp
	
	Las variables $user_logged_in, $objeto_contenido y $objeto_usuario están definidas en index.php
*/


	header("Cache-Control: no-cache, must-revalidate");
	header("Content-type: text/html; charset=UTF-8");
	
	$get_archivo = null;
	$get_content = null;
	$post_max_size = TranslateShorthandNotation(ini_get("post_max_size"));
	
	if (isset($_SERVER['CONTENT_LENGTH']) and ((int)$_SERVER['CONTENT_LENGTH']) > $post_max_size) {
		EmitJSON('Petición demasiado grande.');
		cLogging::Write(__FILE__ ." ".__LINE__ ." Excedido el tamaño máximo para una petición. Establecido (post_max_size): ".$post_max_size.". Recibido (content-length): ".$_SERVER['CONTENT_LENGTH'], LGEV_ERROR);
		return;
	}
	
	if (!empty($handler) and is_array($handler) and (count($handler) > 0)) {
		$get_archivo = $handler[count($handler)-1]; // El último en el path es 'archivo'
		unset($handler[count($handler)-1]);
		if (count($handler) > 0) {
			$get_content = implode(DS,$handler).DS; // Los restantes son 'content'
		}
	}
	
	$ajax_id = null;
	$handler = array(DEFAULT_CONTENT);
	if (!empty($_SERVER['HTTP_REFERER']) and cSecurity::IsAllowedHost($_SERVER['HTTP_REFERER'])) { // Solo del propio sitio...
		$handler = cSecurity::ParsePath(mb_substr($_SERVER['HTTP_REFERER'],mb_strlen(BASE_URL))); // Convertir los dir virturales del referer en un array.
	}

	$ajax_archivo = @$_REQUEST['archivo'];
	if (empty($ajax_archivo)) {
		$ajax_archivo = $get_archivo;
	}
	$ajax_archivo = cSecurity::SatinizePathParam(trim($ajax_archivo)); // No se permite incluir directorios.
	
	if (empty($ajax_archivo)) {
		EmitJSON('No se indicó el archivo a cargar.');
		cLogging::Write(__FILE__." ".__LINE__." No se indicó el archivo a cargar.");
		exit;
	}

	$dir_content = @$_REQUEST['content'];
	if (!empty($dir_content)) {
		$dir_content = mb_substr($dir_content,0,128);
		$dir_content = cSecurity::NeutralizeDT(trim($dir_content)); // Si se permite ir a un directorio más profundo, pero no escalar directorio.
		if (!empty($dir_content)) {
			$dir_content = EnsureTrailingSlash($dir_content);
		} else {
			$dir_content = $get_content;
		}
	} else {
		$dir_content = $get_content;
	}

/* Si el tipo de interfaz es backend, denegar todo excepto lo desmilitarizado. */
	if (INTERFACE_TYPE == 'backend') {
		if (!cSecurity::Demilitarized($dir_content,$ajax_archivo)) { // El contenido solicitado está desmilitarizado?
			if (!$user_logged_in) { // Solo si está logueado puede acceder a contenido militarizado.
				header($_SERVER['SERVER_PROTOCOL'].' 401 Authorization Required');
				EmitJSON(['generr'=>'Debe iniciar sesión de usuario','logging_required'=>true]);
				cLogging::Write(__FILE__." ".__LINE__." Se intentó acceder sin tener sesión de usuario abierta a ".$dir_content.$ajax_archivo);
				exit;
			}
		}
	}

	$ajax_id = SecureInt(substr(trim(@$_REQUEST['id']),0,11),NULL);

/*
	Para determinar dónde está el archivo que finalmente se va a ejecutar...
	Primero se busca en el directorio custom, el que está definido por la constante DEVELOPE_NAME trasladada a DIR_custom_ajax
	Si no está ahí, entonces se busca en el directorio "raiz" de Ajax apuntado por DIR_ajax.
*/
	$ruta_parcial = $dir_content.$ajax_archivo.'.php';
	
	$common_path = DIR_ajax;
	$custom_path = null;
	if (DIR_ajax != DIR_custom_ajax) {
		$custom_path = DIR_custom_ajax;
	}
	$ruta_final = $common_path.$ruta_parcial;

	if (!empty($custom_path) and ExisteArchivo($custom_path.$ruta_parcial)) {
		$ruta_final = $custom_path.$ruta_parcial;
	}

	if (ExisteArchivo($ruta_final)) {
		include_once($ruta_final);
	} else {
		// No voy a revelar la ruta completa en el servidor!
		if (strpos($ruta_final, DIR_ajax) !== false) {
			$ruta_final = substr_replace($ruta_final, '', strpos($ruta_final, DIR_ajax), strlen(DIR_ajax));
		}
		header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found');
		EmitJSON("No existe el archivo: ".$ruta_final, true, true);
		cLogging::Write(__FILE__." "." No se encontró ".$ruta_final);
	}
?>