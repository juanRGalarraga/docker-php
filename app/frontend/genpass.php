<?php
require_once('initialize.php');
require_once(DIR_config.'config.inc.php');
require_once(DIR_includes."common.inc.php"); // Contiene las funciones comunes.
require_once(DIR_includes."class.fechas.inc.php"); // Funciones de tratamiento de fechas y horas.
require_once(DIR_model.'class.dbutili.2.inc.php'); // Clase base para el manejo de la base de datos.
require_once(DIR_model."usuarios".DS."class.usuarios_backend.inc.php"); // Clase para manejar los usuarios del sistema.

$objeto_db = new cDb();
$objeto_db->Connect(DBHOST, DBNAME, DBUSER, DBPASS, DBPORT);
if ($objeto_db->error) {
	Ends($objeto_db->errmsg);
	exit;
}

if (!isset($argv[1])) {
	Ends('Parámetro requerido no fue indicado.');
	exit;
}
if (!isset($argv[2])) {
	Ends('Falta indicar password.');
	exit;
}

$usuario_id = SecureInt($argv[1],null);
$password = substr($argv[2],0,32);

$usuario = new cUsrBackend();



if ($usuario->Get($usuario_id)) {
	$usuario->SetNewPassword($password);
	Ends('Guardada como: '.$usuario->newpassword);
} else {
	Ends('Usuario con id '.$usuario_id.' no encontrado.');
}



Ends('Hecho.');

$objeto_db->Disconnect();

function Ends($msg = null) {
	if (!empty($msg)) {
		EchoLog(FDL.Date('Y-m-d H:i:s')." ".$msg);
	} else {
	EchoLog(FDL.Date('Y-m-d H:i:s'));
	}
}
?>