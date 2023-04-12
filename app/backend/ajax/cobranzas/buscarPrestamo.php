<?php 

require_once(DIR_model."prestamos".DS."class.prestamos.inc.php");
require_once(DIR_model."cuotas".DS."class.cuotas.inc.php");
require_once(DIR_model."clientes".DS."class.clientes.inc.php");
require_once(DIR_model."clientes".DS."class.clientesData.inc.php");
require_once(DIR_model."planes".DS."class.planes.inc.php");

$this_file = substr(__FILE__, strlen(DIR_BASE)) . " ";

$post = CleanArray($_POST);

if(!$prestamo_id = SecureInt($post['nro_prestamo'])){
    ?>
        <div class="card-header">
            <h3><?php EchoLang('Préstamo no encontrado'); ?>.</h3>
        </div>
        <div class="card-body">
            <p class="text-center text-warning"><?php EchoLang('Número de préstamo'); ?> <?php echo $prestamo_id; ?> <?php EchoLang('es incorrecto'); ?>.</p>
        </div>
        <div class="card-footer"></div>
    <?php
    cLogging::Write($this_file." No se encontró el prestamo_id");
    return;
}

$str_nro_prestamo = str_pad($prestamo_id,   8,   '0', STR_PAD_LEFT);
 
$prestamos = new cPrestamos();

if(!$prestamos->Get($prestamo_id)){
    ?>
        <div class="card-header">
            <h3><?php EchoLang('Préstamo no encontrado'); ?>.</h3>
        </div>
        <div class="card-body">
            <p class="text-center text-warning"><?php EchoLang('Número de préstamo '); ?> <?php echo $str_nro_prestamo; ?> <?php EchoLang(' no fue encontrado'); ?>.</p>
        </div>
        <div class="card-footer"></div>
    <?php
    cLogging::Write($this_file." No se encontró el prestamo");
    return;
}


include("DatosTitular.php");
include("DatosPrestamo.php");
if($prestamos->theData->tipo_prestamo != "UNICO"){
    include("DatosCuota.php");
}
include("Gestion.php");


?>