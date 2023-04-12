<?php
/**
 * Listado de marcas
 * Created: 2021-11-19
 * Author: Gastón Fernandez
 */
	$setup = json_decode(
<<<END
	{
	"descripcion":"Listado de los campos que se envían al cliente",
	"fields":[
		"`marcas`.`nombre` as marca",
		"`modelos`.`nombre` as modelo",
		"`productos`.`id`",
		"`productos`.`modelo_id`",
		"`productos`.`nombre`",
		"`productos`.`precio`",
		"`productos`.`tipo_moneda`",
		"`productos`.`estado`",
		"`productos`.`descripcion`",
		"`productos`.`data`",
		"`productos`.`sys_fecha_modif`",
		"`productos`.`sys_fecha_alta`"
	]
}
END
);
	$campos_orden = ["productos.id","marcas.nombre","modelos.nombre","productos.nombre","productos.precio","productos.sys_fecha_modif","productos.sys_fecha_alta"];
	$campos_busqueda = ["`productos`.`nombre`","`productos`.`id`","`marcas`.`nombre`","`modelos`.`nombre`"];
	
	$estado = $ws->GetParam(["estado","estados"]);

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
		$listado->SetSesValue("orden",["sys_fecha_modif"=>"DESC"]);
	}
	
	$db = new cModels();

	$select = "SELECT ".implode(", ",$setup->fields);
	$from = "FROM ".SQLQuote(TBL_productos)." as productos,".SQLQuote(TBL_modelos)." as modelos, ".SQLQuote(TBL_marcas)." as marcas";
	$join = "";
	$where = "WHERE `productos`.`modelo_id`=`modelos`.`id` AND `modelos`.`marca_id`=`marcas`.`id` ";
	if(!empty($estado) AND is_string($estado)){
		$estado = explode(",",$estado);
		$db->RealEscapeArray($estado);
		$where .= "AND `estado` IN ('".implode("','",$estado)."') ";
	}
	
	if(!empty($buscar)){
		$where .= $listado->SetSearch($buscar, $campos_busqueda);		
	}
	//ShowVar(implode(" ",[$select, $from, $join, $where]));
	
	$listado->SetSQL(implode(" ",[$select, $from, $join, $where]));
	
	try {
		$ws->log_max_length = 1024;
		$ws->SendResponse(200, $listado->GetResult($db));
	
	} catch(Exception $e) {
		$ws->SendResponse(500, 'Ocurrió un error al acceder a la base de datos.');
	} finally {
		$db->Disconnect();
	}
