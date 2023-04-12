<?php
/**
*	Devuelve un listado 
*	Created:
*	Author: 
*/

	$setup = json_decode(
<<<END
	{
	"descripcion":"Listado de los campos que se envían al cliente",
	"fields":[
		"`mesa_incidencias`.`id`",
		"`mesa_incidencias`.`persona_id`",
		"`mesa_incidencias`.`usuarios`",
		"`mesa_incidencias`.`tipo_id`",
		"`mesa_incidencias`.`asunto`",
		"`mesa_incidencias`.`prioridad`",
		"`mesa_incidencias`.`estado`",
		"concat_ws(' ',`personas`.`nombre`,`personas`.`apellido`) as 'nom_ape' ",
		"`mesa_incidencias`.`sys_fecha_modif`",
		"`mesa_incidencias`.`sys_fecha_alta`"
	]
}
END
);

	$campos_orden = ["id","sys_fecha_modif"];
	$campos_busqueda = ["`mesa_incidencias`.`id`"];
	$novacias = $ws->GetParam(['novacia','novacias','noempty']);

	require_once(DIR_model."listados".DS."class.listado.inc.php");
	$prioridad = false;
	$buscar = $ws->GetParam(['buscar']);
	if(!is_null($buscar)){
		$buscar = mb_strtolower(mb_substr($buscar,0,25));
	}

    $estado = $ws->GetParam(['estado']);
	if(is_null($estado)){
		$estado = 'HAB';
	}
	

	$usuario_id = $ws->GetParam(['usuario_id']);


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
	$from = "FROM ".SQLQuote(TBL_mesa_incidencia)." AS `mesa_incidencias` , ".SQLQuote(TBL_personas)." AS `personas` ";
	$join = "";
	$where = "WHERE 1=1 AND `mesa_incidencias`.`persona_id` = `personas`.`id` ";
	
	if(!empty($usuario_id)){ 
		$where .=" AND JSON_SEARCH(usuarios,'one',".$usuario_id.") IS NOT NULL ";
	}
	
	if(!empty($prioridad)){ 
		if(CanUseArray($prioridad)){
        	$where .= " AND `mesa_incidencias`.`prioridad` IN ('".implode("','",$prioridad)."') ";
		}else{
        	$where .= " AND `mesa_incidencias`.`prioridad` = '".$prioridad."'";
		}
	}

	if(!empty($buscar)){
		$where .= $listado->SetSearch($buscar, $campos_busqueda);
	}

    // if(CanUseArray($estado)){
    //     $where .= " AND `mesa_incidencias`.`estado` IN ('".implode("','",$estado)."') ";
	// }else{
    //     $where .= " AND `mesa_incidencias`.`estado` = '".$estado."'";
    // }
	$listado->SetSQL(implode(" ",[$select, $from, $join, $where]));

	try {
		$ws->log_max_length = 1024;
		$ws->SendResponse(200, $listado->GetResult($db));
	
	} catch(Exception $e) {
		$ws->SendResponse(500, 'Ocurrió un error al acceder a la base de datos.');
	} finally {
		$db->Disconnect();
	}
