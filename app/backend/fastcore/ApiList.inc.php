<?php
/*
	Lista de APIs atendidas por el Web Service de Becas.
	Created: 2021-05-04
	Author: DriverOp
*/

const _apiAuth = array(
	'GET'=>'checkLogin',
	'POST'=>'checkLogin',
	'restricted'=>false
);

const _personas = array(
	'baseDir'=>'personas',
	'GET'=>'verPersona',
	'restricted'=>false
);


const _solicitudes = array(
	'baseDir'=>'solicitudes',
	'GET'=>'listado',
	'restricted'=>false
);

const _SolicitudesPersona = array(
	'baseDir'=>'personas',
	'GET'=>'listadoSolicitudes',
	'restricted'=>false
);


//Routes
const CONTENT_LIST = array(
	// Auth
	'auth/{username}?/{password}?'=>_apiAuth,
	
	'solicitudes/?'=>_solicitudes,
	'solicitudes/{sarasa}/{firulete}?/?'=>_solicitudes,
	'persona/{id}:int/?'=>_personas,
	'persona/{id}:int/solicitudes/?'=>_personas,
	'persona/sarasa/firulete/?'=>_personas,
	'persona/solicitudes/{id}:int/?' => _SolicitudesPersona,

);

