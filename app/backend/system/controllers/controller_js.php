<?php
/*
	Controlador especial para servir los archivos JavaScript.
	Created: 2020-09-23
	Author: DriverOp

	Las variables $asset y $for_files están definidas en index.php.
	
	Modif: 2021-02-06
	Author: DriverOp
	Desc:
		Hechas las modificaciones para la vesión 1.8

	Orden de búsqueda y carga de archivos:

	1.- El o los archivos indicados con el parámetro nombrado en la constante DEFAULT_FOR_FILES, en la URL. Y se termina la ejecución ignorando lo demás.
		Si se encuentra un archivo del mismo nombre en el subdirectorio del cliente, se carga ese ignorando el del raíz.

	2.- El array $required_scripts definido en este mismo archivo. Se cargan en orden como aparecen en el array.

	3.- La lista de nombres de archivos, uno por cada línea, listados en el archivo JS_LIST_FILES
		3.A- El archivo que está en DIR_js se evalúa primero.
		3.B- Luego el archivo que esté en el subdirectorio del cliente.
		3.C- Se mezclan estas listas entre ellas y con la lista del punto 2.- sin repetir (FIFO).

	4.- Los archivos listados en la propiedad 'js' del JSON en el campo metadata del contenido en la tabla _contenidos de la base de datos.
		4.A- Primero se evalúa si los archivos listados en esa propiedad existen en el subdirectorio del cliente y se cargan los que sí existen.
		4.B- Si no existen en el subdirectorio del cliente, se cargan los de la raíz.
		NOTA: Es UNO o el OTRO, no los dos (XOR). El archivo del cliente tiene precedencia.

	5.- Agrega a la lista de archivos uno con el mismo nombre que el alias. Si ese archivo existe en el subdirectorio del cliente, carga ese en vez del que está en la raíz.
*/

defined("JS_LIST_FILES") || define("JS_LIST_FILES","required_scripts.lst");

$common_path = DIR_js;
$custom_path = null;
if (DIR_js != DIR_custom_js) {
	$custom_path = DIR_custom_js;
}

/* 1.- Se buscan los archivos listados en la URL si se usó el indicador DEFAULT_FOR_FILES */
if ($for_files) {
	if (CanUseArray($handler) > 0) {
		$aux = array();
		$handler = array_map("trim",$handler); // Siempre es buena idea eliminar espacios sobrantes.
		foreach($handler as $f) {
			$aux[] = (!empty($custom_path) and ExisteArchivo($custom_path.$f.".js"))?AddCustomPath($f):AddPath($f);
		}
		LoadFiles($aux);
	} else {
		if (DEVELOPE) { ShowOutput('No se indicó archivo a cargar'); }
	}
	return;
}

/* 2.- La lista de requeridos primordiar */
$required_scripts = array(); // Listar archivos acá si y solo si es absolutamente necesario

/* 3.A- Si existe el archivo JS_LIST_FILES en el directorio raíz de los JS, se carga al array */
if (ExisteArchivo($common_path.JS_LIST_FILES)) {
	$loading_scripts = LoadListFiles($common_path.JS_LIST_FILES);
	if (CanUseArray($loading_scripts)) {
		$loading_scripts = array_map("trim",$loading_scripts); // Eliminar espacios en blanco por delante y por detrás
		$loading_scripts = array_map("ParseDS",$loading_scripts); // Reemplaza el indicador de directorio por el correcto.
		$required_scripts = array_merge($required_scripts,$loading_scripts);
		$required_scripts = array_map("AddPath",$required_scripts);
	}
	unset($loading_scripts);
}

