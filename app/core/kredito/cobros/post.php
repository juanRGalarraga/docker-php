<?php
/**
    * Escribe o registra un dato
    * Created: 
    * Author: api_creator
*/
require_once(DIR_model."cobros".DS."class.cobros.inc.php");
require_once(DIR_model."prestamos".DS."class.prestamos.inc.php");
require_once(DIR_model."biblioteca".DS."class.biblioteca.inc.php");
$cobros = new cCobros;
$prestamos = new cPrestamos;
$biblioteca = new cBiblioteca;
$msgerr = array();

if(!$prestamo_id = SecureInt($ws->GetParam("prestamo_id"))){ 
	cLogging::Write(__FILE__ ." ".__LINE__ ."No se indico el identificador del prestamo");
    $ws->SendResponse(404,NULL,'No se indico el prestamo'); return;
}

if(!$prestamos->Get($prestamo_id)){
    cLogging::Write(__FILE__ ." ".__LINE__ ."El prestamo indicado no fue encontrado");
	$ws->SendResponse(404,NULL,'No se indico el prestamo'); return;
}

if(!$cuota_id = SecureInt($ws->GetParam("cuota_id"))){ 
    cLogging::Write(__FILE__ ." ".__LINE__ ."No se indico la cuota del prestamo");
}

if($prestamos->tipo_prestamo != "UNICO"){
    if(!SecureInt($cuota_id)){ 
        $ws->SendResponse(404,NULL,'No se indico que cuota va a pagar'); return;
    }
}

if(!$broker_id = SecureInt($ws->GetParam("broker_id"))){ 
    $msgerr['broker_id'] = " No se indico el broker ";
}

$fecha_cobro = $ws->GetParam("fecha_cobro");
if(!cCheckInput::Fecha($fecha_cobro)){ 
    $msgerr['fecha_cobro'] = " No se indico la fecha o es invalida ";
}

if(!$monto = SecureFloat($ws->GetParam("monto"))){ 
    $msgerr['monto'] = " No se indico el monto a pagar ";
}
$nro_comprobante = $ws->GetParam("nro_comprobante");
if(empty($nro_comprobante)){ 
    $msgerr['nro_comprobante'] = " No se indico el nº de comprobante ";
}

if(CanUseArray($msgerr)){
    $ws->SendResponse(500,$msgerr); return;
}

$archivo_data = null;
// Creo el archivo
if (!empty($ws->GetParam("files"))) {
	$archivo_data = CrearArchivo($ws->GetParam('files')); // devuelve un json o null
}

$reg = array();
$reg['prestamo_id'] = $prestamo_id;
$reg['persona_id'] = $prestamos->persona_id;
$reg['cuota_id'] = $cuota_id;
$reg['broker_id'] = $broker_id;
$reg['fecha_de_cobro'] = $fecha_cobro;
$reg['monto'] = $monto;
$reg['nro_comprobante'] = $nro_comprobante;
$reg['tipo_moneda'] = $prestamos->tipo_moneda;
$reg['archivo_id'] = ((isset($archivo_data['archivo_id']) && SecureInt($archivo_data['archivo_id']))) ?  $archivo_data['archivo_id'] : NULL; 
$reg['data'] = $archivo_data;

if(!$response = $cobros->Create($reg)){
    $ws->SendResponse(500,'No se pudo crear el cobro'); return;
}
$ws->SendResponse(200,$response); return;



function CrearArchivo($files)
{
    global $biblioteca;
    global $prestamos;
    global $ws;
	
	$result = null;
    if(isJsonEx($files)){
        $files = json_decode($files,true);
    }
    if(is_object($files)){
        $files = json_encode(json_decode($files,true));
    }
	
	$nombre = mb_substr($files['nombre'], 0, 255);
	if (empty($nombre) or is_null($nombre)) {
		cLogging::Write(__FILE__ ." " . __LINE__ . " Nombre del archivo inválido o vacío ");
		$ws->SendResponse(406, null, " Archivo inválido ", 0);
		return;
	}
	$nombre = str_replace([" ",":"],["_","-"],cFechas::Ahora())."-".$nombre;
	
	$data = $files['data'];
	if (empty($data) or is_null($data)) {
		cLogging::Write(__FILE__ ." " . __LINE__ . " El base 64 del archivo llego vacío ");
		$ws->SendResponse(406, null, " Archivo inválido ", 0);
		return;
	}

	if (!$datos_biblioteca = $biblioteca->GetByPersona($prestamos->persona_id)) {
		cLogging::Write(__FILE__ ." " . __LINE__ . " Persona sin biblioteca ");
		$ws->SendResponse(500, null, " Persona sin biblioteca ", 0);
		return;
	}

	$archivo_data = base64_decode($data);
	$ruta = "Comprobantes";
	$direccion_indicada = DIR_biblioteca . $datos_biblioteca->nombre . DS . $ruta;

	if (!ExisteCarpeta(DIR_biblioteca)) {
		cSidekick::EnsureDirExists(DIR_biblioteca);
	}

	if (!ExisteCarpeta(DIR_biblioteca . $datos_biblioteca->nombre)) {
		cSidekick::EnsureDirExists(DIR_biblioteca . $datos_biblioteca->nombre);
	}

	if (!ExisteCarpeta($direccion_indicada)) {
		cSidekick::EnsureDirExists($direccion_indicada);
	}
	
	$direccion_indicada = DIR_biblioteca . $datos_biblioteca->nombre . DS . $ruta . DS . "pagos";
	if (!ExisteCarpeta($direccion_indicada)) {
		cSidekick::EnsureDirExists($direccion_indicada);
	}

	$ruta_final = str_replace(DS . DS, DS, $direccion_indicada) . DS . $nombre;
	file_put_contents($ruta_final, $archivo_data);

	if (ExisteArchivo($ruta_final)) {
		if ($biblioteca->AddFile($nombre)) {
			$result = array("nombre_archivo" => $nombre, "archivo_id" => $biblioteca->last_id);
			cLogging::Write(__FILE__ ." " . " " . $ws->transId . " -> " . __LINE__ . " archivo creado con éxito en la ruta indicada : " . $ruta_final);
		} else {
			cLogging::Write(__FILE__ ." " . " " . $ws->transId . " -> " . __LINE__ . " No se pudo ubicar el archivo en -> " . $ruta_final);
		}
	}
    return $result;
}