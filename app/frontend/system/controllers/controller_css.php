<?php
/*
	Controlador especial para servir los archivos CSS.
	Created: 2020-09-23
	Author: DriverOp

	Las variables $asset, $for_files y $for_dirs están definidas en index.php.
	
	Modif: 2021-02-06
	Author: DriverOp
	Desc:
		Hechas las modificaciones para la vesión 1.8

	Orden de búsqueda y carga de archivos:

	1.- El o los archivos indicados con el parámetro nombrado en la constante DEFAULT_FOR_FILES, en la URL. Y se termina la ejecución ignorando lo demás.
		Si se encuentra un archivo del mismo nombre en el subdirectorio del cliente, se carga ese ignorando el del raíz.

	2.- El array $required_styles definido en este mismo archivo. Se cargan en orden como aparecen en el array.

	3.- La lista de nombres de archivos, uno por cada línea, listados en el archivo CSS_LIST_FILES
		3.A- El archivo que está en DIR_css se evalúa primero.
		3.B- Luego el archivo que esté en el subdirectorio del cliente.
		3.C- Se mezclan estas listas entre ellas y con la lista del punto 2.- sin repetir (FIFO).

	4.- Los archivos listados en la propiedad 'js' del JSON en el campo metadata del contenido en la tabla _contenidos de la base de datos.
		4.A- Primero se evalúa si los archivos listados en esa propiedad existen en el subdirectorio del cliente y se cargan los que sí existen.
		4.B- Si no existen en el subdirectorio del cliente, se cargan los de la raíz.
		NOTA: Es UNO o el OTRO, no los dos (XOR). El archivo del cliente tiene precedencia.

	5.- Agrega a la lista de archivos uno con el mismo nombre que el alias. Si ese archivo existe en el subdirectorio del cliente, carga ese en vez del que está en la raíz.
*/

defined("CSS_LIST_FILES") || define("CSS_LIST_FILES","required_styles.lst");

