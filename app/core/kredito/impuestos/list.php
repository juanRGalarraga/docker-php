<?php
    /**
     * Obtiene un listado de los registros
     * Created: 2021-11-02 15:55:10
     * Author: api_creator
	 *
	 *	Modif: 2021-11-11
	 *	Author: Gastón Fernandez
	 *	Desc:
	 *		Arreglo para realizar ordenamientos y recepción de parametros
     */
	
	$element = $ws->GetParam(["element"]);
    if (isset($element) AND !empty($element) AND !in_array($element,['CARGO','IMP'])){
        $ws->SendResponse(400, null, 10, "El elemento esta vacío o no es compatible.");
        return;
    }
	
	$setup = json_decode(
<<<END
	{
	"descripcion":"Listado de los campos que se envían al cliente",
	"fields":[
		"`id`",
		"`nombre`",
		"`alias`",
		"`valor`",
		"`aplicar`",
		"`aplica_iva`",
		"`estado`"
	]
}
END
);

	$buscar = $ws->GetParam(["search","buscar"]);
    $campos_orden = ["id","nombre","alias","valor"];
	$campos_busqueda = ["id","nombre","alias","valor","aplicar","aplica_iva","estado"];
	require_once(DIR_model."listados".DS."class.listado.inc.php");
	$db = new cModels();
	
	if(!$listado->GetSesValue("orden")){
		$listado->SetSesValue("orden",["id"=>"DESC"]);
	}
	
	$select = "SELECT ".implode(", ",$setup->fields);
	$from = "FROM ".SQLQuote(TBL_cargos_impuestos);
	$where = "WHERE `tipo` LIKE '".$element."' ";
	if(!empty($buscar)){
		$where .= $listado->SetSearch($buscar, $campos_busqueda);		
	}
	
	$listado->SetSQL(implode(" ",[$select, $from, $where]));

	try {
		$ws->SendResponse(200, $listado->GetResult($db));
	} catch(Exception $e) {
		$ws->SendResponse(500, 'Ocurrió un error al acceder a la base de datos.');
	} finally {
		$db->Disconnect();
	}