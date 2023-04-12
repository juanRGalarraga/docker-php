<?php
include("core_constants.inc.php");
/*
	Biblioteca de funciones comunes.
*/

/* ###########################################

	Funciones de ayuda al desarrollo.

   ###########################################*/
/* Muestra una variable para debug */
function ShowVar($var, $type = false) {
	echo (PHP_SAPI == 'cli')?null:'<pre>';
	if ($type) { var_dump($var); }
	else { print_r($var); }
	echo (PHP_SAPI == 'cli')?null:'</pre>';
} // ShowVar

/**/
function EchoLog($msg) {
	echo $msg.FDL;
}
function EchoLogP($msg) {
	echo "<p>".nl2br($msg)."</p>";
}
function ShowOutput($str) {
	echo "/* ".$str." */";
}
/**
* Summary. Pone un mensaje de aviso que debe ser visto por el programador. ¡¡¡¡ No lo uses para mostrarle cosas al usuario final !!!!
* @param str $msg El mensaje a mostrar.
*/
function WarnLogP($msg) {
	echo '<div style="background-color:white;color:black;font-family:monospace;font-size:10pt;line-height:10pt;position:absolute;top:1rem;left:0;padding:10pt;width:100%">';
	echo nl2br($msg);
	echo '</div>';
}

/*  ###########################################

	Helpers varios.

    ########################################### */
/**
* Summary. Determina si un archivo existe, es un archivo y es legible.
* @param str $file La ruta al archivo evaluado.
* @return bool
*/
function ExisteArchivo($file) {
	return (file_exists($file) and is_file($file) and is_readable($file));
}
/**
* Summary. Determina si un array no está vacío, es realmente un array y contiene al menos un elemento.
* @param mixed $array.
* @return bool.
*/
function CanUseArray($array) {
	return ((!empty($array)) and (is_array($array)) and (count($array)>0));
}

/**
* Summary. Se asegura que el último caracter de la cadena es un separador de directorio. Solo si la cadena no está vacía.
* @param string $str La cadena a evaluar contieniendo un path.
* @return string el path con el separador incluido al final.
*/
function EnsureTrailingSlash($str) {
	if (!empty($str)) {
		if (substr($str,-1) != DIRECTORY_SEPARATOR) {
			$str .= DIRECTORY_SEPARATOR;
		}
	}
	return $str;
}
function EnsureTailingSlash($str) { // Alias de la anterior.
	return EnsureTrailingSlash($str);
}
/**
* Summary. Se asegura que el último caracter de la cadena es un separador de URI. Solo si la cadena no está vacía.
* @param str La URI candidata.
* @return str La URI con el caracter separador al final.
*/
function EnsureTrailingURISlash($str) {
	if (!empty($str)) {
		if (substr($str,-1) != '/') {
			$str .= '/';
		}
	}
	return $str;
}
function EnsureTailingURISlash($str) { // Alias de la anterior.
	return EnsureTrailingURISlash($str);
}

/*  ###########################################

	Respuestas al cliente.

    ########################################### */
/**
* Summary. Imprime la estructura JSON como mensaje para JavaScript. Errores.
*/
function EmitJSON($msg, $generr = true, $hidden = false) {
	if ($hidden) { echo '<!-- '; }
	echo '<json>';
	$salida = array();
	if (!empty($msg) and is_array($msg) and (count($msg)>0)) {
		if (isset($msg['generr'])) {
			$salida = $msg;
		} else {
			$salida["dataerr"] = json_decode(json_encode($msg));
		}
	} else {
		if ($generr) {
			$salida["generr"] = $msg;
		} else {
			$salida["goodmsg"] = $msg;
		}
	}
	$salida["time"] = Date('Y-m-d H:i:s');
	echo json_encode($salida);
	echo '</json>';
	if ($hidden) { echo ' -->'; }
}
/**
* Summary. Imprime mensaje JSON para JavaScript. Todo bien
*/
function ResponseOk($msg = null, $hidden = false) {
	if ($hidden) { echo '<!-- '; }
	echo '<json>';
	$salida = array("ok"=>"ok", "time"=>Date('Y-m-d H:i:s'));
	if (!empty($msg)) {
		if (is_string($msg)) {
			$salida['ok'] = $msg;
		}
		if (is_array($msg)) {
			$salida = array_merge($salida, $msg);
		}
		if (is_object($msg)) {
			$msg = (array)$msg;
			$salida = array_merge($salida, $msg);
		}
	}
	echo json_encode($salida);
	echo '</json>';
	if ($hidden) { echo ' -->'; }
}

