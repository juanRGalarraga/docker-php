<?php 

/*

*/

require_once('initialize.php');
@session_start();
header("Cache-Control: no-cache, must-revalidate");
// Establecer las constantes de configuración
require_once(DIR_config.'config.inc.php');
require_once(DIR_includes."common.inc.php"); // Contiene las funciones comunes.
require_once(DIR_includes."class.fechas.inc.php"); // Funciones de tratamiento de fechas y horas.
require_once(DIR_includes.'class.logging.inc.php'); // Para escribir entradas en el log de eventos.
require_once(DIR_includes.'class.sidekick.inc.php'); // Para escribir entradas en el log de eventos.

require_once(DIR_model."prestamos".DS."class.prestamos.inc.php");
require_once(DIR_model."cuotas".DS."class.cuotas.inc.php");

// $objeto_db = new cDb();
// $objeto_db->Connect(DBHOST, DBNAME, DBUSER, DBPASS, DBPORT);
// if ($objeto_db->error) {
// 	cLogging::Write($this_file." DBErr: ".$objeto_db->errmsg);
// 	EchoLogP($objeto_db->errmsg);
// 	return;
// }


$id = false;

if(!$id = SecureInt($_GET["id"])){
    echo "ID : Invalido";
    return false;
}


$this_file = substr(__FILE__,strlen(DIR_BASE))." ";

$prestamos = new cPrestamos;
$cuotas = new cCuotas;

if(!$data_prestamo = $prestamos->Get($id)){
    echo " Prestamo inexistente ";
    return false;
}

$list_cuotas = $cuotas->GetList($id);

?>

<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-wEmeIV1mKuiNpC+IOBjI7aAzPcEZeedi5yW5f2yOq55WWLwNGmvvx4Um1vskeMj0" crossorigin="anonymous">
</head>

<h5 class="mt-3"> Prestamo </h5>
<div class="card">
    <div class="card-body">
        <div class="row">
            <?php foreach ($data_prestamo as $key => $prestamo_d) { ?>
                <div class="col-6" style="border: 1px solid #000;">
                    <label for=""> <?php print_r($key); ?> :  </label>
                    <?php print_r($prestamo_d); ?>
                    <br>
                </div>
            <?php } ?>
        </div>
    </div>
</div>

<h5> Cuotas </h5>
<div class="card">
    <div class="card-body">
        <div class="row">
            <table class="table table-striped table-bordered table-hover">
                <thead>
                    <tr>
                        <th class="text-center"> Cuota Nº </th>
                        <th class="text-center"> Saldo Inicio Periodo </th>
                        <th class="text-center"> Capital </th>
                        <th class="text-center"> Interes </th>
                        <th class="text-center"> IVA </th>
                        <th class="text-center"> Dias </th>
                        <th class="text-center"> Fecha de Vencimiento </th>
                        <th class="text-center"> Monto Cuota </th>
                        <th class="text-center"> Saldo Final Periodo </th>
                    </tr>
                </thead>
                <tbody>
                <?php 
                    if(is_countable($list_cuotas) && count($list_cuotas) > 0){
                        foreach ($list_cuotas as $key => $cuota) { 
                             ?>
                    <tr>
                        <td class="text-center"> <?php echo $cuota->cuota_nro; ?></td>
                        <td class="text-end"> $ <?php echo F($cuota->saldo_inicio_periodo); ?></td>
                        <td class="text-end"> $ <?php echo F($cuota->capital); ?></td>
                        <td class="text-end"> $ <?php echo F($cuota->interes_cuota); ?></td>
                        <td class="text-end"> $ <?php echo F($cuota->iva_interes_cuota); ?></td>
                        <td class="text-center"> <?php echo $cuota->dias; ?></td>
                        <td class="text-center"> <?php echo cFechas::SQLDate2Str($cuota->fecha_venc); ?></td>
                        <td class="text-end" > $ <?php echo F($cuota->monto_cuota); ?></td>
                        <td class="text-end" > $ <?php echo F($cuota->saldo_final_periodo); ?></td>
                    </tr>
                <?php 
                    }
                } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>