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
		"`cobros`.`id`",
		"`cobros`.`prestamo_id`",
		"`cobros`.`cuota_id`",
		"`cobros`.`broker_id`",
		"`cobros`.`estado`",
		"`cobros`.`monto`",
		"`cobros`.`tipo_moneda`",
		"`cobros`.`nro_comprobante`",
		"`cobros`.`data`",
		"`cobros`.`sys_fecha_modif`",
		"`cobros`.`sys_fecha_alta`",
		"`cobros`.`fecha_de_cobro`",
        "`brokers`.`nombre` as 'nombre_broker'"
	]
}
END
);

	$campos_orden = ["id","sys_fecha_modif"];
	$campos_busqueda = ["`cobros`.`prestamo_id`"];
	
	$buscar = mb_strtolower(mb_substr($ws->params['buscar']??null,0,25));
	$estado = mb_strtolower(mb_substr($ws->params['estado']??null,0,25));

	require_once(DIR_model."listados".DS."class.listado.inc.php");
	
    if(!$listado->GetSesValue("orden")){
        $listado->SetSesValue("orden",["id"=>"DESC"]);
    }
	
	$db = new cModels();

	$select = "SELECT ".implode(", ",$setup->fields);
	$from = "FROM ".SQLQuote(TBL_cobros)." AS `cobros` , ".SQLQuote(TBL_brokers)." as `brokers` ";
	$join = "";
	$where = "WHERE 1=1 AND `cobros`.`broker_id` = `brokers`.`id` ";
    if(!empty($estado)){ 
        $where .= " AND `cobros`.`estado` = '".$estado."' ";
    }
	$where .= $listado->SetSearch($buscar, $campos_busqueda);
	
	$listado->SetSQL(implode(" ",[$select, $from, $join, $where]));
	
	try {
		$ws->log_max_length = 1024;
		$ws->SendResponse(200, $listado->GetResult($db));
	
	} catch(Exception $e) {
		$ws->SendResponse(500, 'Ocurrió un error al acceder a la base de datos.');
	} finally {
		$db->Disconnect();
	}