/*  ###########################################

	Información del sistema.

    ########################################### */

/**
* Summary. Determina si el número IP que se pasa como parámetro es válido.
* @param str $ip El número IP.
* @return bool.
*/
function ValidIP($ip) {
	if (!empty($ip) && ip2long($ip)!=-1) {
		$reserved_ips = array (
			array('0.0.0.0','2.255.255.255'),
			array('10.0.0.0','10.255.255.255'),
			array('127.0.0.0','127.255.255.255'),
			array('169.254.0.0','169.254.255.255'),
			array('172.16.0.0','172.31.255.255'),
			array('192.0.2.0','192.0.2.255'),
			array('192.168.0.0','192.168.255.255'),
			array('255.255.255.0','255.255.255.255')
		);
		foreach ($reserved_ips as $r) {
			$min = ip2long($r[0]);
			$max = ip2long($r[1]);
			if ((ip2long($ip) >= $min) and (ip2long($ip) <= $max)) { return false; }
		}
		return true;
	} else { return false; }
}

/**
* Summary. Obtener, por todos los medios posibles, el número de IP real del cliente.
* @return str.
*/
function GetIP() {
	if (php_sapi_name() == "cli"){
		return "CLImode";
	}
	$result = "0.0.0.0";
	if (!empty($_SERVER["HTTP_CLIENT_IP"]) and Validip($_SERVER["HTTP_CLIENT_IP"])) {
		$result = $_SERVER["HTTP_CLIENT_IP"];
	}
	if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
		foreach (explode(",",$_SERVER["HTTP_X_FORWARDED_FOR"]) as $ip) {
			if (ValidIP(trim($ip))) { $result = $ip; break; }
		}
	}
	if (!ValidIP($result)) {
		if (!empty($_SERVER["HTTP_X_FORWARDED"]) and ValidIP($_SERVER["HTTP_X_FORWARDED"])) { $result =  $_SERVER["HTTP_X_FORWARDED"]; }
		else {
			if (!empty($_SERVER["HTTP_FORWARDED_FOR"]) and ValidIP($_SERVER["HTTP_FORWARDED_FOR"])) { $result = $_SERVER["HTTP_FORWARDED_FOR"]; } 
			else {
				if (!empty($_SERVER["HTTP_FORWARDED"]) and ValidIP($_SERVER["HTTP_FORWARDED"])) { $result = $_SERVER["HTTP_FORWARDED"]; } 
				else { 
					if (!empty($_SERVER["HTTP_X_FORWARDED"]) and ValidIP($_SERVER["HTTP_X_FORWARDED"])) { $result = $_SERVER["HTTP_X_FORWARDED"]; }
					else { 
						if (!empty($_SERVER["REMOTE_ADDR"])) { $result = $_SERVER["REMOTE_ADDR"]; }
						else { $result = 'localhost'; }
					}
				}
			}
		}
	}
	return $result;
} // GetIP

/*  ###########################################

	Funciones del Framework.

    ########################################### */

