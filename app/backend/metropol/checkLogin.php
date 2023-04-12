<?php
/*
	Verifica que los datos de logging del cliente sean correctos.
	Created: 2021-05-10
	Author: DriverOp
*/



$paramUser = ['user','username','usuario'];
$paramPass = ['pass','password','contraseña','clave'];

$usuario = $ws->GetParam($paramUser);
$contraseña = $ws->GetParam($paramPass);

if (empty($usuario) or empty($contraseña)) {
	$ws->SendResponse(401,'No se recibió usuario o contraseña',2); return;
}

if (!$ws->usuario->GetByUsername($usuario)) {
	$ws->SendResponse(401,'1.Usuario o contraseña incorrectos',3); return;
}

if (!$ws->usuario->ValidPass($contraseña)) {
	$ws->SendResponse(401,'2.Usuario o contraseña incorrectos',3); return;
}

$ws->SendResponse(200,['token'=>$ws_usuario->MakeToken(),'expire'=>$ws_usuario->expireTime]);
