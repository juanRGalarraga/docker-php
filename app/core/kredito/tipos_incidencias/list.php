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
		"`tipos_incidencias`.`id`",
		"`tipos_incidencias`.`nombre`",
		"`tipos_incidencias`.`sitio`",
		"`tipos_incidencias`.`estado`",
		"`tipos_incidencias`.`sys_fecha_modif`",
		"`tipos_incidencias`.`sys_fecha_alta`"
	]
}
END
);

	$campos_orden = ["id","sys_fecha_modif"];
	$campos_busqueda = ["`tipos_incidencias`.`id`"];
	$novacias = $ws->GetParam(['novacia','novacias','noempty']);

	require_once(DIR_model."listados".DS."class.listado.inc.php");
	
	$buscar = $ws->GetParam(['buscar']);
	if(!is_null($buscar)){
		$buscar = mb_strtolower(mb_substr($buscar,0,25));
	}

    $estado = $ws->GetParam(['estado']);
	if(is_null($estado)){
		$estado = 'HAB';
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
	
	if (!$listado->GetSesValue('orden'))
		$listado->SetSesValue('orden', ["sys_fecha_modif"=>"DESC"]);
	
	$db = new cModels();

	$select = "SELECT ".implode(", ",$setup->fields);
	$from = "FROM ".SQLQuote(TBL_tipo_incidencias)." AS `tipos_incidencias` ";
	$join = "";
	$where = "WHERE 1=1 ";
	if(!empty($buscar)){
		$where .= $listado->SetSearch($buscar, $campos_busqueda);
	}

    if(CanUseArray($estado)){
        $where .= " AND `tipos_incidencias`.`estado` IN ('".implode("','",$estado)."') ";
	}else{
        $where .= " AND `tipos_incidencias`.`estado` = '".$estado."'";
    }
    // Echolog($select.$from.$join.$where);
	$listado->SetSQL(implode(" ",[$select, $from, $join, $where]));

	try {
		$ws->log_max_length = 1024;
		$ws->SendResponse(200, $listado->GetResult($db));
	
	} catch(Exception $e) {
		$ws->SendResponse(500, 'Ocurrió un error al acceder a la base de datos.');
	} finally {
		$db->Disconnect();
	}