/**
* ¡¡¡¡¡¡¡¡¡¡¡¡¡¡ NO SIRVE PARA DETERMINAR SI UN ARCHIVO EXISTE !!!!!!!!!!!
* Summary. Modifica el path en relación al cliente actual (constante DEVELOPE_NAME), devuelve la ruta absoluta y el nombre. Se usa para cargar contenido customizado para cada cliente dentro del framework. Si no existe el archivo custom, regresa lo mismo que se pasó como parámetro .
* @param str $filename. El nombre del archivo.
* @return $result El path completo al archivo modificado de acuerdo al valor de DEVELOPE_NAME.
*/
function CustPath($filename) {
	$result = $filename;
	["basename"=>$basename, "dirname"=>$dirname] = pathinfo($filename);
	$dirname = EnsureTrailingSlash($dirname);
	if (strpos($dirname,DIR_BASE) !== 0) {
		$filename = DIR_BASE.$filename;
		["basename"=>$basename, "dirname"=>$dirname] = pathinfo($filename);
	}
	if (defined("DEVELOPE_NAME") and !empty(trim(DEVELOPE_NAME)) and is_string(DEVELOPE_NAME)) {
		if (FILE_DISP_MODE) {
			// Ej: c:\www\ombucredit\js\
			$a = substr_replace($dirname, '', strpos($dirname, DIR_BASE), strlen(DIR_BASE));
			$candidate = EnsureTrailingSlash(DIR_BASE.DEVELOPE_NAME).$a.$basename;
		} else {
			// Ej: c:\www\js\ombucredit\
			$candidate = EnsureTrailingSlash($dirname).EnsureTrailingSlash(strtolower(DEVELOPE_NAME)).$basename;
		}
		if (ExisteArchivo($candidate)) {
			$result = $candidate;
		}
	}
	return $result;
}
/**
* Summary. Pasado como parámetro el campo metadata de contenidos (ya parseado en objeto), devuelve un array con los archivos de vista estén o no estén listados en metadata.
* @param object $metadata. El objeto metadata.
* @param str $alias El alias del contenido.
* @return array El listado de archivos donde la key es la vista de cada parte y el valor el nombre del archivo correspondiente o null si no fue indicado.
*/
function ParseMetadata($metadata,$alias = null) {
	$result = array();
	if (is_object($metadata)) {

		$result['template'] = (!empty($metadata->template))?$metadata->template:DEFAULT_SITE_TEMPLATE;
		$result['template'] = CustPath(DIR_plantillas.$result['template'].'.htm');

		$result['head'] = (!empty($metadata->head))?$metadata->head:DEFAULT_SITE_HEAD;
		$result['head'] = CustPath(DIR_common.$result['head'].'.htm');

		$result['header'] = (!empty($metadata->header))?$metadata->header:DEFAULT_SITE_HEADER;
		$result['header'] = CustPath(DIR_common.$result['header'].'.htm');

		$result['mainmenu'] = (!empty($metadata->mainmenu))?$metadata->mainmenu:DEFAULT_SITE_MENU;
		$result['mainmenu'] = CustPath(DIR_common.$result['mainmenu'].'.htm');
		if (!isset($metadata->hasmainmenu)) { $metadata->hasmainmenu = true; } // Por omisión Sí
		$result['hasmainmenu'] = $metadata->hasmainmenu;


		$result['vista'] = (!empty($metadata->vista))?$metadata->vista:$alias;
		$result['submenu'] = $result['vista']."_submenu.htm";
		$result['vista'] = CustPath(DIR_site.$result['vista'].'.htm');
		$result['submenu'] = CustPath(DIR_site.$result['submenu']);

		if (!isset($metadata->hassubmenu)) { $metadata->hassubmenu = false; } // Por omisión No
		$result['hassubmenu'] = $metadata->hassubmenu;

		$result['footer'] = (!empty($metadata->footer))?$metadata->footer:DEFAULT_SITE_FOOTER;
		$result['footer'] = CustPath(DIR_common.$result['footer'].'.htm');

	}
	return $result;
}
/**
* Summary. Devuelve en un array el contenido de un archivo, se usa para cargar assets los controladores de JS y CSS.
* @param str $filepath Ruta al archivo físico.
* @return array El array resultante.
* @note Esta función se saltea líneas del archivo que comienzan con dobre barra // o punto y coma ya que se considentan líneas de comentarios. NO VERIFICA LA EXISTENCIA DEL ARCHIVO.
*/
function LoadListFiles($filepath) {
	$result = array();
	if ($aux = file($filepath)) {
		foreach($aux as $a) {
			if ((substr($a,0,1) != ';') and (substr($a,0,2) != '//')) {
				$result[] = $a;
			}
		}
	}
	return $result;
}
/**
* Summary. Traduce la notación abreviada de PHP.ini para tamaños de información (bytes) a un integer que se pueda comparar.
* @param string $valor El valor abreviado (o no) tomado del php.ini
* @return int El valor traducido en bytes.
*/
function TranslateShorthandNotation($valor) {
	$result = 0;
	$valor = trim($valor);
	if (empty($valor)) { return $result; }
	$a = strtoupper(substr($valor,-1));
	$b = substr($valor, 0, strlen($valor)-1);
	$b = (int)$b;
	if (!empty($b)) {
		switch($a) {
			case 'B': $result = $b; break;
			case 'K': $result = $b*1024; break;
			case 'M': $result = $b*1024*1024; break;
			case 'G': $result = $b*1024*1024*1024; break;
			 default: $result = (int)$valor;
		}
	}
	return $result;
}

