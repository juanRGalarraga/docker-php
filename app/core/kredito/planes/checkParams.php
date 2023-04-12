<?php
/*
	Verifica la validez de los datos enviados por el cliente.
	Esto se usa en put.php y post.php
*/

if (empty($plan->data)) {
	$plan->data = new stdClass;
}
if (!is_object($plan->data)) {
	$plan->data = new stdClass;
}

if (isset($ws->params['selc_negocio'])) {
	if (CheckInt($ws->params['selc_negocio'])) {
		if (!$negocios->Get($ws->params['selc_negocio'])) {
			$msgerr['negocio'] = 'No encontrado';
		} else {
			$plan->negocio_id = $ws->params['selc_negocio'];
		}
	}
}

if (isset($ws->params['name_negocio']) and !empty($ws->params['name_negocio'])) {
	if (!SameStr($ws->params['name_negocio'], $plan->nombre_comercial??null)) {
		$plan->nombre_comercial = mb_substr($ws->params['name_negocio'],0,255);
	}
}

if (isset($ws->params['alias_negocio'])) {
	if (!SameStr($ws->params['alias_negocio'], $plan->alias_negocio??null)) {
		$plan->alias = mb_substr($ws->params['alias_negocio'],0,255);
	}
}

if (isset($ws->params['state_negocio'])) {
	if (isset(ESTADOS_VALIDOS[strtoupper($ws->params['state_negocio'])])) {
		$plan->estado = strtoupper($ws->params['state_negocio']);
	}
}

if (isset($ws->params['esdefault'])) {
	$plan->esdefault = $ws->params['esdefault'];
}

if (isset($ws->params['vig_des'])) {
	if (cFechas::LooksLikeISODate($ws->params['vig_des']) and cFechas::IsValidISODate($ws->params['vig_des'])) {
		$plan->vigencia_desde = $ws->params['vig_des'];
	}
	if (empty($ws->params['vig_des'])) { $plan->vigencia_desde = null; }
}

if (isset($ws->params['vig_has'])) {
	if (cFechas::LooksLikeISODate($ws->params['vig_has']) and cFechas::IsValidISODate($ws->params['vig_has'])) {
		$plan->vigencia_hasta = $ws->params['vig_has'];
	}
	if (empty($ws->params['vig_has'])) { $plan->vigencia_hasta = null; }
}

if (isset($ws->params['mnt_min'])) {
	$plan->monto_minimo = ParseFloat($ws->params['mnt_min']);
}

if (isset($ws->params['mnt_max'])) {
	$plan->monto_maximo = ParseFloat($ws->params['mnt_max']);
}

if (isset($ws->params['tipo_plan'])) {
	if (in_array(strtoupper($ws->params['tipo_plan']),['PRESTAMO','REFIN'])) {
		$plan->tipo = strtoupper($ws->params['tipo_plan']);
	}
}

if (isset($ws->params['tipo_pagos'])) {
	if (isset($tipos_pagos->disponibles) and is_array($tipos_pagos->disponibles)) {
		if (in_array(strtolower($ws->params['tipo_pagos']), $tipos_pagos->disponibles)) {
			$plan->tipo_pago = strtolower($ws->params['tipo_pagos']);
		}
	}
}

if (isset($ws->params['plazo_min'])) {
	$ws->params['plazo_min'] = SecureInt($ws->params['plazo_min'],null);
	if ($plan->plazo_minimo != $ws->params['plazo_min']) {
		$plan->plazo_minimo = $ws->params['plazo_min'];
	}
}

if (isset($ws->params['plazo_max'])) {
	$ws->params['plazo_max'] = SecureInt($ws->params['plazo_max'],null);
	if ($plan->plazo_maximo != $ws->params['plazo_max']) {
		$plan->plazo_maximo = $ws->params['plazo_max'];
	}
}

if (isset($ws->params['cto_min'])) {
	$ws->params['cto_min'] = SecureInt($ws->params['cto_min'],null);
	if ($plan->plazo_minimo != $ws->params['cto_min']) {
		$plan->plazo_minimo = $ws->params['cto_min'];
	}
}