/* 3.B- Si además tenemos un archivo JS_LIST_FILES en el directorio del cliente, se carga al array */
if (!empty($custom_path) and ExisteArchivo($custom_path.JS_LIST_FILES)) {
	$loading_scripts = LoadListFiles($custom_path.JS_LIST_FILES);
	if (CanUseArray($loading_scripts)) {
		$loading_scripts = array_map("trim",$loading_scripts); // Eliminar espacios en blanco por delante y por detrás
		$loading_scripts = array_map("ParseDS",$loading_scripts); // Reemplaza el indicador de directorio por el correcto.
		$loading_scripts = array_map("AddCustomPath",$loading_scripts); // Agregar el directorio del cliente.
		$required_scripts = array_merge($required_scripts,$loading_scripts);
	}
	unset($loading_scripts);
}

/* 3.B- Se eliminan los repetidos */
$required_scripts = array_unique($required_scripts);

/* 4.- La lista de archivos que está en la propiedad 'js' del JSON del campo 'metadata' de la tabla _contenidos de la base de datos. */
$content_list = $objeto_contenido->JsList();

if (CanUseArray($content_list)) {
	$aux = array();
	$content_list = array_map("trim",$content_list); // Siempre es buena idea eliminar espacios sobrantes.
	foreach($content_list as $f) {
		$aux[] = (!empty($custom_path) and ExisteArchivo($custom_path.$f.".js"))?AddCustomPath($f):AddPath($f);
	}
	unset($content_list);
	$required_scripts = array_unique(array_merge($required_scripts,$aux));
}



/* 5.- Agregar el alias del contenido actual como un archivo más a ser cargado */
$required_scripts[] = (!empty($custom_path) and ExisteArchivo($custom_path.$objeto_contenido->alias.".js"))?AddCustomPath($objeto_contenido->alias):AddPath($objeto_contenido->alias);

LoadFiles($required_scripts);
return;

/**
* Summary. De la lista que se le pasa como parámetro, los busca en el directorio de JS y los carga sin repetir.
* @param array $files, la lista de archivos a cargas (sin extensión), todas las rutas son "duras".
*/
function LoadFiles($files) {
	
	global $objeto_usuario;
	global $objeto_contenido;

	$files = array_unique($files, SORT_REGULAR);
	reset($files);

	if (DEVELOPE) {
		header("Cache-Control: no-cache, must-revalidate");
	}
	header("Content-type: application/javascript; charset=UTF-8");
	
	if (INTERFACE_TYPE == 'backend') $objeto_usuario->CheckLogin();

	foreach ($files as $value) {
		$ruta = DIR_BASE.$value;
		$ruta = $ruta.".js"; // Precaución adicional: Neutralizar escalada de directorios.
		if ((file_exists($ruta)) AND (is_file($ruta))) {
			echo (DEVELOPE)?"\r\n/* ".$value." */\r\n":null;
			include($ruta);
		}else{
			echo (DEVELOPE)?"\r\n/* El archivo ".$value." no pudo ser encontrado */\r\n":null;
		}
	}
	return;
} // LoadFiles

/**
* Summary. Le agrega el nombre del cliente como subdirectorio al inicio o al final de la cadena colocando el separador de directorios apropiadamente, según el modo de disposición de los archivos en el desarrollo.
* @param str El nombre de un archivo.
* @return str El nombre del archivo precedido o antecedido por el nombre del subdirectorio del cliente y el separador de directorios.
*/
function AddCustomPath($str) {
	return (FILE_DISP_MODE)?DEVELOPE_NAME.DS.JS_DIR.$str:JS_DIR.DEVELOPE_NAME.DS.$str;
}
/**
* Summary. Le agrega el nombre del directorio de los JavaScript comunes a todo el desarrollo.
* @param str El nombre de un archivo.
* @return str El nombre del archivo precedido por el nombre del directorio JS.
*/
function AddPath($str) {
	return JS_DIR.$str;
}
/**
* Summary. Busca en la cadena la subcadena '.DS.' y la reemplaza por el valor de la constante DS
* @param str La cadena objetivo.
* @return str La cadena con los reemplazos hechos.
*/
function ParseDS($str) {
	return str_replace('.DS.',DS,$str);
}
?>