/*  ###########################################

	Verificaciones de tipo de contenidos de variables.

    ########################################### */

/**
* Summary. Verifica si una cadena es un número entero.
* @param mixed $int el valor a determinar.
* @return bool.
*/
function CheckInt($int){
	if(is_numeric($int) === TRUE){if((int)$int == $int){return TRUE;} else {return FALSE;}}else {return FALSE;}
}
/**
* Summary. Verifica que $var es un número entero, en caso de no serlo, devuelve el valor $default 
* @param mixed $var el valor a determinar.
* @param mixed $default default null el valor que se debe devolver en caso que $var no sea un número entero.
* @return mixed $var o $default.
*/
function SecureInt($var,$default = NULL) {
	$var = substr($var,0,strlen(PHP_INT_MAX.''));
	if (!CheckInt($var)) { $var = $default; }
	return $var;
}
/**
* Summary. Verifica que $var es un número entero arbitrariamente grande, en caso de no serlo, devuelve el valor $default
* @param mixed $var el valor a determinar.
* @param mixed $default default null el valor que se debe devolver en caso que $var no sea un número entero.
* @param int $maxlen default 16 Cuántos caracteres de $var evaluar.
* @return mixed $var o $default.
*/
function SecureBigInt($var, $default = NULL, $maxlen = 16) {
	if (!preg_match("/^[-+]?\d{0,".$maxlen."}$/",$var)) {
		$var = $default;
	}
	return $var;
}

/*
	Fltra un array dejando pasar solo los elementos que son enteros
*/
function SecureIntArray($array,$default = NULL) {
	$result = array();
	if (CanUseArray($array)) {
		foreach($array as $var) {
			if (CheckInt($var)) { $result[] = $var; }
		}
	}
	return $result;
}
/*
	Verifica si una cadena es un número real.
*/
function CheckFloat($float) {
	if(is_numeric($float) === TRUE){
		$float = (float)$float;
		if(is_float($float)){
			return TRUE;
		} else {
			return FALSE;
		}
	} else {
		return FALSE;
	}
}
/*
	Verifica que $var es un número real, en caso de no serlo, devuelve el valor $default
*/
function SecureFloat($var,$default = NULL) {
	if (!CheckFloat($var)) { $var = $default; }
	return $var;
}
/*
	Verifica que $var es un número real grande, en caso de no serlo, devuelve el valor $default
*/
function SecureBigFloat($var,$default = NULL) {
	$var = str_replace(',','.',$var);
	if (!preg_match("/^[-+]?\d*(\.?\d)*$/",$var)) {
		$var = $default;
	}
	return $var;
}

