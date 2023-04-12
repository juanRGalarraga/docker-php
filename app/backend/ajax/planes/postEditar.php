<?php
/*
	Evaluación del formulario de edición de plan de préstamo.
*/

// ToDo: Hay que evaluar los permisos del usuario primero!

require_once(DIR_model."planes".DS."class.planes.inc.php");
require_once(DIR_model."negocios".DS."class.negocios.inc.php");
require_once(DIR_includes."class.checkinputs.inc.php");
require_once(DIR_includes."class.sidekick.inc.php");

$p = new cPlanes();
$tipos_pagos = $p->GetTiposPagos();
$negocios = new cNegocios;
$negocios = $negocios->GetListSimple();

$post = CleanArray($_POST);
unset($post[BASE_VPATH]);
unset($post['archivo']);
unset($post['content']);

if (!isset($post['id'])) {
	return EmitJSON('No se indicó ID');
}

// ID
$id = SecureInt($post['id'],null);
$new = (strtolower(substr($post['id'],0,3)) == 'new');
if (!$new) {
	if (empty($id)) {
		return EmitJSON('ID de plan no válido.');
	}
	if (!$p->GetAll($id)) {
		return EmitJSON('Plan no encontrado.');
	}
}

// Nombre del plan
$msgerr = array();
if (!CheckInt($post['selc_negocio'])) {
	$msgerr['selc_negocio'] = 'No es válido';
} else {
	if (!isset($negocios[$post['selc_negocio']])) {
		$msgerr['selc_negocio'] = 'Negocio no encontrado.';
	}
}
$post['name_negocio'] = SatinizeStr($post['name_negocio']??null, 255);
if (empty($post['name_negocio'])) {
	$msgerr['name_negocio'] = 'Nombre del plan es requerido.';
}
if (!empty($post['name_negocio']) and empty($post['alias_negocio'])) {
	$post['alias_negocio'] = cSideKick::GenerateAlias($post['name_negocio']);
}

$post['esdefault'] = (isset($post['esdefault']) and (strtoupper($post['esdefault']) == 'SI'));

// Estado
$post['state_negocio'] = strtoupper(substr($post['state_negocio']??null,0,3));
if (!isset(ESTADOS_VALIDOS[$post['state_negocio']])) {
	$msgerr['state_negocio'] = "Estado no válido.";
}

// Vigencia
$post['vig_des'] = substr($post['vig_des'],0,10);
$post['vig_has'] = substr($post['vig_has'],0,10);

cCheckInput::Fecha($post['vig_des'],'vig_des',"Fecha desde",'ISO');
cCheckInput::Fecha($post['vig_has'],'vig_has',"Fecha hasta",'ISO');

// Límites de montos.

if(isset($post['mnt_min'])){
	$post['mnt_min'] = str_replace(".","",$post['mnt_min']);
}

if(isset($post['mnt_max'])){
	$post['mnt_max'] = str_replace(".","",$post['mnt_max']);
}

$post['mnt_min'] = SecureInt(substr($post['mnt_min'],0,12),null);
$post['mnt_max'] = SecureInt(substr($post['mnt_max'],0,12),null);

if (empty($post['mnt_min'])) {
	$msgerr['mnt_min'] = 'Debe ingresar un mínimo';
} else {
	if ($post['mnt_min'] < 0) {
		$msgerr['mnt_min'] = 'No puede ser negativo';
	}
}

if (empty($post['mnt_max'])) {
	$msgerr['mnt_max'] = 'Debe ingresar un máximo';
} else {
	if ($post['mnt_max'] < 0) {
		$msgerr['mnt_max'] = 'No puede ser negativo';
	}
}

if (!empty($post['mnt_min']) and !empty($post['mnt_max'])) {
	if ($post['mnt_max'] < $post['mnt_min']) {
		$aux = $post['mnt_min'];
		$post['mnt_min'] = $post['mnt_max'];
		$post['mnt_max'] = $aux;
	}
}

// Tipo de plan
$post['tipo_plan'] = strtoupper(SatinizeStr($post['tipo_plan'],10));
if (!in_array($post['tipo_plan'],['PRESTAMO','REFIN'])) {
	$msgerr['tipo_plan'] = 'Tipo de plan no válido';
}

// Modalidad de otorgamiento.
$post['tipo_pagos'] = strtolower(SatinizeStr($post['tipo_pagos'],15));
if (!in_array($post['tipo_pagos'], $tipos_pagos->disponibles??null)) {
	$post['tipo_pagos'] = 'Modalidad no válida';
}

// Límites de plazos o cuotas.
$post['cto_min'] = SecureInt(substr($post['cto_min'],0,12),null);
$post['cto_max'] = SecureInt(substr($post['cto_max'],0,12),null);

