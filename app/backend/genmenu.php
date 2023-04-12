<?php
/*
	Generar o regenear el menú principal para un usuario.
*/
define("DEVELOPE_NAME","metropol");
require_once('initialize.php');
require_once(DIR_config.'config.inc.php');
require_once(DIR_includes."common.inc.php"); // Contiene las funciones comunes.
require_once(DIR_includes."class.fechas.inc.php"); // Funciones de tratamiento de fechas y horas.
require_once(DIR_model.'class.fundation.inc.php'); // Clase base para el manejo de la base de datos.
require_once(DIR_model."usuarios".DS."class.usuarios_backend.inc.php"); // Clase para manejar los usuarios del sistema.
require_once(DIR_model."usuarios".DS."class.usuarios_menu.inc.php"); // Clase para manejar el menú principal de los usuarios del sistema.


if (!isset($argv[1])) {
	EchoLog('Parámetro requerido no fue indicado.');
	exit;
}

$usuario = new cUsrBackend();

if (is_numeric($argv[1])) {
	$usuario->id = SecureInt($argv[1],null);
	$salida = $usuario->Get();
} else {
	$salida = $usuario->GetByUsername(substr($argv[1],0,32));
}
if (is_null($salida)) {
	EchoLog(CLI_COLORES['Rojo claro'].'Usuario no encontrado.'.CLI_COLORES['Ninguno']);
	exit;
}

EchoLog('Generando menú estático para usuario '.$salida->username.'...');


$menu = new cUsrMenu();
$menu->SetUser($usuario->id);
if (!$menu->GenMenu()) {
	EchoLog(CLI_COLORES['Rojo claro'].'No se pudo generar el menú.'.CLI_COLORES['Ninguno']);
	exit;
}
EchoLog(CLI_COLORES['Verde claro'].'Menú generado correctamente.'.CLI_COLORES['Ninguno']);