/**
* Summary. Trata de modificar la entrada interpreándola como un número decimal y devolver un string que PHP pueda transformar en float.
* @param string $str 
* @return string.
*/
function ParseFloat(string $str) {
	$bk = $str;
	$pospunto = strpos($str, ".");
	 $poscoma = strpos($str, ",");
	if ($pospunto === false and $poscoma === false) {
		return $str; // Es solo un integer
	}
	if ($pospunto !== false and $poscoma === false) {
		return $str; // Ya es un float PHP
	}
	if ($pospunto === false and $poscoma !== false) {
		// La coma es el separador de decimales?
		if ($poscoma < 3) {
			return str_replace(",",".",$str); // Lo es!
		}
	}
	if ($pospunto < $poscoma) {
		// $str es así 12.345,67 o así 12345,67
		$str = str_replace(".","",$str); // Quito el punto separador de miles 12345,67
		return str_replace(",",".",$str); // Y reemplazo la coma por el punto 12345.67
	}
	if ($pospunto > $poscoma) {
		// $str es así 12,345.67
		return str_replace(",","",$str); // Quito la coma separador de miles 12345.67
	}
	return $bk;
}

/*  ###########################################

	Traducción dinámica.

    ########################################### */

function EchoLang($indice=null,$default="") {
	echo ReturnLang($indice,$default);
}

function ReturnLang($indice=null,$default="") {
	if(empty($default)){ $default = $indice; }
	return $default;
}

/*  ###########################################

	Tratamiento de cadenas.

    ########################################### */
/**
* Summary. Iguala dos cadenas convirtiendo tildes.
**/
function AplanarStr($string) {
	//setlocale(LC_CTYPE , 'es_ES');
	$string = preg_replace('/\s\s+/',' ',$string); // Eliminar espacios duplicados dejando solo uno.
	$string = mb_strtolower($string);
	$string = str_replace(reptildes, repplanas, $string);
	return $string;
}
/**
* Summary. Determina si dos cadenas son similares.
* @param string $str1 Una de las cadenas a comparar
* @param string $str2 La otra cadena a comparar.
* @return bool.
*/
function SameStr($str1,$str2) {
	return (AplanarStr($str1) == AplanarStr($str2));
}

/**
* Summary. Pone la primer letra de todas las palabras en mayúscula considerando UTF-8
* @param string $string La cadena a convertir
* @return string la cadena convertida.
*/
function mb_ucfirst($string) {
	mb_internal_encoding('UTF-8');
	$string = mb_strtoupper(mb_substr($string, 0, 1)) . mb_substr($string, 1);
	return $string;
}
/**
* Summary. Agrega apóstrofes alrededor de las cadenas. Para ser usada en el armado de sentencias SQL.
* @param string $cad La cadena a encerrar.
* @param string optional default null Cadena para preceder a $cad que también será encerrada en apóstrofes.
* @return string la cadena encerrada.
*/
function SQLQuote($cad,$pre = null) {
	$result = '';
	if ($pre != null) {
		$result = "`".$pre."`.`".$cad."`";
	} else {
		$result = "`".$cad."`";
	}
	return $result;
}

/**
* Summary. Dado un string, trata de determinar si lo que contiene es una estructura JSON.
* @param string $str El supuesto string.
* @return bool.
*/
function IsJsonEx($str) {
	return (is_string($str))?preg_match('/^\[?\s*{[\b"\[\s]??(.*?)}\s*\]?$/s',trim($str)):false;
}

/**
* Summary. Extraer la estructura JSON contenida en una cadena
* @param string $str El supuesto string.
* @return string.
* @note Devuelve solo la primer coincidencia.
*/
function ExtractJsonEx($str) {
	$result = '';
	if ((is_string($str)) and preg_match_all('/(\{|\[)+(?<=\{).*(?=\})(\}|\])+/s',trim($str),$matches)) {
		if (CanUseArray($matches) and isset($matches[0][0])) {
			$result = trim($matches[0][0]);
		}
	}
	return $result;
}

