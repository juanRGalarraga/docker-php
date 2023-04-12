<?php
/***
 * Carga los filtros para utilziar en el listado de préstamos
 * Created: 2021-11-05
 * Author: Gastón Fernandez
 */
	require_once(DIR_model."configuraciones".DS."class.parametros.inc.php");
	require_once(DIR_model."planes".DS."class.planes.inc.php");
	$params = new cParametros;
	$planes = new cPlanes;

	$estados = ESTADOS_PRESTAMOS;
	$mora_filters = $params->GetMoraFilters();
	$listado = $planes->GetListado([]);
	$plan_filters = array();
	if(CanUseArray($listado)){
		foreach($listado as $key => $value){
			$plan_filters[] = array(
				'id' => $value->id,
				'nombre' => $value->nombre_comercial
			);
		}
	}