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
		"`modelos`.`id`",
		"`modelos`.`marca_id`",
		"`modelos`.`nombre`",
		"`modelos`.`descripcion`",
		"`modelos`.`estado`",
		"`modelos`.`data`",
		"`modelos`.`sys_fecha_modif`",
		"`modelos`.`sys_fecha_alta`"
	]
}
END
);
	$campos_orden = ["id","marca_id","nombre","sys_fecha_modif","sys_fecha_alta"];
	$campos_busqueda = ["`modelos`.`nombre`","`modelos`.`id`"];
	
	$estado = $ws->GetParam(["estado","estados"]);

	require_once(DIR_model."modelos".DS."class.modelos.inc.php");
	$modelos = new cModelos;
	require_once(DIR_model."listados".DS."class.listado.inc.php");
	$imgs = $ws->CutParam(["imgs"]);//Indica si debo devolver ADEMÁS del listado normal, un indice más con las imagenes en base64
	$buscar = $ws->GetParam(['buscar','search']);
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
	
	$marca = SecureInt($ws->GetParam(['marca_id','marca']));
	
	$db = new cModels();

	$select = "SELECT ".implode(", ",$setup->fields);
	$from = "FROM ".SQLQuote(TBL_modelos)." as modelos, ".SQLQuote(TBL_marcas)." as marcas";
	$join = "";
	$where = "WHERE `modelos`.`marca_id`=`marcas`.`id` ";
	if(!is_null($marca)){
		$where .= "AND `modelos`.`marca_id`=".$marca." ";
	}
	if(!empty($estado) AND is_string($estado)){
		$estado = explode(",",$estado);
		$db->RealEscapeArray($estado);
		$where .= "AND `modelos`.`estado` IN ('".implode("','",$estado)."') ";
	}
	
	if(!empty($buscar)){
		$where .= $listado->SetSearch($buscar, $campos_busqueda);		
	}
	//ShowVar(implode(" ",[$select, $from, $join, $where]));
	
	$listado->SetSQL(implode(" ",[$select, $from, $join, $where]));
	
	try {
		$ws->log_max_length = 1024;
		$result = $listado->GetResult($db);
		if($imgs AND isset($result['list'])){
			if(CanUseArray($result['list'])){
				foreach($result['list'] as $key => $value){
					$tmpVal = (object)$value;
					$id = $tmpVal->id ?? null;
					if(is_null(SecureInt($id))){ continue; }
					$data = $tmpVal->data ?? null;
					if(empty($data)){ continue; }
					if(!$image = $modelos->GetImageByData($data,$id)){ continue; }
					if(is_object($value)){
						$result['list'][$key]->imagen = $image;
					}else{
						$result['list'][$key]['imagen'] = $image;
					}
				}
			}
		}
		
		$ws->SendResponse(200, $result);
	
	} catch(Exception $e) {
		$ws->SendResponse(500, 'Ocurrió un error al acceder a la base de datos.');
	} finally {
		$db->Disconnect();
	}