if (isset($ws->params['cto_max'])) {
	$ws->params['cto_max'] = SecureInt($ws->params['cto_max'],null);
	if ($plan->plazo_maximo != $ws->params['cto_max']) {
		$plan->plazo_maximo = $ws->params['cto_max'];
	}
}

if (isset($ws->params['periodos_gracia'])) {
	if (isset($plan->data)) {
		if ($plan->data->gracia??null != $ws->params['periodos_gracia']) {
			$plan->data->gracia = $ws->params['periodos_gracia'];
		}
	}
}

if (isset($ws->params['caida'])) {
	if (isset($plan->data)) {
		if ($plan->data->caida??null != $ws->params['caida']) {
			$plan->data->caida = $ws->params['caida'];
		}
	}
}
 
if (isset($ws->params['scr_min'])) {
	$ws->params['scr_min'] = SecureInt($ws->params['scr_min'],null);
	if ($plan->score_minimo != $ws->params['scr_min']) {
		$plan->score_minimo = $ws->params['scr_min'];
	}
}

if (isset($ws->params['scr_max'])) {
	$ws->params['scr_max'] = SecureInt($ws->params['scr_max'],null);
	if ($plan->score_maximo != $ws->params['scr_max']) {
		$plan->score_maximo = $ws->params['scr_max'];
	}
}

if (isset($ws->params['edad_min'])) {
	$ws->params['edad_min'] = SecureInt($ws->params['edad_min'],null);
	if ($plan->data->edad_min??null != $ws->params['edad_min']) {
		$plan->data->edad_min = $ws->params['edad_min'];
	}
	$plan->data = $plan->data;
}

if (isset($ws->params['edad_max'])) {
	$ws->params['edad_max'] = SecureInt($ws->params['edad_max'],null);
	if ($plan->data->edad_max??null != $ws->params['edad_max']) {
		$plan->data->edad_max = $ws->params['edad_max'];
	}
	$plan->data = $plan->data;
}

if (isset($ws->params['ts_nm_anual'])) {
	$ws->params['ts_nm_anual'] = ParseFloat($ws->params['ts_nm_anual']);
	if ($plan->tasa_nominal_anual != $ws->params['ts_nm_anual']) {
		$plan->tasa_nominal_anual = $ws->params['ts_nm_anual'];
	}
}

if (isset($ws->params['mnt_fianza'])) {
	$ws->params['mnt_fianza'] = ParseFloat($ws->params['mnt_fianza']);
	if ($plan->monto_fianza != $ws->params['mnt_fianza']) {
		$plan->monto_fianza = $ws->params['mnt_fianza'];
	}
}

if (isset($ws->params['cargos']) and is_array($ws->params['cargos'])) {
	$cargosImp = array_merge($cargosImp,$ws->params['cargos']);
}

if (isset($ws->params['impuestos']) and is_array($ws->params['impuestos'])) {
	$cargosImp = array_merge($cargosImp,$ws->params['impuestos']);
}

$plan->SetCargosImp($cargosImp);

if (isset($ws->params['etiq_tasa_anual']) or isset($ws->params['valor_tasa_anual']) and !isset($plan->data->TNA_Publico)) { $plan->data->TNA_Publico = new stdClass; }

if (isset($ws->params['etiq_tasa_anual'])) {
	$plan->data->TNA_Publico->etiqueta = SatinizeStr($ws->params['etiq_tasa_anual'], 65);
	$plan->data = $plan->data;
}

if (isset($ws->params['valor_tasa_anual'])) {
	$plan->data->TNA_Publico->valor = SatinizeStr($ws->params['valor_tasa_anual'], 65);
	$plan->data = $plan->data;
}

if (isset($ws->params['etiq_cost_admin']) or isset($ws->params['valor_cost_admin']) and !isset($plan->data->Costo_Publico)) { $plan->data->Costo_Publico = new stdClass; }
if (isset($ws->params['etiq_cost_admin'])) {
	$plan->data->Costo_Publico->etiqueta = SatinizeStr($ws->params['etiq_cost_admin'], 65);
	$plan->data = $plan->data;
}

if (isset($ws->params['valor_cost_admin'])) {
	$plan->data->Costo_Publico->valor = SatinizeStr($ws->params['valor_cost_admin'], 65);
	$plan->data = $plan->data;
}

