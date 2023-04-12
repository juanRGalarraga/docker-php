<?php
/**
	Devolver solo los datos básicos del usuario pedido.
	Created: 2021-10-30
	Author: DriverOp
*/


$user = $ws->params['user'];
if (empty($user)) {
	return $ws->SendResponse(400, "Usuario ID o nick es requerido.");
}

require_once(DIR_model."usuarios".DS."class.usuarios_backend.inc.php");

$usuario = new cUsrBackend;

$user = substr($user,0,32);

if (CheckInt($user)) {
	$usuario->id = $user;
	$datos = $usuario->Get();
} else {
	$datos = $usuario->GetByUsername($user);
}
if (empty($datos)) {
	return $ws->SendResponse(404, "Usuario no encontrado.");
}

$respuesta = new stdClass;
$respuesta->id = $datos->id;
$respuesta->username = $datos->username;
$respuesta->nombre = $datos->nombre;
$respuesta->apellido = $datos->apellido;
$respuesta->estado = $datos->estado;
$respuesta->nivel = $datos->nivel;
$respuesta->sesionAbierta = $usuario->esta_logueado;


$ws->SendResponse(200, $respuesta);