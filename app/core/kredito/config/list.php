<?php
	/**
	* Obtiene un listado de los registros
	* Created: 2021-10-25
	* Author: api_creator
	*/
	
	$campos_orden = ["nombre","valor","nombre_grupo"];
	$campos_busqueda = ["`parametros`.`nombre`","`parametros`.`valor`","`grupos`.`nombre`"];
	$grupo = SecureInt($ws->params['grupo']??null,null);
	
	require_once(DIR_model."listados".DS."class.listado.inc.php");
	
	$buscar = $ws->GetParam(['buscar']);
	if(!is_null($buscar)){
		$buscar = mb_strtolower(mb_substr($buscar,0,25));
	}
	if (!$listado->GetSesValue('search')){
		if(!empty($buscar)){
			$listado->SetSesValue('search', $buscar);
		}
	}
	
	if(empty($buscar) AND !is_null($buscar)){
		$listado->SetSesValue('search', "");
	}
	
	if(is_null($buscar) AND $tmp = $listado->GetSesValue('search')){
		$buscar = $tmp;
	}
	
	$db = new cModels();
	$select = "SELECT `parametros`.*, `grupos`.`nombre` AS `nombre_grupo`";
	$from = "FROM ".SQLQuote(TBL_parametros)." AS `parametros`,";
	$from .= SQLQuote(TBL_config_parametros_grupos)." AS `grupos`";
	$join = "";
	$where = "WHERE 1=1 ";
	$where .= "AND `grupos`.`id` = `parametros`.`grupo_id`";
	if ($grupo) {
		$where .= "AND `parametros`.`grupo_id` = ".$grupo;
	}
	if (!empty($buscar)) {
		/*
		$search = $db->RealEscape($buscar);
		$where .= sprintf(" AND ((`parametros`.`nombre` LIKE '%%%s%%') OR (`parametros`.`valor` LIKE '%%%s%%') OR (`grupos`.`nombre` LIKE '%%%s%%'))",$search,$search,$search);
		*/
		$where .= $listado->SetSearch($buscar, $campos_busqueda);
	}
	
	$listado->SetSQL(implode(" ",[$select, $from, $join, $where]));
	
	try {
		$ws->log_max_length = 1024;
		$ws->SendResponse(200, $listado->GetResult($db));
	
	} catch(Exception $e) {
		$ws->SendResponse(500, 'Ocurrió un error al acceder a la base de datos.');
	} finally {
		$db->Disconnect();
	}
