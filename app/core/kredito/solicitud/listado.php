<?php
/**
*	Devuelve un listado de solicitudes
*	Created: 2021-09-23
*	Author: Gastón Fernandez
*/

	$setup = json_decode(
<<<END
	{
	"descripcion":"Listado de los campos que se envían al cliente",
	"fields":[
		"`solicitudes`.`id`",
		"`solicitudes`.`negocio_id`",
		"`solicitudes`.`estado`",
		"`solicitudes`.`estado_solicitud`",
		"`solicitudes`.`prestamo_id`",
		"`solicitudes`.`persona_id`",
		"`solicitudes`.`data`",
		"`solicitudes`.`data`->>'$.plazo' AS 'plazo'",
		"`solicitudes`.`data`->>'$.origen' AS 'origen'",
		"`solicitudes`.`data`->>'$.capital' AS 'capital'",
		"`solicitudes`.`data`->>'$.nombre' AS 'nombre'",
		"`solicitudes`.`data`->>'$.apellido' AS 'apellido'",
		"`solicitudes`.`data`->>'$.nro_doc' AS 'nro_doc'",
		"`solicitudes`.`data`->>'$.paso_alias' AS 'paso'",
		"`solicitudes`.`sys_fecha_modif`",
		"`solicitudes`.`sys_fecha_alta`"
	]
}
END
);

	$campos_orden = ["id","sys_fecha_modif"];
	$campos_busqueda = ["`solicitudes`.`id`","`solicitudes`.`data`->>'$.nro_doc'","`solicitudes`.`data`->>'$.nombre'","`solicitudes`.`data`->>'$.apellido'"];
	$novacias = $ws->GetParam(['novacia','novacias','noempty']);

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
	
	if (!$listado->GetSesValue('orden'))
		$listado->SetSesValue('orden', ["sys_fecha_modif"=>"DESC"]);
	
	$db = new cModels();

	$select = "SELECT ".implode(", ",$setup->fields);
	$from = "FROM ".SQLQuote(TBL_solicitudes)." AS `solicitudes`";
	$join = "";
	$where = "WHERE 1=1 ";
	if(!empty($buscar)){
		$where .= $listado->SetSearch($buscar, $campos_busqueda);
	}
	if ($novacias) {
		$where .= "AND (`solicitudes`.`data`->>'$.nro_doc' IS NOT NULL AND `solicitudes`.`data`->>'$.nro_doc' != '')";
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
