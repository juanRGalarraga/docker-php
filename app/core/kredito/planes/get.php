<?php
/*
	Devolver los datos de un plan, para el cotizador
*/

require_once(DIR_model."planes".DS."class.planes.inc.php");

$planes = new cPlanes();

/*
	Esto debe funcionar así.
	Si se pasa el parámetro ID, se toma ese plan y listo.
	Si no hay parámetro ID, entonces se busca el plan por omisión del negocio indicado por...
	... El parámetro en la URL ?negocio=<id>
	Si no está, entonces el negocio_id indicado en los headers de la petición...
	Si no está, entonces el negocio_id del usuario del web services.
	Si no está, fallar.
*/

$id = $ws->GetParam('id');

if (!empty($id) and $salida = $planes->Get($id)) {
	return EmitirPlan($salida);
}

$negocio_id = $ws->GetParam(['negocio','negocio_id','negocioid','bussinessid','idnegocio']);
if (!empty($negocio_id) and ($salida = $planes->GetDefault($negocio_id))) {
	return EmitirPlan($salida);
}

$negocio_id = SecureInt($ws->GetHeader('negocio-id'),null);
if (!empty($negocio_id) and ($salida = $planes->GetDefault($negocio_id))) {
	return EmitirPlan($salida);
}

$negocio_id = SecureInt($ws->usuario->negocio_id,null);
if (!empty($negocio_id) and ($salida = $planes->GetDefault($negocio_id))) {
	return EmitirPlan($salida);
}

return $ws->SendResponse(404,"Plan no encontrado");

/**
* Summary. Construye la respuesta para el cliente.
* @param object $salida.
*/
function EmitirPlan($salida) {
	global $ws;
	if ($salida->estado != 'HAB') {
		return $ws->SendResponse(404, 'Plan está deshabilitado.');
	}
	if ($salida->vigencia_desde > cFechas::Hoy()) {
		return $ws->SendResponse(404, 'Plan no está en vigencia.');
	}
	if ($salida->vigencia_hasta < cFechas::Hoy()) {
		return $ws->SendResponse(404, 'Plan ya no está en vigencia.');
	}
	$data = new stdClass();
	$data->plan_id = $salida->id??null;
	
	$data->negocioId = $salida->negocio_id;
	$data->nombreComercial = $salida->nombre_comercial;
	$data->alias = $salida->alias;

	$data->montoMinimo = $salida->monto_minimo;
	$data->montoMaximo = $salida->monto_maximo;

	$data->montoMinimoDisplay = number_format($salida->monto_minimo,0,',','.');
	$data->montoMaximoDisplay = number_format($salida->monto_maximo,0,',','.');

	$data->plazoMinimo = $salida->plazo_minimo;
	$data->plazoMaximo = $salida->plazo_maximo;

	$data->tipoMoneda = $salida->tipo_moneda;
	$data->simbolo = "$";
	$data->esDefault = $salida->esdefault;
	$data->Estado = $salida->estado;
	
	$data->vigenciaDesde = $salida->vigencia_desde;
	$data->vigenciaHasta = $salida->vigencia_hasta;
	
	if (!empty($salida->data->step))
		$data->step = $salida->data->step;
	
	if (!empty($salida->data->TNA_Publico)) 
		$data->Etiqueta1 = @$salida->data->TNA_Publico->etiqueta;
	if (!empty($salida->data->Costo_Publico))
		$data->Etiqueta2 = @$salida->data->Costo_Publico->etiqueta;

	$ws->SendResponse(200, $data);
}
