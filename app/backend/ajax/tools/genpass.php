<?php

$post = CleanArray($_POST);
$msgerr = [];

$username = $post['theUsername']??null;
$password = $post['thePassword']??null;

$username = substr(trim($username),0,32);
if (empty($username)) {
	$msgerr['theUsername'] = 'Falta indicar usuario';
}
$password = substr(trim($password),0,32);
if (empty($password)) {
	$msgerr['thePassword'] = 'Falta indicar password';
}

if (CanUseArray($msgerr)) {
	return EmitJSON($msgerr);
}

require_once(DIR_model."usuarios".DS."class.usuarios_backend.inc.php"); // Clase para manejar los usuarios del sistema.

$usuario_id = SecureInt($username,null);

$usuario = new cUsrBackend();

if (!is_null($usuario_id)) {
	$usuario->id = $usuario_id;
	if (!$usuario->Get()) {
		return EmitJSON("Usuario no encontrado.");
	}
} else {
	if (!$usuario->GetByUsername($username)) {
		return EmitJSON("Usuario no encontrado.");
	}
}


if ($usuario->SetNewPassword($password)) {
	EmitJSON('Guardada como: '.$usuario->password,false);
} else {
	EchoLog('Usuario con id '.$usuario_id.' no encontrado.');
}
$objeto_db->Disconnect();

//EmitJSON("Esto es el mensaje de error.");