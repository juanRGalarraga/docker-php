<?php
/*
	Buscar y devolver datos de un DNI
	Created: 2021-10-14
	Author: DriverOp
*/

require_once(DIR_includes."class.checkinputs.inc.php");

$dni = $ws->params['dni']??null;
$nombre = $ws->params['nombre']??null;
$apellido = $ws->params['apellido']??null;


if (empty($dni)) {
	return $ws->SendResponse(400, ['dni'=>'Es requerido'],11);
}

if (!cCheckInput::DNI($dni)) {
	return $ws->SendResponse(406, ['dni'=>'No es válido'],11);
}

/*
	Hay tres cosas para hacer antes de ir al buró.
	1.- Si el DNI está en la ban list, si es así fallar y salir.
	2.- Si el DNI ya está en la tabla de personas, recuperar el score de allí, si el score no ha pasado los 30 días desde la última revisión, devolver esos datos, caso contrario, tratar como el caso 3.
	3.- Ir al buró a buscar la info. Con lo que el buró devuelva, actualizar el score de la persona si la persona existe.
*/

$dias_renovación_score = $sysParams->Get('dias_renovacion_score',30);

// 1.- Si la ban list está habilitada para usarse...
if ($sysParams->Get('use_ban_list',false)) {
	
	require_once(DIR_model."banlist".DS."class.banList.inc.php");
	$banlist = new cBanList;


	if ($banlist->GetByTipo('DNI', $dni)) {
		return $ws->SendResponse(403, 'DNI está en la lista de exclusión',60);
	}
} // use_ban_list


require_once(DIR_model."personas".DS."class.personas.inc.php");
require_once(DIR_model."scores".DS."class.scores.inc.php");

$persona = new cPersonas;
$scores = new cScores;

// 2.- ¿Ya existe la persona?
if ($data = $persona->GetByDNI($dni)) {
	$score = $scores->GetScore($persona->id);
	// ¿Tiene score y no ha expirado?.
	if (!is_null($score) and (cFechas::DiasEntreFechas($scores->fecha_request??cFechas::Hoy(), cFechas::Hoy()) <= $dias_renovación_score)) {
		$data->score = $score;
		$data = TranslateField($data);
		$data->esCliente = 'SI';
		return $ws->SendResponse(200,$data);
	}
}

// 3.- Vamos a preguntarle al buró.

require_once(DIR_integraciones."fakeburo".DS."class.fakeburo.inc.php");
$fakeburo = new cFakeburo(null, null);

$data = $fakeburo->GetScore($dni);
if (!$data) {
	return $ws->SendResponse(500,'El buro no respondió apropiadamente');
}

if (isset($data->calle) or isset($data->nro)) {
	$data->direccion = new stdClass;
	$data->direccion->calle = $data->calle??null;
	$data->direccion->nro = $data->nro??null;
}

$data = TranslateField($data);

$data->esCliente = ($persona->existe)?'SI':'NO';
$ws->SendResponse(200,$data);

if (!$persona->existe) { return; } // Si la persona no existía, salir.

// Actualizar el score de la persona.

$scores->Create([
	"persona_id"=>$persona->id,
	"score"=>$data->Score,
	"buro_id"=>1,
	"data"=>json_encode($fakeburo->parsed_response, JSON_HACELO_BONITO)
]);
