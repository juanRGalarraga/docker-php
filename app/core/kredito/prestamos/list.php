<?php
/**
*	Devuelve un listado de préstamos
*	Created: 2021-09-23
*	Author: Gastón Fernandez
*/
	require_once(DIR_model."prestamos".DS."class.filtrosMora.inc.php");
	$filtros = new cFiltrosMoras;

	$setup = json_decode(
<<<END
	{
	"descripcion":"Listado de los campos que se envían al cliente",
	"fields":[
		"`personas`.`nombre`",
		"`personas`.`apellido`",
		"`personas`.`nro_doc`",
		"`prestamos`.`id`",
		"`prestamos`.`negocio_id`",
		"`prestamos`.`estado`",
		"`prestamos`.`persona_id`",
		"`prestamos`.`fechahora_emision`",
		"`prestamos`.`fecha_vencimiento`",
		"`prestamos`.`tipo_moneda`",
		"`prestamos`.`periodo`",
		"`prestamos`.`capital`",
		"`prestamos`.`total_imponible`",
		"`prestamos`.`total_intereses`",
		"`prestamos`.`total_cargos`",
		"`prestamos`.`total_impuestos`",
		"`prestamos`.`sys_fecha_modif`",
		"`prestamos`.`sys_fecha_alta`"
	]
}
END
);
	$campos_orden = ["prestamos.id","prestamos.fechahora_emision","prestamos.sys_fecha_modif","personas.nombre","personas.nro_doc"];
	$campos_busqueda = ["`personas`.`nombre`","`personas`.`apellido`","`personas`.`nro_doc`","`prestamos`.`id`"];
	
	$estado = $ws->GetParam(["estado","estados"]);
	$mora = $ws->GetParam(["mora","mora_filter"]);
	$moraCond = "";
	$persona_id = $ws->GetParam(["persona","persona_id"]);
	$plan = $ws->GetParam(["plan","plan_id"]);
	if(!is_null(SecureInt($mora)) AND $mora > 0){
		$filtros->Get($mora);
		$moraCond = $filtros->GetFilterQuery(TBL_prestamos);
	}
	

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
		$listado->SetSesValue("orden",["prestamos.sys_fecha_modif"=>"DESC"]);
	}
	
	$db = new cModels();

	$select = "SELECT ".implode(", ",$setup->fields);
	$from = "FROM ".SQLQuote(TBL_prestamos)." AS `prestamos`,".SQLQuote(TBL_personas)." AS `personas`";
	$join = "";
	$where = "WHERE `prestamos`.`persona_id`=`personas`.`id` ";
	if(!is_null(SecureInt($persona_id))){
		$where .= "AND `prestamos`.`persona_id`=".$persona_id." ";
	}
	if(!empty($estado) AND is_string($estado)){
		$where .= "AND `prestamos`.`estado` IN (".$db->RealEscape($estado).") ";
	}
	
	if(!is_null(SecureInt($plan)) AND $plan > 0){
		$where .= "AND `prestamos`.`plan_id` = ".$plan." ";
	}
	if(!empty($moraCond)){
		$where .= "AND (".$moraCond.") ";
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