/**
* Summary. Convertir una cadena a identificador válido JS en camel case.
* @param string $value
* @return string.
*/
function camelCase(string $string):string {
	if (empty($string)) { return 'null'; }
	$string = mb_strtolower($string);
	$string = AplanarStr($string);
	$string = preg_replace('/\s\s+/',' ',$string); // Eliminar espacios duplicados dejando solo uno.
	$string = preg_replace('/--+/','-',$string); // Eliminar guiones duplicados dejando solo uno. Para que no interfiera con lo que sigue.
	$string = str_replace(' ','-',$string);
	$string = str_replace('-', '', ucwords($string, '-'));
	$string = lcfirst($string);
	return $string;
}
/**
* Summary. Corta $cad por el espacio en blanco siguente a la posición $len y agrega $elipse al final.
* @param string $cad La cadena a cortar.
* @param int $len A los cuántos caracteres cortar.
* @param string $elipse El caracter de sustitución.
* @param bool $strict Pone el elipse exactamente en la posición $len y elimina el resto.
* @return string el string cortado y reemplazado.
*/
function CortarElipse($cad, $len, $elipse = null, $strict = false) {
	$result = $cad;
	if (empty($elipse)) { $elipse = "&hellip;"; }
	if (mb_strlen($cad) > $len) {
		if (!$strict) {
			$posSpace = mb_strpos($cad," ",$len);
			if (is_bool($posSpace) and ($posSpace == false)) {
				$posSpace = mb_strlen($cad);
			}
			$posHyphen = mb_strpos($cad,"-",$len);
			if (!is_bool($posHyphen) and ($posHyphen < $posSpace)) {
				$posSpace = $posHyphen;
			}
			if ($posSpace > 0) {
				$result = mb_substr($cad,0,$posSpace).$elipse;
			} else { $result = $cad; }
		} else {
			$result = mb_substr($cad,0,($len-1)).$elipse;
		}
	}
	return $result;
}


/**
* Summary. Hace lo mismo que mysqli_real_escape_string() pero sin necesidad de tener la conexión a la base de datos abierta.
* @param string $string La cadena de caracteres objetivo.
* @return string.
* @note Esta función, al contrario de mysqli_real_escape_string(), no tiene en cuenta el conjunto de caracteres usando por MySQL (por obvias razones).
Los caracteres que deben ser escapados:
00 = \0 (NUL)
0A = \n
0D = \r
1A = ctrl-Z
22 = "
25 = % <-- Se usa en LIKE
27 = '
5C = \
5F = _ <-- Se usa en LIKE
*/

function mb_escape(string $string) {
	$theCharacters = '[\x00\x0A\x0D\x1A\x22\x25\x27\x5C\x5F]';
	if (function_exists('mb_ereg_replace')) {
		return mb_ereg_replace($theCharacters, '\\\0', $string);
	} else {
		return preg_replace('~'.$theCharacters.'~u', '\\\$0', $string);
	}
}


/**
* Summary. Realiza una neutralización simple de una cadena eliminando tags HTML y reemplazando caracteres por su identidad HTML.
* @param string $string La cadena objetivo.
* @return string el string neutralizado.
*/
function NeutralizeStr($string = null) {
	return htmlentities(strip_tags($string));
}

/**
* Summary. Realiza una neutralización simple de una cadena eliminando tags HTML y reemplazando caracteres especiales por su identidad HTML. Además recorta la cadena al largo indicado.
* @param string $string La cadena objetivo.
* @param int $len La cantidad de caracteres a dejar de la cadena desde el inicio.
* @return string el string neutralizado.
*/
function SatinizeStr($string = null, $len = 32) {
	return htmlspecialchars(strip_tags(mb_substr(trim($string),0,$len)));
}

/*  ###########################################

	Tratamiento de archivos y carpetas.

    ########################################### */

