
<?php 

require_once(DIR_model."prestamos".DS."class.prestamos.inc.php");
require_once(DIR_model."cobros".DS."class.cobros.inc.php");

$this_file = substr(__FILE__, strlen(DIR_BASE)) . " ";

$cobros = new cCobros();
$préstamos = new cPrestamos();

$reg = array();
$post = CleanArray($_POST);

$ruta = DIR_downloader_files."comprobantes".DS;
$tipo_prest = "UNICO";
$files = array();
if(!$prestamo_id = SecureInt($post['prestamo_id'])){
    cLogging::Write($this_file." No se indico el id de prestamo");
    EmitJSON(" No se puede realizar el cobro en estos momentos, reintente luego... ");
    return;
}

if(!$préstamos->Get($prestamo_id)){
    cLogging::Write($this_file." No se encontro el prestamo_id buscado");
    EmitJSON(" No se puede realizar el cobro en estos momentos, reintente luego... ");
    return;
}

if(!$persona_id = SecureInt($post['persona_id'])){
    cLogging::Write($this_file." No se indico el id de prestamo");
    EmitJSON(" No se puede realizar el cobro en estos momentos, reintente luego... ");
    return;
}



if($préstamos->theData->tipo_prestamo != "UNICO"){
    if(!$cuotas_selected = SecureInt($post['cuotas_selected'])){
        cLogging::Write($this_file." No se indico el id de prestamo");
        EmitJSON(" No se puede realizar el cobro en estos momentos, reintente luego... ");
        return;
    }
    $reg['cuota_id'] = $cuotas_selected;
}

$monto_a_pagar = $post['monto_a_pagar'];
if(empty($monto_a_pagar) or !SecureFloat($monto_a_pagar)){
    cLogging::Write($this_file." El monto a pagar no es valido");
    EmitJSON(" No se puede realizar el cobro en estos momentos, reintente luego... ");
    return;
}

$monto_pago = $post['monto_pago'];
if(empty($monto_pago) or !SecureFloat($monto_pago)){
    cLogging::Write($this_file." El monto pago no es valido");
    EmitJSON(" No se puede realizar el cobro en estos momentos, reintente luego... ");
    return;
}

if(!cCheckInput::Fecha($post['fecha_cobro'])){
    cLogging::Write($this_file." La fecha que se indico es invalida ");
    EmitJSON(" No se puede realizar el cobro en estos momentos, reintente luego... ");
    return;
}
$fecha_cobro = $post['fecha_cobro'];
$nro_comprobante = $post['nro_comprobante'];

$reg['prestamo_id'] = $prestamo_id;
$reg['broker_id'] = 1;
$reg['fecha_cobro'] = $fecha_cobro;
$reg['monto'] = $monto_pago;
$reg['nro_comprobante'] = $nro_comprobante;

$extensiones_permitidas = ['pdf','jpg','png','webp','bmp','doc','docx','odt','odf'];

if(isset($_FILES['adjunto_input']) AND CanUseArray($_FILES['adjunto_input'])){
    $nombre_original = $_FILES['adjunto_input']['name'];
    $ext = ExtraerExtension($nombre_original);
    if (!in_array(strtolower($ext), $extensiones_permitidas)) {
        cLogging::Write($this_file . " Tipo de archivo no permitido: ".$nombre_original);
        $msgerr['zonaComprobante'] = "Tipo de archivo no permitido.";
        EmitJSON($msgerr);
        return;
    }
    $nombre_archivo = date('Y-m-d_H-i') . "-" . basename($_FILES['adjunto_input']['name']);
    if (!ExisteCarpeta($ruta)) {
        cSidekick::EnsureDirExists($ruta);
    }
    
    $Direccionfile = $ruta . $nombre_archivo;
    if (ExisteArchivo($Direccionfile)) unlink($Direccionfile);
    
    move_uploaded_file($_FILES['adjunto_input']['tmp_name'], $Direccionfile);

    $reg['files'] = array("nombre" => $nombre_archivo, "data" => base64_encode(file_get_contents($Direccionfile)));
}


if(!$cobros->CreateCobro($reg)){
    cLogging::Write($this_file." No se pudo crear el cobro, problemas con el core ");
    EmitJSON(" No se puede realizar el cobro en estos momentos, reintente luego... ");
    return;
}
ResponseOk();


?> 