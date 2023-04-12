<?php
/**
*	Devuelve un listado
*	Created: 
*	Author: Tom
*/

	$setup = json_decode(
<<<END
	{
	"descripcion":"Listado de los campos que se envían al cliente",
	"fields":[
		"`respuestas_aut`.`id`",
		"`respuestas_aut`.`tipo_id`",
		"`respuestas_aut`.`texto_clave`",
		"`respuestas_aut`.`mensaje`",
		"`respuestas_aut`.`estado`",
		"`respuestas_aut`.`sys_fecha_modif`",
		"`respuestas_aut`.`sys_fecha_alta`"
	]
}
END
);

	$campos_orden = ["id","sys_fecha_modif"];
	$campos_busqueda = ["`respuestas_aut`.`id`"];
	$novacias = $ws->GetParam(['novacia','novacias','noempty']);

	require_once(DIR_model."listados".DS."class.listado.inc.php");
	
    if(!$tipo_id = SecureInt($ws->GetParam(['id']))){ 
        cLogging::Write(__FILE__." ".__LINE__." No se indico el tipo de incidencia sobre la cual se quiere el listado ");
        $ws->SendResponse(500,' Tipo de incidencia no indicado '); return;
    }

	$buscar = $ws->GetParam(['buscar']);
	if(!is_null($buscar)){
		$buscar = mb_strtolower(mb_substr($buscar,0,25));
	}

    $estado = $ws->GetParam(['estado']);
	if(is_null($estado)){
		$estado = 'HAB';
	}

    $listado->ItemsPorPagina = 3;
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
	
	if (!$listado->GetSesValue('orden'))
		$listado->SetSesValue('orden', ["sys_fecha_modif"=>"DESC"]);
	
	$db = new cModels();

    
	$select = "SELECT ".implode(", ",$setup->fields);
	$from = "FROM ".SQLQuote(TBL_RA_incidencias)." AS `respuestas_aut` ";
	$join = "";
	$where = "WHERE 1=1 AND `tipo_id` = ".$tipo_id;
	if(!empty($buscar)){
		$where .= $listado->SetSearch($buscar, $campos_busqueda);
	}

    if(CanUseArray($estado)){
        $where .= " AND `respuestas_aut`.`estado` IN ('".implode("','",$estado)."') ";
	}else{
        $where .= " AND `respuestas_aut`.`estado` = '".$estado."'";
    }
    // Echolog($select.$from.$join.$where);
	$listado->SetSQL(implode(" ",[$select, $from, $join, $where]));
    Echolog($listado->sql);
	try {
		$ws->log_max_length = 1024;
		$ws->SendResponse(200, $listado->GetResult($db));
	
	} catch(Exception $e) {
		$ws->SendResponse(500, 'Ocurrió un error al acceder a la base de datos.');
	} finally {
		$db->Disconnect();
	}
