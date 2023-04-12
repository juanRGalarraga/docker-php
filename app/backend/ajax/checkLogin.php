<?php
require_once(DIR_includes."class.checkinputs.inc.php");
require_once(DIR_model."usuarios".DS."class.usuarios_menu.inc.php");

$post = array('username'=>null,'password'=>null);
foreach($_POST as $key => $value) {
	if (array_key_exists($key,$post)) {
		$post[$key] = substr(trim($value),0,32);
	}
}

$msgerr = array();

if (empty(trim($post['username']))) {
	$msgerr['username'] = 'Ingresá nombre de usuario';
}
if (empty(trim($post['password']))) {
	$msgerr['password'] = 'Ingresá contraseña';
}
if (CanUseArray($msgerr) > 0) {
	EmitJSON($msgerr);
	return;
}

cCheckInput::Nick($post['username'],'username',"Nombre de usuario");
cCheckInput::StrongPassword($post['password'],'password',"Contraseña");

if (CanUseArray($msgerr) > 0) {
	EmitJSON($msgerr);
	return;
}

require_once(DIR_model."usuarios".DS."class.usuarios_backend.inc.php");
$usuario = new cUsrBackend();
if ($usuario->GetByUsername($post['username'])) {
	if ($usuario->ValidPass($post['password'])) {
		if ($usuario->Login()) {
			$menu = new cUsrMenu();
			$menu->SetUser($usuario->id);
			$menu->GenMenu();
			ResponseOk();
			cLogging::Write(__FILE__." ".__LINE__." ".$post['username']." ingresó correctamente.");
			return;
		} else {
			EmitJSON('Problemas al validar las credenciales<br/>Revisar registro de actividad.');
			cLogging::Write(__FILE__." ".__LINE__." ".$post['username']." no pudo ingresar correctamente.");
			return;
		}
	}else{
		EmitJSON('El password implementado no es el correcto.');
		cLogging::Write(__FILE__." ".__LINE__." ".$post['username']." ingreso un password incorrecto.");
		return;
	}
}
EmitJSON('Las credenciales no son correctas');
cLogging::Write(__FILE__." ".__LINE__." ".$post['username']." o el ".$post['password']." no son correctos.");
return;

?>