/**
* Summary. Determina si un directorio existe y es accesible.
* @param string $folder El directorio a determina su existencia
* @return bool.
*/
function ExisteCarpeta($folder) {
	$path = realpath($folder);
    if($path !== false AND is_dir($path) AND is_readable($folder)) {       
		return $path;
	}
	return false;
}

/**
* Summary. Dado un nombre de archivo, devuelve su nombre (la parte anterior al último punto hasta el último separador de directorio, si existe).
* @param string $archivo El nombre de archivo del cual hay que extraer su nombre
* @return string el nombre de $archivo.
*/
function ExtraerNombre($archivo) {
	$aux = pathinfo($archivo);
	if (isset($aux['basename'])) {
		return $aux['basename'];
	} else {
		return "";
	}
}

/**
* Summary. Dado un nombre de archivo, devuelve su extensión (todo lo que está a continuación del último punto solo si ese punto es posterior al último separador de directorio, si existe).
* @param string $archivo El nombre de archivo del cual hay que extraer su extensión.
* @return string la extensión de $archivo.
*/
function ExtraerExtension($archivo) {
	$aux = pathinfo($archivo);
	if (isset($aux['extension'])) {
		return $aux['extension'];
	} else {
		return "";
	}
}

/*
	Eliminar items vacíos de un array.
*/
function CleanArray(&$arr) {
	$result = null;
	foreach($arr as $key => $value) {
		// $key = strtolower($key); // No!. Hay scripts que sí les importa que la clave tenga mayúsculas.
		if (is_array($value)) {
			$result[$key] = CleanArray($value);
		} else {
			$result[$key] = trim($value);
		}
	}
	return $result;
}

/*
	Genera un token random
*/
function genToken(){
	$token =	substr(str_shuffle('0123456789abcdfghijklmnopqrstuvwxyz'),4,8);
	$result =	substr(md5($token),0,rand(2,3)).substr(str_shuffle('abcdfghijklmnopqrstuvwxyz'),3,4);
	return $result;
}
/**
* Summary. Escapar los caracteres que son significativos en una expresión regular.
* @param string $str.
*/
function filter_preg($str){
	$result = @$str;
	if(!empty($result)){
		$result = preg_replace('/([\[|\]|\(|\)|\.|\\\|\*|\+|\?|\{|\}|\||\^|\-])/im', '\\\\$1', $str);
	}
	return $result;
}

/**
* Summary. Devuelve un número float con separador de miles y decimales.
* @param float $value El número a formatear.
* @param int $dec Cuántos decimales mostrar.
* @return string.
*/
function F($value, $dec = 2) {
	if (empty($value)) { $value = 0; }
	return number_format($value,$dec,',','.');
}

/**
* Summary. Devuelve un número float como entero truncando la parte decimal sin separador de miles.
* @param float $value El número a formatear.
* @return string.
*/
function I($value) {
	return (!empty($value))?number_format($value,0,',',''):$value;
}

function ShowDie($var,$type = false){
	ShowVar($var,$type);
	die();
}

/**
 * Summary. Dado un número devuelve el mensaje del error de JSON
 * @param int $number El número de error
 */
function GetJsonMsg($number){
    $result = "Error desconocido";
    if(CheckInt($number)){
        $result = JSON_ERROR_MSG_ESP[$number] ?? $result;
    }
    return $result;
}

/**
 * Summary. Copia todo el contenido de una carpeta a otra
 * @param string $origen Desde donde copiar los elementos
 * @param string $destino a donde copiar los elementos
 * @param bool $result true en caso de exito, false en caso de error
 */
function CopyFolderTo($origen,$destino){
    $result = true;
    if(!ExisteCarpeta($origen)){
        return false;
    }

    $origen = EnsureTailingSlash($origen);
    $destino = EnsureTailingSlash($destino);
    if(!ExisteCarpeta($destino)){
        mkdir($destino);
    }
    foreach(glob($origen."*") as $value){
        //Solo me quedo con el final de la cadena luego del separador de directorio
        $tmp = explode(DS,$value);
        $tmp = array_pop($tmp);
        if(is_dir($value)){
            if(!$result = CopyFolderTo($value,$destino.$tmp)){
                break;
            }
            continue;
        }

        if(!$result = copy($value,$destino.$tmp)){
            break;
        }
    }

    return $result;
}

