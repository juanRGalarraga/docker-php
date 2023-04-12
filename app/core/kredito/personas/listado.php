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
		"`personas`.`id`",
		"`personas`.`nombre`",
		"`personas`.`apellido`",
		"`personas`.`nro_doc`",
		"`personas`.`email`",
		"`personas`.`tel_movil`",
		"`personas`.`dir`->>'$.dir_calle' as 'dir'",
		"`personas`.`ciudad_nombre`",
		"`personas`.`region_nombre`",
		"`personas`.`sys_fecha_alta`"
	]
}
END
);

	$campos_orden = ["id","nombre","apellido","nro_doc","sys_fecha_modif"];
	$campos_busqueda = ["`personas`.`id`","`personas`.`nro_doc`","`personas`.`nombre`","`personas`.`apellido`","`personas`.`email`","`personas`.`tel_movil`"];

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
	
	if(!$listado->GetSesValue("orden")){
		$listado->SetSesValue("orden",["id"=>"DESC"]);
	}
	
	$db = new cModels();

	$select = "SELECT ".implode(", ",$setup->fields);
	$from = "FROM ".SQLQuote(TBL_personas)." AS `personas`";
	$join = "";
	$where = "WHERE 1=1 ";
	if(!empty($buscar)){
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