if ($post['tipo_pagos'] != 'unico') {
		if (empty($post['cto_min'])) {
			$msgerr['cto_min'] = 'Debe ingresar un mínimo';
		} else {
			if ($post['cto_min'] < 1) {
				$msgerr['cto_min'] = 'No puede ser cero o negativo';
			}
		}
		if (empty($post['cto_max'])) {
			$msgerr['cto_max'] = 'Debe ingresar un máximo';
		} else {
			if ($post['cto_max'] < 1) {
				$msgerr['cto_max'] = 'No puede ser cero o negativo';
			}
		}
		if (!empty($post['cto_min']) and !empty($post['cto_max'])) {
			if ($post['cto_max'] < $post['cto_min']) {
				$aux = $post['cto_min'];
				$post['cto_min'] = $post['cto_max'];
				$post['cto_max'] = $aux;
			}
		}
} else {
	if (empty($post['plazo_min'])) {
		$msgerr['plazo_min'] = 'Debe ingresar un mínimo';
	} else {
		if ($post['plazo_min'] < 1) {
			$msgerr['plazo_min'] = 'No puede ser cero o negativo';
		}
	}
	if (empty($post['plazo_max'])) {
		$msgerr['plazo_max'] = 'Debe ingresar un máximo';
	} else {
		if ($post['plazo_max'] < 1) {
			$msgerr['plazo_max'] = 'No puede ser cero o negativo';
		}
	}
	if (!empty($post['plazo_min']) and !empty($post['plazo_max'])) {
		if ($post['plazo_max'] < $post['plazo_min']) {
			$aux = $post['plazo_min'];
			$post['plazo_min'] = $post['plazo_max'];
			$post['plazo_max'] = $aux;
		}
	}
}


// Período de gracia.
if (!empty($post['periodos_gracia'])) {
	$post['periodos_gracia'] = SecureInt($post['periodos_gracia'], null);
	if (empty($post['periodos_gracia'])) {
		$msgerr['periodos_gracia'] = "Valor no válido";
	} else {
		if ($post['periodos_gracia'] < 0) {
			$msgerr['periodos_gracia'] = "No debe ser negativo.";
		}
	}
}

// Porcentajes iniciales.
$post['porc_ini_minimo'] = SecureInt(substr($post['porc_ini_minimo'],0,12),null);
$post['porc_ini_maximo'] = SecureInt(substr($post['porc_ini_maximo'],0,12),null);

if (!is_null($post['porc_ini_minimo']) and $post['porc_ini_minimo'] < 0) {
	$msgerr['porc_ini_minimo'] = 'No puede ser negativo';
}

if (!is_null($post['porc_ini_maximo']) and ($post['porc_ini_maximo'] < 0)) {
	$msgerr['porc_ini_maximo'] = 'No puede ser negativo';
}


if (!empty($post['porc_ini_minimo']) and !empty($post['porc_ini_maximo'])) {
	if ($post['porc_ini_maximo'] < $post['porc_ini_minimo']) {
		$aux = $post['porc_ini_minimo'];
		$post['porc_ini_minimo'] = $post['porc_ini_maximo'];
		$post['porc_ini_maximo'] = $aux;
	}
}

// scores
$post['scr_min'] = SecureInt(substr($post['scr_min'],0,5),null);
$post['scr_max'] = SecureInt(substr($post['scr_max'],0,5),null);

if (!is_null($post['scr_min']) and $post['scr_min'] < 0) {
	$msgerr['scr_min'] = 'No puede ser negativo';
}

if (!is_null($post['scr_max']) and ($post['scr_max'] < 0)) {
	$msgerr['scr_max'] = 'No puede ser negativo';
}


if (!empty($post['scr_min']) and !empty($post['scr_max'])) {
	if ($post['scr_max'] < $post['scr_min']) {
		$aux = $post['scr_min'];
		$post['scr_min'] = $post['scr_max'];
		$post['scr_max'] = $aux;
	}
}

// Edades
$post['edad_min'] = SecureInt(substr($post['edad_min'],0,5),null);
$post['edad_max'] = SecureInt(substr($post['edad_max'],0,5),null);

if (!is_null($post['edad_min']) and $post['edad_min'] < 0) {
	$msgerr['edad_min'] = 'No puede ser negativo';
}

if (!is_null($post['edad_max']) and ($post['edad_max'] < 0)) {
	$msgerr['edad_max'] = 'No puede ser negativo';
}


if (!empty($post['edad_min']) and !empty($post['edad_max'])) {
	if ($post['edad_max'] < $post['edad_min']) {
		$aux = $post['edad_min'];
		$post['edad_min'] = $post['edad_max'];
		$post['edad_max'] = $aux;
	}
}

$post['ts_nm_anual'] = SecureFloat(ParseFloat($post['ts_nm_anual']),null);
if (is_null($post['ts_nm_anual'])) {
	$msgerr['ts_nm_anual'] = "No es un número válido";
} else {
	if($post['ts_nm_anual'] < 0) {
		$msgerr['ts_nm_anual'] = "No debe ser negativo";
	}
}

$post['mnt_fianza'] = SecureFloat(ParseFloat($post['mnt_fianza']),null);
if (is_null($post['mnt_fianza'])) {
	$msgerr['mnt_fianza'] = "No es un número válido";
} else {
	if($post['mnt_fianza'] < 0) {
		$msgerr['mnt_fianza'] = "No debe ser negativo";
	}
}

$post['etiq_tasa_anual'] = SatinizeStr($post['etiq_tasa_anual'],64);
$post['valor_tasa_anual'] = SatinizeStr($post['valor_tasa_anual'],12);
$post['etiq_cost_admin'] = SatinizeStr($post['etiq_cost_admin'],64);
$post['valor_cost_admin'] = SatinizeStr($post['valor_cost_admin'],12);

if (CanUseArray($msgerr)) {
	return EmitJSON($msgerr);
}

if ($new) {
	$p->Post($post);
} else {
	$p->Put($id, $post);
}

ResponseOk(['leave'=>$new]);