/**
 * Summary. Borra una carpeta junto a todos sus subcarpetas
 * @param string $objetivo La carpeta que sera eliminada
 */
function DeleteFolder($objetivo){
    $result = true;
    if(!ExisteCarpeta($objetivo)){
        return false;
    }

    $objetivo = EnsureTailingSlash($objetivo);
    foreach(glob($objetivo."*") as $value){
        //Solo me quedo con el final de la cadena luego del separador de directorio
        $tmp = explode(DS,$value);
        $tmp = array_pop($tmp);
        if(is_dir($value)){
            DeleteFolder($value);
            continue;
        }

        if(!$result = unlink($value)){
            break;
        }
    }

    if($result){
        rmdir($objetivo);
    }

    return $result;
}

/**
 * Toma un párrafo y pone mayúsculas después de un punto y aparte.
 */

function SetCase($texto, &$i) { 
	$arr = array(".",":"," ","\r","\n","\t");
	$fin = false;
	$char = mb_substr($texto,$i,1);
	$x = $i;
	$strlen = mb_strlen($texto);
	while (!$fin and ($x < $strlen)) {
		if (!in_array($char,$arr)) {
			$fin = true;
			$texto = mb_substr($texto,0,$x).mb_strtoupper($char).mb_substr($texto,$x+1);
			$i = $x;
		} else {
			$x++;
			$char = mb_substr($texto,$x,1);
		}
	}
	return $texto;
} // SetCase

/**
 * Tomo un texto y convierte la primera letra de cada palabra a Mayúscula
 * 
 */

function ArreglarMayusculas($texto) {
	mb_internal_encoding('UTF-8');
	$texto = mb_strtoupper(mb_substr($texto, 0, 1)) . mb_substr($texto, 1);
	$strlen = mb_strlen($texto);
	$arr = array(".",":","\r","\n");
	for($i = 0; $i<$strlen;$i++) {
		$char = mb_substr($texto,$i,1);
		if (in_array($char,$arr)) {
			$texto = SetCase($texto,$i);
		}
	}
	return $texto;
}

 /*
 * Summary. Obtiene un parametro coincidente con una lista de objetivos en formato string o array (2.0 :O)
 * @param mixed $targets Array con lista de keys a buscar, o un string separado por ","
 * @return mixed $result
 */
function FindParam($targets){
	if(is_string($targets)){
		$targets = explode(",",$targets);
	}

	if(!is_array($targets) or count($targets) == 0){ return null; }
	$targets = array_map("strtolower",$targets);

	$params = $_REQUEST ?? array();
	$body = file_get_contents("php://input");
	if(!empty($body)){
		$bodyData = array();
		if(IsJsonEx($body)){ $bodyData = json_decode($body,true); }

		if(empty($bodyData) OR !is_array($bodyData) OR count($bodyData) == 0){
			$bodyData = array();
			//Para procesar peticiones put....
			$tmp = explode("&",$body);
			foreach($tmp as $value){
				$tmp2 = explode("=",$value);
				$bodyData[$tmp2[0]] = $tmp2[1] ?? null;
			}
		}

		$params += (count($bodyData) > 0) ? $bodyData : array();
	}

	foreach($params as $key => $value){
		if(in_array(strtolower($key),$targets)){
			return $value;
		}
	}
}

/**
 * Summary. Comprueba que una variable tenga un valor bool válido, sin importar cual sea
 * @param mixed $valor
 * @return bool $result Si es un valor bool válido true, false en caso contrario
 */
function CheckBool($valor){
	return (in_array($valor,VALID_TRUE_VALUES) OR in_array($valor,VALID_FALSE_VALUES));
}