$common_path = DIR_css;
$custom_path = null;
if (DIR_css != DIR_custom_css) {
	$custom_path = DIR_custom_css;
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

if ($for_dirs) {
	if (CanUseArray($handler) > 0) {
		$aux = array();
		$handler = array_map("trim",$handler); // Siempre es buena idea eliminar espacios sobrantes.
		$c = implode(DS,$handler);
		$aux[] = (!empty($custom_path) and ExisteArchivo($custom_path.$c.".js"))?AddCustomPath($c):AddPath($c);
		LoadFiles($aux);
	} else {
		if (DEVELOPE) { ShowOutput('No se indicó archivo a cargar'); }
	}
	return;
}

/* 2.- La lista de requeridos primordial */
$required_styles = array(); // Listar archivos acá si y solo si es absolutamente necesario

/* 3.A- Si existe el archivo CSS_LIST_FILES en el directorio raíz de los CSS, se carga al array */
if (ExisteArchivo($common_path.CSS_LIST_FILES)) {
	$loading_styles = LoadListFiles($common_path.CSS_LIST_FILES);
	if (CanUseArray($loading_styles)) {
		$loading_styles = array_map("trim",$loading_styles); // Eliminar espacios en blanco por delante y por detrás
		$loading_styles = array_map("ParseDS",$loading_styles); // Reemplaza el indicador de directorio por el correcto.
		$required_styles = array_merge($required_styles,$loading_styles);
		$required_styles = array_map("AddPath",$required_styles);
	}
	unset($loading_styles);
}

/* 3.B- Si además tenemos un archivo CSS_LIST_FILES en el directorio del cliente, se carga al array */
if (!empty($custom_path) and ExisteArchivo($custom_path.CSS_LIST_FILES)) {
	$loading_styles = LoadListFiles($custom_path.CSS_LIST_FILES);
	if (CanUseArray($loading_styles)) {
		$loading_styles = array_map("trim",$loading_styles); // Eliminar espacios en blanco por delante y por detrás
		$loading_styles = array_map("ParseDS",$loading_styles); // Reemplaza el indicador de directorio por el correcto.
		$loading_styles = array_map("AddCustomPath",$loading_styles); // Agregar el directorio del cliente.
		$required_styles = array_merge($required_styles,$loading_styles);
	}
	unset($loading_styles);
}

/* 3.B- Se eliminan los repetidos */
$required_styles = array_unique($required_styles);

/* 4.- La lista de archivos que está en la propiedad 'css' del JSON del campo 'metadata' de la tabla _contenidos de la base de datos. */
$content_list = $objeto_contenido->CssList();
if (CanUseArray($content_list)) {
	$aux = array();
	$content_list = array_map("trim",$content_list); // Siempre es buena idea eliminar espacios sobrantes.
	foreach($content_list as $f) {
		$aux[] = (!empty($custom_path) and ExisteArchivo($custom_path.$f.".css"))?AddCustomPath($f):AddPath($f);
	}
	unset($content_list);
	$required_styles = array_unique(array_merge($required_styles,$aux));
}



/* 5.- Agregar el alias del contenido actual como un archivo más a ser cargado */
$required_styles[] = (!empty($custom_path) and ExisteArchivo($custom_path.$objeto_contenido->alias.".css"))?AddCustomPath($objeto_contenido->alias):AddPath($objeto_contenido->alias);

LoadFiles($required_styles);
return;


/**
* Summary. De la lista que se le pasa como parámetro, los busca en el directorio de CSS y los carga sin repetir. Si el framework no está en modo develope, hace una compresión lijera.
* @param array $files, la lista de archivos a cargas (sin extensión)
*/
function LoadFiles($files) {

	global $objeto_usuario;
	global $objeto_contenido;

	$files = array_unique($files);
	
	reset($files);

	if (DEVELOPE) {
		header("Cache-Control: no-cache, must-revalidate");
	}
	header("Content-type: text/css; charset=UTF-8");

	if (!DEVELOPE) {
		ob_start("compressCss");
	}

	foreach ($files as $value) {
		$ruta = DIR_BASE.$value;
		$ruta = cSecurity::NeutralizeDT($ruta).".css"; // Precaución adicional: Neutralizar escalada de directorios.
		if ((file_exists($ruta)) AND (is_file($ruta))) {
			echo "\r\n/* ".$value.".css */\r\n";
			include($ruta);
		}else{
			echo "\r\n/* El archivo ".$value.".css no pudo ser encontrado */\r\n";
		}
	}

	if (!DEVELOPE) {
		ob_end_flush();
	}
} // LoadFiles.

/**
* Summary. Quita los espacios, tabuladores y retornos de carro irrelevantes. Quita los comentarios.
* @param buffer $buffer Un buffer de salida del servidor web.
* @return el buffer modificado.
*/
	function compressCss($buffer) {
		/* remove comments */
		$buffer = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $buffer);
		/* remove tabs, spaces, newlines, etc. */
		$buffer = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $buffer);
		/* remove irrelevant white spaces */
		$buffer = str_replace(
			array(', ',': ',' {','{ ',' ;','; '),
			array(',',':','{','{',';',';'),
			$buffer);
		return $buffer;
	}

/**
* Summary. Le agrega el nombre del cliente como subdirectorio al inicio o al final de la cadena colocando el separador de directorios apropiadamente, según el modo de disposición de los archivos en el desarrollo.
* @param str El nombre de un archivo.
* @return str El nombre del archivo precedido o antecedido por el nombre del subdirectorio del cliente y el separador de directorios.
*/
function AddCustomPath($str) {
	return (FILE_DISP_MODE)?DEVELOPE_NAME.DS.CSS_DIR.$str:CSS_DIR.DEVELOPE_NAME.DS.$str;
}
/**
* Summary. Le agrega el nombre del directorio de los JavaScript comunes a todo el desarrollo.
* @param str El nombre de un archivo.
* @return str El nombre del archivo precedido por el nombre del directorio CSS.
*/
function AddPath($str) {
	return CSS_DIR.$str;
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