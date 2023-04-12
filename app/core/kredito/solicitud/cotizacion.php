<?php
/*
	Preparar la cotización del préstamo según los datos enviados por el cliente y el plan almacenado en la solicitud.
	Devuelve la misma respuesta que la API de simulate.
	
	Created: 2021-10-06
	Author: DriverOp
*/

require_once(DIR_model."planes".DS."class.planes.inc.php");
require_once(DIR_model."simulador".DS."class.simulador.inc.php");

function ActualizarCotizacion() {

	global $ws;
	global $ws_usuario;
	global $solicitud;

	$datosSolicitud = $solicitud->Get();

	$cotizacion = new stdClass;

	$cotizacion->capital = $ws->GetParam(['monto','capital','total']);
	if (empty($cotizacion->capital)) {
		$cotizacion->capital = $datosSolicitud->data->capital??null;
	}
	$cotizacion->plazo = $ws->GetParam(['plazo']);
	if (empty($cotizacion->plazo)) {
		$cotizacion->plazo = $datosSolicitud->data->plazo??null;
	}
	$cotizacion->plan = $datosSolicitud->data->plan??null;

	$planes = new cPlanes();

	if ($cotizacion->plan != 0) {
		if (!$plan = $planes->Get($cotizacion->plan)) {
			return $ws->SendResponse(404, 'Plan no encontrado.');
		}
	} else {
		if (!$plan = $planes->GetDefault($ws_usuario->negocio_id)) {
			return $ws->SendResponse(404, 'Plan no encontrado.');
		}
		$cotizacion->plan = $plan->id;
	}

	if (empty($cotizacion->capital)) {
		$cotizacion->capital = $plan->monto_minimo+(($plan->monto_maximo-$plan->monto_minimo)/2);
	} else {
		if (!CheckFloat($cotizacion->capital)) {
			$msgerr['monto'] = 'Monto no es un número.';
		} else {
			$cotizacion->capital = ((float)$cotizacion->capital)*1;
		}
	}
	if (empty($cotizacion->plazo) or (!CheckInt($cotizacion->plazo))) {
		$cotizacion->plazo = floor($plan->plazo_minimo+(($plan->plazo_maximo-$plan->plazo_minimo)/2));
	} else {
		$cotizacion->plazo = ((int)$cotizacion->plazo)*1;
	}
	if ($cotizacion->capital > $plan->monto_maximo) {
		$cotizacion->capital = $plan->monto_maximo;
	}

	if ($cotizacion->capital < $plan->monto_minimo) {
		$cotizacion->capital = $plan->monto_minimo;
	}

	if ($cotizacion->plazo > $plan->plazo_maximo) {
		$cotizacion->plazo = $plan->plazo_maximo;
	}

	if ($cotizacion->plazo < $plan->plazo_minimo) {
		$cotizacion->plazo = $plan->plazo_minimo;
	}

	$simulador = new cSimulador($planes);
	$simulador->cantDecs = 0;

	if (!$simulador->Calcular(['monto'=>$cotizacion->capital, 'plazo'=>$cotizacion->plazo, 'plan'=>$plan])) {
		cLogging::Write(__FILE__ ." ".__LINE__ ." El cálculo de la cotización no devolvió resultados.");
	} else {
		$cotizacion = $simulador->GenerarSalida('request');
	}
	return $cotizacion;

}